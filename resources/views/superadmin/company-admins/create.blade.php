
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Add Company Admin
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-6">
            <div class="rounded-lg bg-white p-6 shadow-sm">

                @if ($errors->any())
                    <div class="mb-5 rounded-md bg-red-100 p-4 text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('superadmin.company-admins.store') }}">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="mb-1 block font-medium">
                                Admin Name *
                            </label>
                            <input id="name" name="name" type="text"
                                   value="{{ old('name') }}" required
                                   class="w-full rounded-md border-gray-300"
                                   placeholder="Enter admin name">
                        </div>

                        <div>
                            <label for="email" class="mb-1 block font-medium">
                                Email *
                            </label>
                            <input id="email" name="email" type="email"
                                   value="{{ old('email') }}" required
                                   class="w-full rounded-md border-gray-300"
                                   placeholder="admin@example.com">
                        </div>

                        <div>
                            <label for="company_id" class="mb-1 block font-medium">
                                Assign Company *
                            </label>
                            <select id="company_id" name="company_id" required
                                    class="w-full rounded-md border-gray-300">
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        @selected(old('company_id') == $company->id)>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="password" class="mb-1 block font-medium">
                                Password *
                            </label>
                            <input id="password" name="password" type="password"
                                   required minlength="8"
                                   class="w-full rounded-md border-gray-300">
                        </div>

                        <div>
                            <label for="password_confirmation"
                                   class="mb-1 block font-medium">
                                Confirm Password *
                            </label>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password" required minlength="8"
                                   class="w-full rounded-md border-gray-300">
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <a href="{{ route('superadmin.company-admins.index') }}"
                           style="color:#4b5563; text-decoration:underline;">
                            Cancel
                        </a>

                        <button type="submit"
                                style="background:#4f46e5;color:white;padding:10px 18px;border-radius:6px;font-weight:600;">
                            Create Company Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
