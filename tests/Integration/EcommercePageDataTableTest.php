<?php
namespace Tests\Integration;

use App\Http\Controllers\Ecommerce\API\PageController;
use App\Models\Ecommerce\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EcommercePageDataTableTest extends TestCase
{
    use DatabaseTransactions;

    private function rows(bool $editable): array
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->with('ecommerce_page_edit')->andReturn($editable);
        $this->actingAs($user);
        Page::create(['title' => 'Datatable fixture', 'slug' => 'datatable-fixture', 'status' => 1]);
        $data = app(PageController::class)->datatable()->getData(true);
        $this->assertArrayNotHasKey('error', $data);
        return $data['data'];
    }

    public function test_view_only_user_gets_rows_without_undefined_action_variable(): void
    {
        foreach ($this->rows(false) as $row) $this->assertSame('', $row['action']);
    }

    public function test_page_edit_permission_shows_edit_action_without_blog_permission(): void
    {
        foreach ($this->rows(true) as $row) $this->assertStringContainsString('/admin/ecommerce/page/edit/', $row['action']);
    }
}
