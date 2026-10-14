<?php

namespace App\Livewire\Admin\Auth;

use App\Livewire\Concerns\ThrottlesActions;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    use ThrottlesActions;

    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->throttle('admin-login|'.strtolower($this->username), 'username', maxAttempts: 5);

        // Autentikasi menggunakan username (atau email jika format input adalah email)
        $loginField = filter_var($this->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$loginField => $this->username, 'password' => $this->password, 'is_admin' => true];

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('username', __('These credentials do not match our records.'));

            return;
        }

        RateLimiter::clear('admin-login|'.strtolower($this->username).'|'.request()->ip());
        session()->regenerate();

        return $this->redirectIntended(route('admin.dashboard'), navigate: true);
    }

    /**
     * Sapaan sesuai waktu server (zona waktu aplikasi).
     */
    private function greeting(): string
    {
        return match (true) {
            now()->hour < 11 => __('Good morning'),
            now()->hour < 15 => __('Good afternoon'),
            now()->hour < 18 => __('Good evening'),
            default => __('Good night'),
        };
    }

    public function render()
    {
        return view('livewire.admin.auth.login', [
            'greeting' => $this->greeting(),
            'eventName' => app(SettingService::class)->get('event_name'),
            'organizationName' => app(SettingService::class)->get('organization_name'),
        ])->title(__('Admin Login'));
    }
}
