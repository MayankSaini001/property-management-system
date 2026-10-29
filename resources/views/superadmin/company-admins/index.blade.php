<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Company Admins
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-none px-6">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        All Company Admins
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage admins and their assigned companies.
                    </p>
                </div>


                <a href="{{ route('superadmin.company-admins.create') }}"
                    style="display:inline-block; background-color:#4f46e5; color:#ffffff; padding:10px 18px; border-radius:6px; font-size:14px; font-weight:600; text-decoration:none; border:1px solid #4f46e5;">
                    + Add Company Admin
                </a>

            </div>

            <div class="overflow-x-auto rounded-lg bg-white p-6 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Name
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Email
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Assigned Company
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                Role
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse ($admins as $admin)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">
                                    {{ $admin->name }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $admin->email }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $admin->company?->name ?? 'Not assigned' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    No Company Admins found. Click "Add Company Admin" to create your first admin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-5">
                    {{ $admins->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>