
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Add Company
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-6">

            <div class="rounded-lg bg-white p-6 shadow-sm">

                <form method="POST" action="{{ route('superadmin.companies.store') }}">
                    @csrf

                    <div class="space-y-5">

                        <div>
                            <label for="name" class="mb-1 block font-medium text-gray-700">
                                Company Name *
                            </label>
                            <input id="name" name="name" type="text"
                                   value="{{ old('name') }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Enter company name">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1 block font-medium text-gray-700">
                                Company Email
                            </label>
                            <input id="email" name="email" type="email"
                                   value="{{ old('email') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="company@example.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-1 block font-medium text-gray-700">
                                Phone
                            </label>
                            <input id="phone" name="phone" type="text"
                                   value="{{ old('phone') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Company phone number">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="mb-1 block font-medium text-gray-700">
                                Default Currency *
                            </label>
                            <select id="currency" name="currency" required
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="GBP" @selected(old('currency', 'GBP') === 'GBP')>GBP — British Pound</option>
                                <option value="INR" @selected(old('currency') === 'INR')>INR — Indian Rupee</option>
                                <option value="EUR" @selected(old('currency') === 'EUR')>EUR — Euro</option>
                                <option value="USD" @selected(old('currency') === 'USD')>USD — US Dollar</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="mb-1 block font-medium text-gray-700">
                                Status *
                            </label>
                            <select id="status" name="status" required
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <a href="{{ route('superadmin.companies.index') }}"
                           style="color: #4b5563; text-decoration: underline;">
                            Cancel
                        </a>

                        <button type="submit"
                                style="background-color: #4f46e5; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600;">
                            Save Company
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
