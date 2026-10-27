<x-app-layout>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Companies Management
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Page heading and Add Company button --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        All Companies
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage your companies and their settings.
                    </p>
                </div>


                <a href="{{ route('superadmin.companies.create') }}"
                    style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 10px 18px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                    + Add Company
                </a>

            </div>

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto p-6">

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Company
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Email
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Currency
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">

                            @forelse ($companies as $company)
                                                    <tr>
                                                        <td class="px-4 py-3 font-medium text-gray-900">
                                                            {{ $company->name }}
                                                        </td>

                                                        <td class="px-4 py-3 text-gray-600">
                                                            {{ $company->email ?? '—' }}
                                                        </td>

                                                        <td class="px-4 py-3 text-gray-600">
                                                            {{ $company->currency }}
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <span class="rounded-full px-3 py-1 text-xs font-medium
                                                                    {{ $company->status === 'active'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-700' }}">
                                                                {{ ucfirst($company->status) }}
                                                            </span>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-3">

                                                                <a href="{{ route('superadmin.companies.edit', $company) }}"
                                                                    class="text-indigo-600 hover:underline">
                                                                    Edit
                                                                </a>

                                                                @if ($company->status === 'active')
                                                                    <form method="POST"
                                                                        action="{{ route('superadmin.companies.destroy', $company) }}"
                                                                        onsubmit="return confirm('Deactivate this company?')">
                                                                        @csrf
                                                                        @method('DELETE')

                                                                        <button type="submit" class="text-red-600 hover:underline">
                                                                            Deactivate
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                            </div>
                                                        </td>
                                                    </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                        No companies found. Click
                                        <strong>+ Add Company</strong>
                                        to create your first company.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>

                    <div class="mt-5">
                        {{ $companies->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>