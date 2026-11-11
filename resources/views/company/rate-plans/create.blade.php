<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">New Rate Plan</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-3xl px-6">

            <a href="{{ route('company.rate-plans.index') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to Rate Plans</a>

            @if ($properties->isEmpty())
                <div class="mt-3 rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-100">
                    <p class="text-sm font-medium text-slate-700">You need an active property to create a rate plan.</p>
                    <a href="{{ route('company.properties.create') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">+ Add a property</a>
                </div>
            @else
                <div class="mt-3 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                    <form method="POST" action="{{ route('company.rate-plans.store') }}">
                        @csrf

                        @include('company.rate-plans._form')

                        <p class="mt-6 text-sm text-slate-500">After saving, you will set the weekday and weekend prices.</p>

                        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-6">
                            <a href="{{ route('company.rate-plans.index') }}"
                                class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>

                            <button type="submit"
                                class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                                Save and Set Prices
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>