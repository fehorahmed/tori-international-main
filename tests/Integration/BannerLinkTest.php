<?php

namespace Tests\Integration;

use App\Http\Controllers\Ecommerce\BannerController;
use App\Models\Ecommerce\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BannerLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::firstOrFail();
        $user->givePermissionTo(['ecommerce_banner_create', 'ecommerce_banner_edit']);
        $this->actingAs($user);
    }

    public function test_banner_url_can_be_created_updated_and_removed_and_is_exposed_to_homepage(): void
    {
        $controller = new BannerController();
        $request = Request::create('/', 'POST', ['type' => 1, 'sort' => 1, 'status' => 1,
            'url' => 'https://toriinternational.com/special-offers'], [],
            ['image' => UploadedFile::fake()->image('banner.png')]);
        $controller->addPost($request);
        $banner = Banner::latest('id')->firstOrFail();
        try {
            $this->assertSame('https://toriinternational.com/special-offers', $banner->url);
            $home = app(\App\Http\Controllers\Ecommerce\API\FrontendController::class)->getHomeData()->getData(true);
            $this->assertContains($banner->url, array_column($home['banners'], 'url'));
            $controller->editPost($banner, Request::create('/', 'POST', [
                'type' => 1, 'sort' => 1, 'status' => 1, 'url' => 'https://example.com/products',
            ]));
            $this->assertSame('https://example.com/products', $banner->fresh()->url);
            $controller->editPost($banner, Request::create('/', 'POST', [
                'type' => 1, 'sort' => 1, 'status' => 1, 'url' => '',
            ]));
            $this->assertNull($banner->fresh()->url);
            $this->assertNotNull($banner->fresh()->image);
        } finally {
            $controller->deleteFile($banner->image);
        }
    }

    public function test_unsafe_banner_url_is_rejected_without_changing_banner(): void
    {
        $banner = Banner::create(['type' => 1, 'sort' => 1, 'status' => 1,
            'url' => 'https://example.com']);
        foreach (['javascript:alert(1)', 'data:text/html,test', '//example.com', 'not a url'] as $url) {
            try {
                (new BannerController())->editPost($banner, Request::create('/', 'POST', [
                    'type' => 1, 'sort' => 1, 'status' => 1, 'url' => $url,
                ]));
                $this->fail('Invalid URL should be rejected.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('url', $exception->errors());
            }
        }
        $this->assertSame('https://example.com', $banner->fresh()->url);
    }
}
