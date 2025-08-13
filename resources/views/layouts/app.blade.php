<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Echo) {
            window.Echo.private('user.{{ auth()->id() }}')
                .listen('ImageProcessed', (e) => {
                    console.log('Echo initialized:', window.Echo);
                    const image = e.image;
                    const row = document.querySelector(`tr[data-image-id="${image.id}"]`);
                    if (row) {
                        row.querySelector('.status').textContent = image.status.charAt(0).toUpperCase() +
                            image.status
                            .slice(1);
                        row.querySelector('.timestamp').textContent = image.processed_at || row
                            .querySelector('.timestamp')
                            .textContent;
                    } else {
                        const tbody = document.querySelector('#image-table tbody');
                        const newRow = `
                                <tr data-image-id="${image.id}">
                                    <td>${image.url}</td>
                                    <td class="status">${image.status.charAt(0).toUpperCase() + image.status.slice(1)}</td>
                                    <td class="timestamp">${image.processed_at}</td>
                                </tr>
                            `;
                        const el = document.getElementById('image-table');
                        if (el) {
                            el.insertAdjacentHTML('beforeend', newRow);
                        } else {
                            console.error('Element not found for insertAdjacentHTML');
                        }
                    }
                });
        } else {
            console.error('Echo is not defined');
        }
    });

    setInterval(() => {
        fetch('/dashboard')
            .then(response => response.text())
            .then(html => {
                document.querySelector('#image-table tbody').innerHTML = new DOMParser()
                    .parseFromString(html, 'text/html')
                    .querySelector('#image-table tbody').innerHTML;
            });
    }, 10000);
</script>

</html>
