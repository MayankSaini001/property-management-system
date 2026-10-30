<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Guest - {{ $guest->fullName() }}</h2>
    </x-slot>

    <div class="min-h-[75vh] bg-gradient-to-b from-indigo-50 via-slate-50 to-white py-10">
        <div class="mx-auto max-w-4xl px-6">

            <a href="{{ route('company.guests.show', $guest) }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-800">&larr; Back to profile</a>

            <div class="mt-3 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <form method="POST" action="{{ route('company.guests.update', $guest) }}">
                    @csrf
                    @method('PUT')

                    @include('company.guests._form')

                    <div class="mt-10 flex items-center justify-between border-t border-slate-100 pt-6">
                        <a href="{{ route('company.guests.show', $guest) }}"
                            class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>

                        <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-600 hover:to-violet-600">
                            Update Guest
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>