@props(['title' => null, 'description' => null])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ? $title.' · Deskly' : 'Deskly — Customer support without the chaos' }}</title>
<meta name="description" content="{{ $description ?? 'Deskly is a beautifully calm help desk for teams who care about response times. A shared inbox, knowledge base, and CSAT — without the bloat.' }}">

{{-- Prevent theme flash (light-first; honors a stored dark preference) --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('deskly-theme') || 'light';
            document.documentElement.classList.toggle('dark', t === 'dark');
            document.documentElement.style.colorScheme = t;
        } catch (e) {
            // default to light
        }
    })();
</script>

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=bricolage-grotesque:400,500,600,700,800|hanken-grotesk:400,500,600,700|ibm-plex-mono:400,500" rel="stylesheet">

<style>[x-cloak]{display:none!important}</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
