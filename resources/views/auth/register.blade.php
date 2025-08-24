<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-label for="name" :value="__('Name')" />

                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <!-- Role Selection -->
            <div class="mt-4">
                <x-label for="role_name" :value="__('Role')" />
                <select id="role_name" name="role_name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <option value="">Select a role</option>
                    <option value="vendor">Vendor</option>
                    <option value="mandor">Mandor</option>
                    <option value="admin">Admin</option>
                    <option value="gis_department">GIS Department</option>
                    <option value="finance">Finance</option>
                    <option value="Assistant Divisi Plantation">Assistant Divisi Plantation</option>
                </select>
            </div>

            <!-- Username -->
            <div class="mt-4">
                <x-label for="username" :value="__('Phone Number (for Vendor) or Email (for other roles)')" />
                <x-input id="username" class="block mt-1 w-full" 
                         type="text" 
                         name="username" 
                         :value="old('username')" 
                         required 
                         autocomplete="username"
                         pattern="[0-9]*"
                         inputmode="numeric"
                         title="Please enter a valid phone number" />
                <div id="username_help" class="text-sm text-gray-500 mt-1">
                    For vendors, please enter phone number. For other roles, please enter email.
                </div>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('Password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div>

            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const roleSelect = document.getElementById('role_name');
                    const usernameInput = document.getElementById('username');
                    const usernameHelp = document.getElementById('username_help');
                    
                    function updateUsernameField() {
                        if (roleSelect.value === 'vendor') {
                            usernameInput.pattern = '[0-9]*';
                            usernameInput.inputMode = 'numeric';
                            usernameInput.title = 'Please enter a valid phone number';
                            usernameHelp.textContent = 'Please enter a valid phone number (numbers only)';
                        } else {
                            usernameInput.pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$';
                            usernameInput.inputMode = 'email';
                            usernameInput.title = 'Please enter a valid email address';
                            usernameHelp.textContent = 'Please enter a valid email address';
                        }
                        // Clear the input when role changes
                        usernameInput.value = '';
                    }
                    
                    // Initial setup
                    updateUsernameField();
                    
                    // Update on role change
                    roleSelect.addEventListener('change', updateUsernameField);
                });
            </script>
            @endpush

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
