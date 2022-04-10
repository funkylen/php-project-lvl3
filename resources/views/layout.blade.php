<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <link rel="stylesheet" href="{{ asset('css/app.css', app()->environment('production')) }}">
</head>
<body>

<header>
    @include('navbar')
</header>

<main class="flex-grow-1">
    @include('flash::message')

    <div class="container mt-3">

        @yield('content')

    </div>
</main>


<style src="{{ asset('js/app.js', app()->environment('production')) }}"></style>
</body>
</html>
