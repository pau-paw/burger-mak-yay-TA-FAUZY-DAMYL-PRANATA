<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoMenuSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

class SecurityAndSetupTest extends ShopTestCase
{
    public function test_csrf_blocks_cross_site_post_without_token(): void
    {
        // Laravel skips CSRF in ordinary tests; explicitly enable that check here.
        $this->app->bind(PreventRequestForgery::class, function ($app) {
            return new class($app, $app['encrypter']) extends PreventRequestForgery
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        $product = $this->product();
        $this->withHeader('Sec-Fetch-Site', 'cross-site')->post('/cart/'.$product->id, ['quantity' => 1])->assertStatus(419);
    }

    public function test_security_headers_are_present_and_blade_escapes_product_content(): void
    {
        $this->product(['name' => '<script>alert(1)</script>']);
        $this->get('/')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_demo_seeder_is_repeatable_without_resetting_existing_stock(): void
    {
        $this->seed(DemoMenuSeeder::class);
        $product = \App\Models\Product::firstOrFail();
        $product->update(['stock' => 7]);
        $this->seed(DemoMenuSeeder::class);
        $this->assertDatabaseCount('products', 8);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_create_admin_command_creates_secure_account(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Uji')
            ->expectsQuestion('Email admin baru', 'admin-test@example.com')
            ->expectsQuestion('Kata sandi (minimal 10 karakter, huruf dan angka)', 'SecureAdmin123')
            ->expectsQuestion('Ulangi kata sandi', 'SecureAdmin123')
            ->expectsOutput('Admin berhasil dibuat. Masuk melalui /login.')
            ->assertExitCode(0);
        $user = User::where('email', 'admin-test@example.com')->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SecureAdmin123', $user->password));
    }
}
