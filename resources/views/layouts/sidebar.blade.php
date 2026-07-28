
@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 left-0 bg-white text-gray-900 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-200 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
    x-data="{
        openSubmenus: {},
        init() {
            // Auto-open Dashboard menu on page load
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        // Check if any submenu item matches current path
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            }
                        @endforeach
                    @endif
                @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            // Close all other submenus when opening a new one
            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">
    <!-- Logo Section -->
    <div class="flex items-center justify-center pt-7 pb-6"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-center'">
        <a href="/dashboard" class="flex items-center justify-center">
            <img x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="sidebar-logo h-16 w-16 rounded-full object-contain dark:hidden" src="/images/logo/logo.png" alt="Logo" />
            <img x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="sidebar-logo hidden h-16 w-16 rounded-full object-contain dark:block" src="/images/logo/logo.png" alt="Logo" />
            <img x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                class="sidebar-logo-icon h-11 w-11 rounded-full object-contain" src="/images/logo/logo.png" alt="Logo" />

        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
            <div class="flex flex-col gap-4">
                @foreach($menuGroups as $group)

            <div class="mb-6">

                <p class="text-xs font-semibold text-gray-400 uppercase mb-3 dark:text-gray-500">
                    {{ $group['title'] }}
                </p>

                @foreach($group['items'] as $item)
                    @if(isset($item['permission']) && !$item['permission'])
                        @continue;
                    @endif
                    @php
                        $hasSub = isset($item['subItems']);
                    @endphp

                    {{-- NORMAL LINK --}}
                    @if(!$hasSub && isset($item['path']))

                        @php
                            $itemPath = trim($item['path'], '/');
                            $active = $itemPath !== ''
                                && (
                                    request()->path() === $itemPath
                                    || request()->routeIs($item['activeRoutes'] ?? '')
                                );
                        @endphp

                        <a href="{{ $item['path'] }}"
                        class="group flex items-center gap-3 px-3 py-2 rounded-md mb-1 text-sm transition
                        {{ $active ? 'bg-indigo-50 text-indigo-600 font-medium dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">

                            <span class="shrink-0 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-white">
                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                            </span>
                            <span>{{ $item['name'] }}</span>
                        </a>

                    {{-- SUBMENU --}}
                    @elseif($hasSub)

                       @php
                            $childActive = false;
                            foreach($item['subItems'] as $sub){
                                if(isset($sub['path']) && request()->path() === ltrim($sub['path'],'/')){
                                    $childActive = true;
                                }
                            }
                        @endphp

                        <div x-data="{ open: {{ $childActive ? 'true' : 'false' }} }">

                            <button @click="open = !open"
                                class="group w-full flex items-center justify-between px-3 py-2 rounded-md text-sm transition
                                {{ $childActive ? 'bg-indigo-50 text-indigo-600 font-medium dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">

                                <div class="flex items-center gap-3">
                                    <span class="shrink-0 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-white">
                                        {!! MenuHelper::getIconSvg($item['icon']) !!}
                                    </span>
                                    <span>{{ $item['name'] }}</span>
                                </div>

                                <span :class="open ? 'rotate-180' : ''" class="transition">
                                    ▼
                                </span>
                            </button>

                            <div x-show="open" x-collapse class="ml-8 mt-1">

                                @foreach($item['subItems'] as $sub)

                                    @if(isset($sub['path']))

                                        @php
                                            $subActive = request()->path() === ltrim($sub['path'],'/');
                                        @endphp

                                        <a href="{{ $sub['path'] }}"
                                        class="block px-3 py-2 text-sm rounded-md transition
                                        {{ $subActive ? 'bg-indigo-50 text-indigo-600 font-medium dark:bg-indigo-500/15 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                            {{ $sub['name'] }}
                                        </a>

                                    @endif

                                @endforeach

                            </div>
                        </div>

                    @endif

                @endforeach

            </div>

        @endforeach
            </div>
        </nav>

        <!-- Sidebar Widget -->
        <div x-data x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" x-transition class="mt-auto">
            @include('layouts.sidebar-widget')
        </div>

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>
