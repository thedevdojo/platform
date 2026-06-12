@props(['title' => null, 'description' => null])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ? $title.' · Relay' : 'Relay — Project management with momentum' }}</title>
<meta name="description" content="{{ $description ?? 'Relay is a beautifully fast project management tool for teams who ship. Plan, prioritize and track work without the clutter.' }}">

{{-- Prevent theme flash --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('relay-theme') || 'dark';
            document.documentElement.classList.toggle('dark', t === 'dark');
            document.documentElement.style.colorScheme = t;
        } catch (e) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=geist:300,400,500,600,700|geist-mono:400,500" rel="stylesheet">

<style>[x-cloak]{display:none!important}</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
