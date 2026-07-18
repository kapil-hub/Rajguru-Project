<meta name="application-name" content="{{ config('app.name', 'Rajguru') }}">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Rajguru') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#1d4ed8">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="apple-touch-icon" href="{{ asset('icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512x512.png') }}">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.error('[PWA] Service worker registration failed:', error);
            });
        });
    }
</script>
<script src="{{ asset('pwa-install.js') }}" defer></script>
