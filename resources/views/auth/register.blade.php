<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>


        
                <!-- employee_number -->
        <div class="mt-4">
            <x-input-label for="employee_number" :value="__('社員番号')" />
            <x-text-input id="employee_number" class="block mt-1 w-full" 
            type="text" 
            name="employee_number" 
            :value="old('employee_number')" 
            required autocomplete="off" />
            <x-input-error :messages="$errors->get('employee_number')" class="mt-2" />
        </div>
        
        <!-- affiliation -->
        <div class="mt-4">
            <x-input-label for="affiliation" :value="__('所属')" />
            <x-text-input id="affiliation" class="block mt-1 w-full" 
            type="text" 
            name="affiliation" 
            :value="old('affiliation')" 
            required autocomplete="off" />
            <x-input-error :messages="$errors->get('affiliation')" class="mt-2" />
        </div>
        
        <!-- affiliation -->
        <div class="mt-4">
            <x-input-label for="position" :value="__('役職')" />
            <x-text-input id="position" class="block mt-1 w-full" 
            type="text" 
            name="position" 
            :value="old('position')" 
            required autocomplete="off" />
            <x-input-error :messages="$errors->get('position')" class="mt-2" />
        </div>
        

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
