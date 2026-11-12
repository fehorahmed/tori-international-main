<?php

namespace Tests\Integration;

use App\Http\Controllers\Ecommerce\PackageOrderController;
use App\Http\Controllers\Ecommerce\ProductPackageController;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Inventory\Inventory;
use App\Models\Sales\SaleOrderItem;
use App\Models\Settings\Category;
use App\Models\Settings\Product;
use App\Models\Settings\Unit;
use App\Models\Settings\Warehouse;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Ecommerce\CartService;
use App\Services\Packages\PackageOrderService;
use App\Services\Packages\ProductPackageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductPackageTest extends TestCase
{
    use DatabaseTransactions;

    private Product $a;

    private Product $b;

    private Product $package;

    private Warehouse $warehouse;

    private User $operator;

    private Supplier $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->warehouse = Warehouse::where('status', 1)->whereHas('sisterConcern', fn ($q) => $q->where('status', 1))->firstOrFail();
        $this->operator = User::firstOrFail();
        $this->operator->givePermissionTo(['ecommerce_product', 'ecommerce_product_create', 'ecommerce_product_edit', 'ecommerce_order', 'ecommerce_order_confirm', 'ecommerce_order_confirm']);
        $this->actingAs($this->operator)->withSession(['active_sister_concern_id' => $this->warehouse->sister_concern_id]);
        $this->customer = Supplier::create(['name' => 'Package rollback customer', 'type' => 'Customer', 'status' => 1]);
        $this->a = $this->product('Component A', '100.00');
        $this->b = $this->product('Component B', '50.00');
        $this->package = $this->product('Test package', '225.01', ['catalog_type' => 'package', 'package_warehouse_id' => $this->warehouse->id]);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->a->id, 'quantity' => 2], ['product_id' => $this->b->id, 'quantity' => 1]]);
        foreach ([[$this->a, 10], [$this->b, 3]] as [$p,$qty]) {
            Inventory::create(['warehouse_id' => $this->warehouse->id, 'sister_concern_id' => $this->warehouse->sister_concern_id, 'product_id' => $p->id, 'quantity' => $qty, 'unit_price' => '10.00', 'product_code' => $p->code, 'batch_no' => 'PKG-'.Str::random(8)]);
        }
    }

    private function product(string $name, string $price, array $extra = []): Product
    {
        return Product::create($extra + ['name' => $name.' '.Str::random(8), 'code' => 'PKGTEST-'.Str::random(10), 'slug' => 'pkgtest-'.Str::random(15), 'category_id' => Category::firstOrFail()->id, 'unit_id' => Unit::firstOrFail()->id, 'status' => 1, 'is_show' => 1, 'selling_price' => $price, 'old_selling_price' => $price, 'user_id' => $this->operator->id, 'catalog_type' => 'single']);
    }

    private function data(): array
    {
        return ['request_key' => (string) Str::uuid(), 'billing' => ['name' => 'Package customer', 'phone' => '01712345678', 'address' => 'Rollback test address'], 'payment_method' => 'cod', 'shipping_charge' => '50.00', 'items' => [['product_id' => $this->package->id, 'quantity' => 1, 'price' => '0.01']]];
    }

    public function test_product_details_returns_cart_id_and_images_for_singles_and_packages(): void
    {
        foreach ([$this->a, $this->package] as $product) {
            $this->getJson('/api/get-product-details-data/'.$product->slug)
                ->assertOk()
                ->assertJsonPath('product.id', $product->id)
                ->assertJsonPath('product.images', []);
        }
        $this->getJson('/api/get-product-details-data/missing-product-slug')->assertNotFound();
    }

    public function test_product_and_package_catalogs_are_separate(): void
    {
        foreach (['single', 'package'] as $type) {
            $items = $this->getJson('/api/get-product-data?perPage=200&catalog_type='.$type)
                ->assertOk()->json('products');
            $this->assertNotEmpty($items);
            foreach ($items as $item) {
                $this->assertSame($type, $item['catalog_type']);
            }
        }
        $items = $this->getJson('/api/get-product-data?perPage=200')->assertOk()->json('products');
        $this->assertNotContains($this->package->id, array_column($items, 'id'));
    }

    public function test_admin_product_list_uses_primary_image_without_gallery_queries(): void
    {
        $response = $this->getJson(route('ecom_product.datatable', ['draw' => 1, 'start' => 0, 'length' => 10]));
        $response->assertOk();
        $this->assertArrayNotHasKey('error', $response->json());
        $this->assertNotEmpty($response->json('data'));
        $this->assertStringContainsString('image_preview', $response->json('data.0.image'));
    }

    public function test_package_availability_is_limited_by_components_and_has_no_inventory(): void
    {
        $this->assertSame(3, app(ProductPackageService::class)->availability($this->package));
        $this->assertCount(2, $this->package->package_info['components']);
        $this->expectException(ValidationException::class);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'sister_concern_id' => $this->warehouse->sister_concern_id, 'product_id' => $this->package->id, 'quantity' => 1, 'unit_price' => 0]);
    }

    public function test_nested_package_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->package->id, 'quantity' => 1], ['product_id' => $this->a->id, 'quantity' => 1]]);
    }

    public function test_checkout_uses_server_price_and_retry_is_idempotent(): void
    {
        $service = app(PackageOrderService::class);
        $data = $this->data();
        $order = $service->checkout($this->customer, $data);
        $this->assertEquals(275.01, $order->saleOrder->total);
        $this->assertSame('225.01', $order->lines[0]['unit_price']);
        $this->assertSame($order->id, $service->checkout($this->customer, $data)->id);
        $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
    }

    public function test_confirmation_deducts_components_and_posts_balanced_accounting_once(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        $service->confirm($checkout, $this->operator);
        $this->assertSame(8, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $this->assertSame(2, (int) Inventory::where('product_id', $this->b->id)->sum('quantity'));
        $this->assertSame('Confirmed', $checkout->fresh()->status);
        $items = SaleOrderItem::where('sale_order_id', $checkout->sale_order_id)->get();
        $this->assertSame(22501, (int) round($items->sum(fn ($i) => $i->selling_price * $i->quantity) * 100));
        $voucher = Voucher::where('sale_order_id', $checkout->sale_order_id)->firstOrFail();
        $this->assertSame(Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 1)->sum('amount'), Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 2)->sum('amount'));
        $service->confirm($checkout, $this->operator);
        $this->assertSame(1, Voucher::where('sale_order_id', $checkout->sale_order_id)->count());
    }

    public function test_package_edit_does_not_change_existing_order_snapshot(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        $this->package->update(['selling_price' => '999.00']);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->a->id, 'quantity' => 1], ['product_id' => $this->b->id, 'quantity' => 2]]);
        $service->confirm($checkout, $this->operator);
        $this->assertSame('225.01', $checkout->fresh()->lines[0]['unit_price']);
        $this->assertSame(8, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
    }

    public function test_stock_changed_after_checkout_rolls_back_without_posting(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        Inventory::where('product_id', $this->b->id)->update(['quantity' => 0]);
        try {
            $service->confirm($checkout, $this->operator);
            $this->fail('Expected shortage');
        } catch (ValidationException) {
            $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
            $this->assertSame(0, Voucher::where('sale_order_id', $checkout->sale_order_id)->count());
            $this->assertSame('Pending', $checkout->fresh()->status);
        }
    }

    public function test_api_creates_package_order_and_returns_customer_snapshot(): void
    {
        $this->actingAs($this->customer, 'customer');
        $data = $this->data();
        $response = $this->postJson('/api/package-checkout', $data)->assertCreated()->assertJsonPath('order.total', 275.01);
        $this->getJson('/api/order/'.$response->json('order_no'))->assertOk()->assertJsonPath('products.0.unit_price', '225.01');
    }

    public function test_cart_package_quantity_and_price_are_server_controlled(): void
    {
        $this->actingAs($this->customer, 'customer');
        $service = app(CartService::class);
        $service->add($this->package->id, 1, 9999);
        $item = $service->all()->first();
        $this->assertSame(1, (int) $item->quantity);
        $this->assertEquals(0, $item->discount);
        $this->assertEquals(225.01, $item->price);
    }

    public function test_mixed_cart_checks_shared_component_stock_as_one_demand(): void
    {
        $data = $this->data();
        $data['items'][] = ['product_id' => $this->a->id, 'quantity' => 9];
        $this->expectException(ValidationException::class);
        app(PackageOrderService::class)->checkout($this->customer, $data);
    }

    public function test_other_customer_cannot_read_order_or_receipt(): void
    {
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $other = Supplier::create(['name' => 'Unrelated package customer', 'type' => 'Customer', 'status' => 1]);
        $this->actingAs($other, 'customer');
        $this->getJson('/api/order/'.$checkout->saleOrder->order_no)->assertForbidden();
        $this->get('/api/order/'.$checkout->saleOrder->order_no.'/01712345678/receipt')->assertForbidden();
    }

    public function test_package_create_controller_saves_components_and_published_product(): void
    {
        $request = Request::create('/', 'POST', ['name' => 'New bundle '.Str::random(8), 'category_id' => $this->a->category_id, 'unit_id' => $this->a->unit_id, 'package_warehouse_id' => $this->warehouse->id, 'selling_price' => '150.00', 'status' => 1, 'is_show' => 1, 'items' => [['product_id' => $this->a->id, 'quantity' => 1], ['product_id' => $this->b->id, 'quantity' => 1]]]);
        (new ProductPackageController)->store($request, app(ProductPackageService::class));
        $package = Product::where('name', $request->name)->firstOrFail();
        $this->assertTrue($package->isPackage());
        $this->assertCount(2, $package->packageItems);
    }

    public function test_package_admin_forms_and_order_details_render(): void
    {
        $products = new ProductPackageController;
        $this->assertStringContainsString('Products inside this package', $products->create()->with('errors', new ViewErrorBag)->render());
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $orders = new PackageOrderController;
        $this->assertStringContainsString($checkout->saleOrder->order_no, $orders->show($checkout, app(PackageOrderService::class))->with('errors', new ViewErrorBag)->render());
    }
}
