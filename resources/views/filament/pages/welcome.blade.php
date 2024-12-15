@php
    use Composer\InstalledVersions;

    $version = InstalledVersions::getVersion('swisnl/filament-backgrounds');
    $backgroundImage = asset('images/swisnl/filament-backgrounds/curated-by-swis/26.jpg');
    $styleVersion = asset("css/swisnl/filament-backgrounds/filament-backgrounds-styles.css?v=$version");
@endphp

@pushOnce('styles')
    <link href="{{ $styleVersion }}" rel="stylesheet" data-navigate-track />
    <style>
        :root {
            --filament-backgrounds-image: url('{{ $backgroundImage }}');
        }
    </style>
@endPushOnce

<x-filament-panels::page.simple>
    @livewire('home')
</x-filament-panels::page.simple>
