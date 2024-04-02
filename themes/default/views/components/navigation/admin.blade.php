<nav class="w-full px-4 bg-primary-800 h-14 flex flex-row justify-between">
    <x-logo class="h-10" />


    <div class="flex flex-row justify-between w-fit items-center" x-data="{ profileMenuOpen: false, mobileMenuOpen: false }">
        <div class="flex flex-row">
            <x-navigation.link :href="route('profile')" class="text-sm">Profile</x-navigation.link>
        </div>

        <div class="flex flex-row">
            <!-- Has notifications? (updates, errors, etc) (TODO) -->
            <div class="relative">
                <button class="flex flex-row items-center border border-primary-700 rounded-md px-2 py-1"
                    x-on:click="profileMenuOpen = !profileMenuOpen">
                    <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full" alt="avatar" />
                    <div class="flex flex-col ml-2">
                        <span class="text-sm text-white sm:hidden">{{ auth()->user()->initials }}</span>
                        <span class="text-sm text-white hidden sm:block">{{ auth()->user()->name }}</span>
                    </div>
                    <!-- arrow down -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-primary-800 rounded-md shadow-lg z-10 border border-primary-700 "
                    x-show="profileMenuOpen" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90" x-on:click.outside="profileMenuOpen = false">
                    <x-navigation.link :href="route('profile')" class="text-sm">Profile</x-navigation.link>
                    {{-- <x-navigation.link :href="route('settings')" class="text-sm">Settings</x-navigation.link> --}}
                    <livewire:auth.logout />
                </div>
            </div>

        </div>
</nav>
