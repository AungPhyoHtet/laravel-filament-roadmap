<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
</head>

<body class="min-h-screen bg-gray-50 text-gray-950 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('features.index') }}" wire:navigate>
                <img src="{{ asset('images/roadmap-light.png') }}" alt="{{ config('app.name') }}" class="h-8">
            </a>

            <nav class="flex items-center gap-3 text-sm">
                @auth
                    <span class="hidden text-gray-600 sm:inline">{{ auth()->user()->name }}</span>
                    <x-filament::button tag="a" :href="filament()->getUrl()" color="gray" size="sm">
                        {{ auth()->user()->is_admin ? 'Admin panel' : 'Dashboard' }}
                    </x-filament::button>
                @else
                    <x-filament::link :href="filament()->getLoginUrl()">Log in</x-filament::link>
                    <x-filament::button tag="a" :href="filament()->getRegistrationUrl()" size="sm">Sign up</x-filament::button>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    @livewire('notifications') {{-- Only required if you wish to send flash notifications --}}

    @filamentScripts
</body>

</html>
