<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends ShopTestCase
{
    public function test_public_forms_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Kata sandi');
        $this->get('/register')->assertOk()->assertSee('Buat akun pelanggan');
    }

    public function test_registration_hashes_password_and_cannot_assign_admin_role(): void
    {
        $this->post('/register', ['name' => 'Fauzy', 'email' => 'CUSTOMER@example.com', 'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'role' => 'admin'])->assertRedirect('/');
        $user = User::where('email', 'customer@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('customer', $user->role);
        $this->assertTrue(Hash::check('SecurePass123', $user->password));
        $this->get('/admin')->assertForbidden();
    }

    public function test_weak_password_and_duplicate_email_are_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $this->post('/register', ['name' => 'Test', 'email' => 'taken@example.com', 'password' => 'abc', 'password_confirmation' => 'xyz'])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $user = $this->admin();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'wrong'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_logout_invalidates_customer_session(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['cart' => [1 => 2]])->post('/logout')->assertRedirect('/')->assertSessionMissing('cart');
        $this->assertGuest();
    }

    public function test_guests_cannot_open_checkout_or_admin_and_customers_cannot_mutate_admin(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->post('/admin/categories', ['name' => 'Forbidden'])->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Forbidden']);
    }
}
