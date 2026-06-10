@props(['title' => null, 'description' => null])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ? $title.' · Hunted' : 'Hunted — Discover the best new products, every day' }}</title>
<meta name="description" content="{{ $description ?? 'Hunted is where makers launch and the community decides. Discover, upvote and discuss the best new products in tech — fresh every single day.' }}">

{{-- Prevent theme flash --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('hunted-theme') || 'light';
            document.documentElement.classList.toggle('dark', t === 'dark');
            document.documentElement.style.colorScheme = t;
        } catch (e) {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|newsreader:400,500,600,700,400i,600i|spline-sans-mono:400,500,600" rel="stylesheet">

<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

<style>[x-cloak]{display:none!important}</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
