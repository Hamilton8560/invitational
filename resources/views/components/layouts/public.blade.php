<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        <!-- Public Navbar -->
        <nav class="border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800" x-data="{ mobileMenuOpen: false }">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 rtl:space-x-reverse">
                        <x-app-logo />
                    </a>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="{{ route('events.index') }}"
                           class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors {{ request()->routeIs('events.*') ? 'text-zinc-900 dark:text-white' : '' }}">
                            Events
                        </a>
                    </div>

                    <!-- Desktop Auth Buttons -->
                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 rounded-lg transition-colors">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>

                    <!-- Mobile Menu Button -->
                    <button type="button"
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                            @click="mobileMenuOpen = !mobileMenuOpen">
                        <flux:icon.bars-3 class="size-6" x-show="!mobileMenuOpen" />
                        <flux:icon.x-mark class="size-6" x-show="mobileMenuOpen" x-cloak />
                    </button>
                </div>

                <!-- Mobile Menu -->
                <div class="md:hidden pb-4" x-show="mobileMenuOpen" x-cloak x-transition>
                    <div class="flex flex-col gap-2 pt-2">
                        <a href="{{ route('events.index') }}"
                           class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors {{ request()->routeIs('events.*') ? 'bg-zinc-100 dark:bg-zinc-700' : '' }}">
                            Events
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="px-3 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 rounded-lg transition-colors text-center">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mt-20">
            <div class="container mx-auto px-4 py-12 max-w-7xl">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Brand -->
                    <div>
                        <x-app-logo />
                        <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                            The Invitational hosts elite multi-sport tournaments featuring competitive divisions and substantial cash prizes.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('events.index') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                                    Browse Events
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.contact') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                                    Contact Us
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Legal</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('legal.terms') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                                    Terms of Service
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.privacy') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.refunds') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                                    Refund Policy
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Account -->
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Account</h3>
                        <ul class="space-y-2">
                            @auth
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                        Settings
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                        Log In
                                    </a>
                                </li>
                                @if (Route::has('register'))
                                    <li>
                                        <a href="{{ route('register') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                            Register
                                        </a>
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 text-center">
                        © {{ date('Y') }} The Invitational. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
