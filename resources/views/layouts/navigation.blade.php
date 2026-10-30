@php
    $authUser = auth()->user();

    // Role ke hisaab se menu links. Naye modules (Properties, Bookings...) yahan add karna.
    $navLinks = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
    ];

    if ($authUser->isSuperAdmin()) {
        $navLinks[] = ['label' => 'Companies', 'route' => 'superadmin.companies.index', 'active' => 'superadmin.companies.*'];
        $navLinks[] = ['label' => 'Company Admins', 'route' => 'superadmin.company-admins.index', 'active' => 'superadmin.company-admins.*'];
    }

    if ($authUser->isCompanyAdmin()) {
        $navLinks[] = ['label' => 'Properties', 'route' => 'company.properties.index', 'active' => 'company.properties.*'];
        $navLinks[] = ['label' => 'Guests', 'route' => 'company.guests.index', 'active' => 'company.guests.*'];
        $navLinks[] = ['label' => 'Availability', 'route' => 'company.availability.index', 'active' => 'company.availability.*'];
    }

    $roleLabel = $authUser->isSuperAdmin() ? 'Super Admin' : 'Company Admin';
    $initial = strtoupper(mb_substr($authUser->name, 0, 1));
@endphp

<nav x-data="{ open: false }" class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">

                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-lg font-extrabold text-indigo-700 shadow">
                        B
                    </span>
                    <span class="text-xl font-bold tracking-tight text-white">
                        base<span class="text-indigo-200">NEXA</span>
                    </span>
                </a>

                <!-- Navigation Links (desktop) -->
                <div class="hidden sm:ms-10 sm:flex sm:items-center sm:gap-1">
                    @foreach ($navLinks as $link)
                                    <a href="{{ route($link['route']) }}" class="rounded-md px-3 py-2 text-sm font-medium transition duration-150 ease-in-out
                                                {{ request()->routeIs($link['active'])
                        ? 'bg-white/20 text-white'
                        : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                                        {{ $link['label'] }}
                                    </a>
                    @endforeach
                </div>
            </div>

            <!-- User menu (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center gap-3 rounded-full bg-white/10 py-1.5 pe-3 ps-1.5 text-sm font-medium text-white transition hover:bg-white/20 focus:outline-none">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-sm font-bold text-indigo-700">
                                {{ $initial }}
                            </span>

                            <span class="text-left leading-tight">
                                <span class="block text-sm font-semibold">{{ $authUser->name }}</span>
                                <span class="block text-xs text-indigo-200">{{ $roleLabel }}</span>
                            </span>

                            <svg class="h-4 w-4 fill-current text-indigo-200" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-gray-100 px-4 py-3">
                            <p class="text-sm font-semibold text-gray-800">{{ $authUser->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $authUser->email }}</p>
                            @if ($authUser->company)
                                <p class="mt-1 truncate text-xs font-medium text-indigo-600">{{ $authUser->company->name }}
                                </p>
                            @endif
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-indigo-100 transition hover:bg-white/10 hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-white/10 sm:hidden">
        <div class="space-y-1 px-3 pb-3 pt-2">
            @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-base font-medium
                                {{ request()->routeIs($link['active'])
                ? 'bg-white/20 text-white'
                : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-white/10 pb-3 pt-4">
            <div class="flex items-center gap-3 px-4">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-base font-bold text-indigo-700">
                    {{ $initial }}
                </span>
                <div>
                    <div class="text-base font-semibold text-white">{{ $authUser->name }}</div>
                    <div class="text-sm text-indigo-200">{{ $authUser->email }}</div>
                    <div class="text-xs text-indigo-200">{{ $roleLabel }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-3">
                <a href="{{ route('profile.edit') }}"
                    class="block rounded-md px-3 py-2 text-base font-medium text-indigo-100 hover:bg-white/10 hover:text-white">
                    {{ __('Profile') }}
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="block w-full rounded-md px-3 py-2 text-left text-base font-medium text-indigo-100 hover:bg-white/10 hover:text-white">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>