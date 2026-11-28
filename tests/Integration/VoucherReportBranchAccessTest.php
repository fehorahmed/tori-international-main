<?php
namespace Tests\Integration;

use App\Http\Controllers\Account\ReceiptPaymentController;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Support\ReportBranch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class VoucherReportBranchAccessTest extends TestCase
{
    use DatabaseTransactions;
    private User $user;
    private int $branch;
    private int $other;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrFail();
        $this->user->syncRoles([]); $this->user->syncPermissions([]);
        $this->actingAs($this->user);
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->other = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        session(['active_sister_concern_id' => $this->branch]);
    }
    private function voucher(int $branch, int $type = 3): Voucher
    {
        return Voucher::create(['sister_concern_id' => $branch, 'voucher_type' => $type, 'amount' => '10.25',
            'date' => now()->toDateString(), 'voucher_no' => 'VBA-'.Str::random(12), 'voucher_no_group_sl' => 1, 'user_id' => $this->user->id]);
    }
    private function denied(string $method, Voucher $voucher): void
    {
        try { (new ReceiptPaymentController)->$method($voucher); $this->fail('Expected forbidden'); }
        catch (HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }
    public function test_active_branch_access_remains_and_foreign_voucher_is_blocked_without_permission(): void
    {
        $this->user->givePermissionTo('receipt_voucher');
        $own = $this->voucher($this->branch); $foreign = $this->voucher($this->other);
        foreach (['details', 'print'] as $method) {
            $view = (new ReceiptPaymentController)->$method($own);
            $this->assertSame($own->id, $view->getData()['voucher']->id);
            $this->denied($method, $foreign);
        }
    }
    public function test_all_branch_permission_allows_view_and_print_with_correct_voucher_permission(): void
    {
        $this->user->givePermissionTo(ReportBranch::PERMISSION);
        foreach ([1 => 'journal_voucher', 2 => 'payment_voucher', 3 => 'receipt_voucher', 4 => 'contra_voucher'] as $type => $permission) {
            $voucher = $this->voucher($this->other, $type);
            foreach (['details', 'print'] as $method) $this->denied($method, $voucher);
            $this->user->givePermissionTo($permission);
            foreach (['details', 'print'] as $method) {
                $view = (new ReceiptPaymentController)->$method($voucher);
                $this->assertSame($voucher->id, $view->getData()['voucher']->id);
                $this->assertSame('accounts.receipt_payment.'.$method, $view->name());
            }
        }
        $this->assertSame($this->branch, $this->user->activeConcernId());
    }
    public function test_accounts_module_route_permission_is_still_required(): void
    {
        $this->user->givePermissionTo([ReportBranch::PERMISSION, 'receipt_voucher']);
        $voucher = $this->voucher($this->other);
        $this->get(route('voucher.details', ['voucher' => $voucher->id]))->assertForbidden();
        $this->get(route('voucher.print', ['voucher' => $voucher->id]))->assertForbidden();
    }
}
