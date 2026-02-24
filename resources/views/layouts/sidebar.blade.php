
@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
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
    <div class="pt-8 pb-7 flex"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-start'">
        <a href="/dashboard">
            <img x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="dark:hidden" src="/images/logo/logo.png" alt="Logo" width="70" height="40" />
            <img x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="hidden dark:block" src="/images/logo/logo.png" alt="Logo" width="70"
                height="40" />
            <img x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                src="/images/logo/logo.png" alt="Logo" width="32" height="32" />

        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
            <div class="flex flex-col gap-4">
                @foreach($menuGroups as $group)

            <div class="mb-6">

                <p class="text-xs text-gray-400 uppercase mb-3">
                    {{ $group['title'] }}
                </p>

                @foreach($group['items'] as $item)
                    @if(!(isset($item['permission']) && $item['permission']))
                        @continue;
                    @endif
                    @php
                        $hasSub = isset($item['subItems']);
                    @endphp

                    {{-- NORMAL LINK --}}
                    @if(!$hasSub && isset($item['path']))

                        @php
                            $active = request()->is(trim($item['path'],'/').'*');
                        @endphp

                        <a href="{{ $item['path'] }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md mb-1
                        {{ $active ? 'text-indigo-600 font-medium' : 'hover:bg-gray-100' }}">

                            {!! MenuHelper::getIconSvg($item['icon']) !!}
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
                                class="w-full flex items-center justify-between px-3 py-2 rounded-md
                                {{ $childActive ? 'text-indigo-600 font-medium' : 'hover:bg-gray-100' }}">

                                <div class="flex items-center gap-3">
                                    {!! MenuHelper::getIconSvg($item['icon']) !!}
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
                                        class="block px-3 py-2 text-sm rounded-md
                                        {{ $subActive ? 'text-indigo-600 font-medium' : 'hover:bg-gray-100' }}">
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
