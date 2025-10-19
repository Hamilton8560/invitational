<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br via-black from-stone-900 to-stone-800">
        <!-- Public Navbar -->
        <nav class="px-6 w-full lg:px-7" x-data="{ mobileMenuOpen: false }">
            <div class="mx-auto max-w-6xl">
                <div class="flex relative flex-wrap justify-between items-center mx-auto w-full h-20 font-medium md:items-center lg:h-24 md:justify-between">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center w-1/4 text-xl font-bold text-white">
                        <x-app-logo />
                    </a>

                    <!-- Desktop Navigation - Centered Pills -->
                    <div :class="mobileMenuOpen ? 'flex' : 'hidden md:flex'" class="absolute top-0 left-1/2 z-50 flex-col justify-center items-center px-2 mt-2 w-full h-auto text-center rounded-full border-0 -translate-x-1/2 md:mt-0 md:top-auto text-stone-400 border-stone-700 md:border md:w-auto md:h-10 md:flex-row md:items-center">
                        <a href="{{ route('home') }}"
                           class="inline-block relative px-4 py-5 mx-2 w-full h-full font-medium leading-tight text-center duration-300 ease-out md:py-2 group {{ request()->routeIs('home') ? 'text-white' : 'hover:text-white' }} md:w-auto md:px-2 lg:mx-3 md:text-center">
                            <span>Home</span>
                            <span class="absolute bottom-0 h-px bg-gradient-to-r duration-300 ease-out translate-y-px {{ request()->routeIs('home') ? 'left-0 w-full' : 'left-1/2 w-0 group-hover:left-0 group-hover:w-full' }} md:from-stone-700 md:via-stone-400 md:to-stone-700 from-stone-900 via-stone-600 to-stone-900"></span>
                        </a>
                        <a href="{{ route('events.index') }}"
                           class="inline-block relative px-4 py-5 mx-2 w-full h-full font-medium leading-tight text-center duration-300 ease-out md:py-2 group {{ request()->routeIs('events.*') ? 'text-white' : 'hover:text-white' }} md:w-auto md:px-2 lg:mx-3 md:text-center">
                            <span>Events</span>
                            <span class="absolute bottom-0 h-px bg-gradient-to-r duration-300 ease-out translate-y-px {{ request()->routeIs('events.*') ? 'left-0 w-full' : 'left-1/2 w-0 group-hover:left-0 group-hover:w-full' }} md:from-stone-700 md:via-stone-400 md:to-stone-700 from-stone-900 via-stone-600 to-stone-900"></span>
                        </a>
                        <a href="{{ route('legal.contact') }}"
                           class="inline-block relative px-4 py-5 mx-2 w-full h-full font-medium leading-tight text-center duration-300 ease-out md:py-2 group {{ request()->routeIs('legal.contact') ? 'text-white' : 'hover:text-white' }} md:w-auto md:px-2 lg:mx-3 md:text-center">
                            <span>Contact</span>
                            <span class="absolute bottom-0 h-px bg-gradient-to-r duration-300 ease-out translate-y-px {{ request()->routeIs('legal.contact') ? 'left-0 w-full' : 'left-1/2 w-0 group-hover:left-0 group-hover:w-full' }} md:from-stone-700 md:via-stone-400 md:to-stone-700 from-stone-900 via-stone-600 to-stone-900"></span>
                        </a>
                    </div>

                    <!-- Desktop Auth Buttons -->
                    <div :class="mobileMenuOpen ? 'flex' : 'hidden md:flex'" class="fixed top-0 left-0 z-40 items-center p-3 w-full h-full text-sm bg-opacity-50 bg-stone-900 md:w-auto md:bg-transparent md:p-0 md:relative">
                        <div class="overflow-hidden flex-col items-center p-3 w-full h-full bg-black bg-opacity-50 rounded-lg backdrop-blur-lg select-none md:p-0 md:h-auto md:bg-transparent md:rounded-none md:relative md:flex md:flex-row md:overflow-auto">
                            <div class="flex flex-col justify-end items-center pt-2 w-full h-full md:w-full md:flex-row md:py-0">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="py-5 mr-0 w-full text-center text-stone-200 md:py-3 md:w-auto hover:text-white md:pl-0 md:mr-3 lg:mr-5">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="py-5 mr-0 w-full text-center text-stone-200 md:py-3 md:w-auto hover:text-white md:pl-0 md:mr-3 lg:mr-5">
                                        Sign In
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-3 w-full font-medium leading-6 text-center bg-white rounded-lg border border-transparent transition duration-150 ease-in-out md:py-1.5 whitespace-nowrap text-stone-600 md:w-auto md:rounded-full hover:bg-white focus:outline-none focus:border-stone-700 focus:shadow-outline-gray active:bg-stone-700">
                                            Sign Up
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <div @click="mobileMenuOpen = !mobileMenuOpen"
                         :class="mobileMenuOpen ? 'text-stone-400' : 'text-stone-100'"
                         class="flex absolute right-0 z-50 flex-col items-end p-2 mr-2 w-10 h-10 rounded-full -translate-y-0.5 cursor-pointer md:hidden hover:bg-stone-200/10">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-stone-700/50 mt-20">
            <div class="container mx-auto px-4 py-12 max-w-6xl">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Brand -->
                    <div>
                        <x-app-logo />
                        <p class="mt-4 text-sm text-stone-300">
                            The Invitational hosts elite multi-sport tournaments featuring competitive divisions and substantial cash prizes.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-white mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('events.index') }}" class="text-sm text-stone-300 hover:text-white transition-colors" wire:navigate>
                                    Browse Events
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.contact') }}" class="text-sm text-stone-300 hover:text-white transition-colors" wire:navigate>
                                    Contact Us
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold text-white mb-4">Legal</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('legal.terms') }}" class="text-sm text-stone-300 hover:text-white transition-colors" wire:navigate>
                                    Terms of Service
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.privacy') }}" class="text-sm text-stone-300 hover:text-white transition-colors" wire:navigate>
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('legal.refunds') }}" class="text-sm text-stone-300 hover:text-white transition-colors" wire:navigate>
                                    Refund Policy
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Account -->
                    <div>
                        <h3 class="text-sm font-semibold text-white mb-4">Account</h3>
                        <ul class="space-y-2">
                            @auth
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-sm text-stone-300 hover:text-white transition-colors">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="text-sm text-stone-300 hover:text-white transition-colors">
                                        Settings
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}" class="text-sm text-stone-300 hover:text-white transition-colors">
                                        Log In
                                    </a>
                                </li>
                                @if (Route::has('register'))
                                    <li>
                                        <a href="{{ route('register') }}" class="text-sm text-stone-300 hover:text-white transition-colors">
                                            Register
                                        </a>
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 pt-8 border-t border-stone-700/50">
                    <p class="text-sm text-stone-400 text-center">
                        © {{ date('Y') }} The Invitational. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
