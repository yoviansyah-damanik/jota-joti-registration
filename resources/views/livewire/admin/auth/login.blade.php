<div>
    <div class="mb-6 flex flex-col items-center gap-1 text-center">
        <x-ui.logo class="size-28" />
        <h1 class="mt-2 text-xl font-extrabold leading-tight">{{ $eventName }}</h1>
        <p class="text-base-content/60 text-sm">{{ $organizationName }}</p>
    </div>

    <x-ui.card>
        <div class="mb-2 space-y-1 text-center">
            <h2 class="text-xl font-extrabold">{{ $greeting }}!</h2>
            <p class="text-base-content/60 text-sm">{{ __('Please log in to continue to the admin panel.') }}</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <x-ui.input :label="__('Username')" name="username" type="text" wire:model="username" autocomplete="username" autofocus />

            <div x-data="{ show: false }">
                <x-ui.input :label="__('Password')" name="password" type="password" x-bind:type="show ? 'text' : 'password'" wire:model="password" autocomplete="current-password">
                    <x-slot:append>
                        <button type="button" class="btn join-item btn-square" x-on:click="show = ! show" x-bind:aria-label="show ? @js(__('Hide password')) : @js(__('Show password'))">
                            <x-ui.icon name="eye" class="size-5" />
                        </button>
                    </x-slot:append>
                </x-ui.input>
            </div>

            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" wire:model="remember" class="checkbox checkbox-sm checkbox-primary" />
                <span class="text-base-content text-sm">{{ __('Remember me') }}</span>
            </label>

            <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled">
                <span wire:loading wire:target="login" class="loading loading-spinner loading-sm"></span>
                {{ __('Log in') }}
            </button>
        </form>
    </x-ui.card>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('home') }}" wire:navigate class="link link-hover inline-flex items-center gap-1">
            <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back to registration page') }}
        </a>
    </p>
</div>
