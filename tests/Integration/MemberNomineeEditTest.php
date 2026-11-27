<?php

namespace Tests\Integration;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Nominee;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class MemberNomineeEditTest extends TestCase
{
    use DatabaseTransactions;

    private Supplier $member;
    private User $admin;
    private int $branch;
    private array $geo;

    protected function setUp(): void
    {
        parent::setUp();
        $middleware = app('router')->gatherRouteMiddleware(app('router')->getRoutes()->getByName('shomriddhi-advance.members.edit'));
        $this->withoutMiddleware(array_values(array_filter($middleware, fn ($class) => $class !== \Illuminate\Routing\Middleware\SubstituteBindings::class)));
        $this->admin = User::firstOrFail();
        $this->admin->givePermissionTo('customer_edit');
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->actingAs($this->admin);
        view()->share('errors', new ViewErrorBag);
        $geo = DB::table('villages as v')->join('unions as u', 'u.id', '=', 'v.union_id')->join('upazilas as z', 'z.id', '=', 'u.upazila_id')
            ->join('districts as d', 'd.id', '=', 'z.district_id')->select('d.division_id', 'd.id as district_id', 'z.id as upazila_id', 'u.id as union_id', 'v.id as village_id')->first();
        $this->assertNotNull($geo);
        $this->geo = (array) $geo;
        $this->member = Supplier::create(['name' => 'Nominee member', 'type' => 'Customer', 'status' => 1, 'member_no' => 'NOM-'.uniqid(), 'origin_sister_concern_id' => $this->branch,
            'division_id' => $geo->division_id, 'district_id' => $geo->district_id, 'upazila_id' => $geo->upazila_id, 'shomriddhi_group_id' => ShomriddhiGroup::firstOrFail()->id]);
        $this->member->update(['mobile_no' => '019'.str_pad((string) $this->member->id, 8, '0', STR_PAD_LEFT)]);
    }

    private function memberData(): array
    {
        return ['name' => $this->member->name, 'mobile_no' => $this->member->mobile_no, 'shomriddhi_group_id' => $this->member->shomriddhi_group_id,
            'division' => $this->geo['division_id'], 'district' => $this->geo['district_id'], 'upazila' => $this->geo['upazila_id']];
    }

    private function nomineeData(): array
    {
        return ['nominee_name' => 'Updated <Nominee>', 'nominee_relation' => 'Sister', 'nominee_nid' => '1234567890', 'nominee_phone' => '01712345678',
            'nominee_division' => $this->geo['division_id'], 'nominee_district' => $this->geo['district_id'], 'nominee_upazila' => $this->geo['upazila_id'],
            'nominee_union' => 'union_'.$this->geo['union_id'], 'nominee_village_id' => $this->geo['village_id'], 'nominee_address' => 'House 25'];
    }

    private function updateMember(array $data)
    {
        return $this->withSession(['active_sister_concern_id' => $this->branch])->put(route('shomriddhi-advance.members.update', $this->member), $data + $this->memberData());
    }

    public function test_edit_loads_existing_nominee_and_updates_without_duplicates_or_losing_image(): void
    {
        $nominee = Nominee::create(['customer_id' => $this->member->id, 'name' => 'Old <Nominee>', 'image' => 'uploads/nominee/existing-test.webp',
            'division_id' => $this->geo['division_id'], 'district_id' => $this->geo['district_id'], 'upazila_id' => $this->geo['upazila_id'],
            'union_id' => $this->geo['union_id'], 'village_id' => $this->geo['village_id']]);
        $this->withSession(['active_sister_concern_id' => $this->branch])->get(route('shomriddhi-advance.members.edit', $this->member))->assertOk()
            ->assertSee('Nominee Information')->assertSee('Old &lt;Nominee&gt;', false)->assertSee('uploads/nominee/existing-test.webp', false)
            ->assertViewHas('nomineeVillages', fn ($villages) => $villages->contains('id', $this->geo['village_id']));
        $this->updateMember($this->nomineeData())->assertRedirect(route('shomriddhi-advance.members.index'));
        $this->assertSame('Updated <Nominee>', $nominee->fresh()->name);
        $this->assertSame('Sister', $nominee->fresh()->relation);
        $this->assertSame('House 25', $nominee->fresh()->address);
        $this->assertSame('uploads/nominee/existing-test.webp', $nominee->fresh()->image);
        $this->assertEquals($this->geo['union_id'], $nominee->fresh()->union_id);
        $this->assertNull($nominee->fresh()->pourashava_id);
        $this->assertSame(1, Nominee::where('customer_id', $this->member->id)->count());
        // Old callers omitting nominee fields must not erase the nominee.
        $this->updateMember([])->assertRedirect(route('shomriddhi-advance.members.index'));
        $this->assertSame('Updated <Nominee>', $nominee->fresh()->name);
    }

    public function test_edit_can_add_nominee_and_clear_optional_fields(): void
    {
        $this->updateMember($this->nomineeData())->assertRedirect(route('shomriddhi-advance.members.index'));
        $nominee = Nominee::where('customer_id', $this->member->id)->firstOrFail();
        $this->assertSame('01712345678', $nominee->phone);
        $clear = array_fill_keys(array_keys($this->nomineeData()), null);
        $clear['nominee_name'] = 'Name only';
        $this->updateMember($clear)->assertRedirect(route('shomriddhi-advance.members.index'));
        $this->assertSame('Name only', $nominee->fresh()->name);
        $this->assertNull($nominee->fresh()->phone);
        $this->assertNull($nominee->fresh()->union_id);
        $this->assertNull($nominee->fresh()->village_id);
    }

    public function test_nominee_validation_blocks_invalid_location_and_file_without_partial_member_changes(): void
    {
        $payload = $this->nomineeData();
        $payload['nominee_division'] = DB::table('divisions')->where('id', '!=', $this->geo['division_id'])->value('id');
        $this->updateMember($payload)->assertSessionHasErrors('nominee_district');
        $this->updateMember(['nominee_name' => '', 'nominee_relation' => 'Sister'])->assertSessionHasErrors('nominee_name');
        $this->updateMember(['nominee_name' => 'Test', 'nominee_image' => UploadedFile::fake()->create('unsafe.txt', 1, 'text/plain')])->assertSessionHasErrors('nominee_image');
        $this->assertSame(0, Nominee::where('customer_id', $this->member->id)->count());
        $this->assertSame('Nominee member', $this->member->fresh()->name);
    }

    public function test_new_nominee_photo_replaces_old_photo(): void
    {
        $oldPath = 'uploads/nominee/test-'.\Illuminate\Support\Str::uuid().'.webp';
        \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path('uploads/nominee'));
        \Illuminate\Support\Facades\File::put(public_path($oldPath), 'test old nominee photo');
        $nominee = Nominee::create(['customer_id' => $this->member->id, 'name' => 'Photo nominee', 'image' => $oldPath]);
        $newPath = null;
        try {
            $this->updateMember(['nominee_name' => 'Photo nominee', 'nominee_image' => UploadedFile::fake()->image('portrait.png', 12, 12)])
                ->assertRedirect(route('shomriddhi-advance.members.index'));
            $newPath = $nominee->fresh()->image;
            $this->assertNotSame($oldPath, $newPath);
            $this->assertFileExists(public_path($newPath));
            $this->assertFileDoesNotExist(public_path($oldPath));
        } finally {
            \Illuminate\Support\Facades\File::delete(public_path($oldPath));
            if ($newPath && $newPath !== $oldPath) \Illuminate\Support\Facades\File::delete(public_path($newPath));
        }
    }

    public function test_other_branch_cannot_view_or_update_nominee(): void
    {
        $other = SisterConcern::whereKeyNot($this->branch)->firstOrFail()->id;
        $this->withSession(['active_sister_concern_id' => $other])->get(route('shomriddhi-advance.members.edit', $this->member))->assertForbidden();
        $this->withSession(['active_sister_concern_id' => $other])->put(route('shomriddhi-advance.members.update', $this->member), $this->memberData() + $this->nomineeData())->assertForbidden();
        $this->assertSame(0, Nominee::where('customer_id', $this->member->id)->count());
    }
}
