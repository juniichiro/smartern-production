@props(['portalType', 'userName', 'userRole', 'showSidebar' => true, 'showNotifications' => true, 'fullHeight' => false])

<div class="flex {{ $fullHeight ? 'h-screen overflow-hidden' : 'min-h-screen' }} bg-gray-50 dark:bg-gray-950 font-sans text-[15px] transition-colors duration-300">
    @if($showSidebar)
        <x-sidebar :portalType="$portalType" :userName="$userName" :userRole="$userRole" />
    @endif

    <main class="{{ $showSidebar ? 'lg:ml-64' : '' }} {{ $showNotifications ? 'lg:mr-80' : '' }} flex-1 p-3 sm:p-6 lg:p-10 text-left mt-14 lg:mt-0 transition-all duration-300 flex flex-col min-w-0 overflow-x-hidden {{ $fullHeight ? 'overflow-hidden' : '' }}">
        {{ $slot }}
    </main>

    @if($showNotifications)
        <x-notification-sidebar :notifications="$notifications" />
    @endif
</div>
