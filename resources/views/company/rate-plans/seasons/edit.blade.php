<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Season - {{ $season->name }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-6">

            <a href="{{ route('company.rate-plans.edit', $ratePlan) }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to {{ $ratePlan->name }}</a>

            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <form method="POST" action="{{ route('company.rate-plan-seasons.update', $season) }}">
                    @csrf
                    @method('PUT')

                    @include('company.rate-plans.seasons._form')

                    <div class="mt-10 flex items-center justify-between border-t border-slate-100 pt-6">
                        <a href="{{ route('company.rate-plans.edit', $ratePlan) }}"
                            class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>

                        <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                            Update Season
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <h4 class="text-sm font-semibold text-rose-700">Remove season</h4>
                <p class="mt-1 text-sm text-slate-500">Bookings that are already saved keep the prices they were booked with.</p>

                <form method="POST" action="{{ route('company.rate-plan-seasons.destroy', $season) }}" class="mt-4"
                    onsubmit="return confirm('Remove this season?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-xl bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">
                        Remove Season
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>