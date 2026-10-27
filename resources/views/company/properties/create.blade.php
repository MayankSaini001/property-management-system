<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Add Property</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">

                <form method="POST" action="{{ route('company.properties.store') }}">
                    @csrf

                    @include('company.properties._form')

                    <div class="mt-8 flex items-center justify-between">
                        <a href="{{ route('company.properties.index') }}"
                            class="text-sm font-medium text-gray-600 underline hover:text-gray-900">
                            Cancel
                        </a>

                        <button type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                            Save Property
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>