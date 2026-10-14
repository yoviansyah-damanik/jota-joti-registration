<?php

namespace Tests\Feature;

use App\Livewire\Admin\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'is_admin' => true,
        ]);
    }

    public function test_guests_cannot_access_profile_page(): void
    {
        $this->get(route('admin.profile'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_profile_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSeeLivewire(Profile::class)
            ->assertSee('Super Admin')
            ->assertSee('superadmin')
            ->assertSee('admin@example.com');
    }

    public function test_admin_can_update_profile_info(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Profile::class)
            ->set('name', 'Admin Baru')
            ->set('username', 'adminbaru')
            ->set('email', 'adminbaru@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->admin->refresh();
        $this->assertSame('Admin Baru', $this->admin->name);
        $this->assertSame('adminbaru', $this->admin->username);
        $this->assertSame('adminbaru@example.com', $this->admin->email);
    }

    public function test_admin_cannot_use_existing_username_or_email_from_other_users(): void
    {
        User::factory()->create([
            'username' => 'otheruser',
            'email' => 'other@example.com',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Profile::class)
            ->set('username', 'otheruser')
            ->set('email', 'other@example.com')
            ->call('updateProfile')
            ->assertHasErrors(['username' => 'unique', 'email' => 'unique']);
    }

    public function test_admin_can_update_password_with_valid_current_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Profile::class)
            ->set('current_password', 'secret123')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->admin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->admin->password));
    }

    public function test_admin_cannot_update_password_with_wrong_current_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Profile::class)
            ->set('current_password', 'wrongpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->admin->refresh();
        $this->assertTrue(Hash::check('secret123', $this->admin->password));
    }

    public function test_admin_cannot_update_password_with_mismatched_confirmation(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Profile::class)
            ->set('current_password', 'secret123')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'mismatch123')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'confirmed']);
    }
}
