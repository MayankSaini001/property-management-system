<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $company->name }} — Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">
                    Welcome, {{ auth()->user()->name }}
                </h3>

                <p class="mt-2 text-gray-600">
                    Manage your hotels and villas from here.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>