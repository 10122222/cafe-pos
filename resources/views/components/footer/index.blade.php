@if (\Filament\Facades\Filament::auth()->check())
    <footer
        class="fixed bottom-0 left-0 z-20 flex h-8 w-full items-center gap-x-2 bg-white px-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-3 lg:px-4"
    >
        <span class="text-sm text-gray-950 dark:text-white">
            &copy; {{ now()->format('Y') }} Muhammad Irkham Nurmauludifa -
            10122222 - IF-6 All Rights Reserved.
        </span>
    </footer>
@endif
