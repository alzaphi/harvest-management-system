<header class="bg-white shadow-sm z-10">
    <div class="flex items-center justify-between px-6 py-3">
        <!-- Left Section -->
        <div class="flex items-center">
            <button id="sidebar-toggle" class="block md:hidden text-gray-500 hover:text-gray-700 focus:outline-none mr-4">
                <i class="fas fa-bars text-lg"></i>
            </button>
            
            @php
                // Default breadcrumb jika tidak didefinisikan
                $breadcrumb = $breadcrumb ?? [
                    ['title' => 'Dashboard', 'url' => route('dashboard')],
                    ['title' => $header ?? 'Dashboard']
                ];
            @endphp
            
            <div class="topbar-breadcrumb">
                @foreach ($breadcrumb as $key => $crumb)
                    @if ($key > 0)
                        <span class="breadcrumb-separator">/</span>
                    @endif
                    @if ($key === count($breadcrumb) - 1 || !isset($crumb['url']))
                        <span class="breadcrumb-item active">{{ $crumb['title'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}" class="breadcrumb-item">{{ $crumb['title'] }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-4">
            <!-- Notification -->
            <div class="relative">
                <button class="text-gray-500 hover:text-gray-700 focus:outline-none relative">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">2</span>
                </button>
            </div>
            
            <!-- User Menu -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button 
                    @click="open = !open"
                    class="flex items-center text-gray-700 hover:text-gray-900 focus:outline-none transition-colors duration-200"
                    :class="{ 'text-blue-600': open }"
                >
                    <i class="fas fa-user-circle text-2xl mr-2"></i>
                    <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg overflow-hidden z-50 border border-gray-100"
                    style="display: none;"
                >
                    <button 
                        type="button" 
                        onclick="window.location='{{ route('profile') }}'"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100 transition-colors duration-150"
                    >
                        <i class="fas fa-user mr-2 text-gray-500"></i> Profile
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button 
                            type="submit" 
                            class="block w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                        >
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</header>