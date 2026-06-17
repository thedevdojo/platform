<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Welcome') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex items-center justify-center w-screen h-screen bg-black bg-gradient-radial">
            <div class="relative flex flex-col justify-center min-h-screen py-6 sm:py-12" x-cloak>
                <div class="relative px-6 pt-8 bg-black shadow-xl pb-9 ring-1 ring-gray-200/[15%] sm:mx-auto sm:max-w-md sm:rounded-2xl sm:px-10">
                    <div class="absolute top-0 right-0 w-full h-px -translate-y-px opacity-100 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 w-full h-px translate-y-px opacity-100 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                    <div class="max-w-md mx-auto">
                        <div class="flex items-center justify-center w-full h-auto py-2">
                            <a href="/" class="h-5 text-2xl flex items-center space-x-1.5 text-white font-semibold">
    <svg class="size-7" viewBox="0 0 39 39" fill="none" aria-hidden="true">
                <path fill="#ffffff" d="M13.71 13.71v11.495h11.495V13.71zM0 27.504V39h11.496V27.504zM0 0v11.496h11.496V0z"></path>
                <path fill="#ffffff" fill-opacity=".2" d="M27.41 13.71v11.495h11.496V13.71zM13.701 27.504V39h11.496V27.504zM13.701 0v11.496h11.496V0z"></path>
            </svg>
</a>
                        </div>
                        <div class="relative">
                            <div class="pt-5 pb-6 space-y-6 text-sm font-light leading-6 text-center text-white/90">
                                <p>Welcome to the <strong>DevDojo Platform</strong>. It's time to build your app in record time, and have fun while you do it!</p>
                            </div>
                            <div class="absolute w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                            <div class="flex items-center pt-8 text-xs font-normal leading-7">
                                <p class="w-1/2 text-center flex items-center justify-center pr-2.5">
                                    <a href="https://static.devdojo.com/docs" target="_blank" class="flex items-center justify-center w-full px-3 py-1 space-x-1 border rounded-lg text-neutral-200 hover:bg-white/5 border-white/20 hover:text-white group">
                                        <span>Start Building</span>
                                        <span class="block duration-300 ease-out group-hover:translate-x-1">&rarr;</span>
                                    </a>
                                </p>
                                <p class="w-1/2 text-center flex items-center justify-center pl-2.5">
                                    <a href="https://static.devdojo.com" target="_blank" class="flex items-center justify-center w-full px-3 py-1 space-x-1 border rounded-lg bg-neutral-100 hover:bg-white text-neutral-800 border-white/20 hover:text-neutral-900 group">
                                        <span>View Docs</span>
                                        <span class="block duration-300 ease-out group-hover:translate-x-1">&rarr;</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
