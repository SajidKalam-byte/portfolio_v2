{{--
    Google Analytics (gtag.js). Production only, and only when a measurement
    ID is configured — local/staging traffic never gets tracked.
--}}
@php
    $measurementId = config('portfolio.analytics.google_measurement_id');
@endphp
@if ($measurementId && app()->isProduction())
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '{{ $measurementId }}');
    </script>
@endif
