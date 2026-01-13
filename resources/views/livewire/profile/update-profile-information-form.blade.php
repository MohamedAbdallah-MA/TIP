<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $token = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->token = Session::get('api_token', '');
    }

    /**
     * Generate a new API token.
     */
    public function generateToken(): void
    {
        $user = Auth::user();
        $user->tokens()->delete();

        $this->token = $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('API Token') }}
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This is your bearer token for API access. It is only shown once after generation for security.') }}
            </p>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end" 
                 x-data="{ 
                    token: @entangle('token'),
                    copy() {
                        if (!this.token) return;
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(this.token).then(() => {
                                $dispatch('show-copy-message');
                            });
                        } else {
                            let textArea = document.createElement('textarea');
                            textArea.value = this.token;
                            textArea.style.position = 'fixed';
                            textArea.style.left = '-9999px';
                            textArea.style.top = '0';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            try {
                                document.execCommand('copy');
                                $dispatch('show-copy-message');
                            } catch (err) {
                                console.error('Unable to copy', err);
                            }
                            document.body.removeChild(textArea);
                        }
                    }
                 }">
                <div class="flex-grow">
                    <x-text-input 
                        id="api_token" 
                        type="text" 
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-800" 
                        x-bind:value="token || '{{ __('****************') }}'" 
                        disabled 
                    />
                </div>

                <div class="flex gap-2 shrink-0">
                    <x-secondary-button 
                        type="button"
                        x-on:click="copy()"
                        x-bind:disabled="!token"
                    >
                        {{ __('Copy') }}
                    </x-secondary-button>

                    <x-secondary-button 
                        type="button" 
                        wire:click="generateToken"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="generateToken">{{ __('Generate New') }}</span>
                        <span wire:loading wire:target="generateToken">{{ __('Generating...') }}</span>
                    </x-secondary-button>
                </div>

                <x-action-message class="me-3" on="show-copy-message">
                    {{ __('Copied to clipboard.') }}
                </x-action-message>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
