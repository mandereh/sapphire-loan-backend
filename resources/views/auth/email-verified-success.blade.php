<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <a href="/">
                <x-jet-authentication-card-logo />
            </a>
        </x-slot>

        <div class="text-xl mb-2 text-green-600">Success!</div>
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Your email was verified successfully.') }}
        </div>
        <div class="mb-6">
            <a  href="{{ route('dashboard') }}">
                <x-jet-button>
                    Proceed to Dashboard
                </x-jet-button>
            </a>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
    </x-jet-authentication-card>
</x-guest-layout>