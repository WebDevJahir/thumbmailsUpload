<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Notifications -->
                    @if ($notifications->count())
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Notifications</h3>
                            <ul>
                                @foreach ($notifications as $notification)
                                    <li class="border-b py-2">
                                        {{ $notification->data['message'] }}
                                        <a href="{{ $notification->data['url'] }}" class="text-blue-500">View</a>
                                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-gray-500">Mark as Read</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Submission Form -->
                    <form method="POST" action="{{ route('submit') }}">
                        @csrf
                        <textarea name="urls" rows="10" class="w-full" placeholder="Paste image URLs, one per line"></textarea>
                        <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2">Submit</button>
                    </form>

                    <!-- Table for Results -->
                    <table class="mt-8 w-full" id="image-table">
                        <thead>
                            <tr>
                                <th>Image URL</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                @foreach ($request->images as $image)
                                    <tr data-image-id="{{ $image->id }}">
                                        <td>{{ $image->url }}</td>
                                        <td class="status">{{ ucfirst($image->status) }}</td>
                                        <td class="timestamp">{{ $image->processed_at ?? $image->created_at }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Filter by Status -->
                    <form method="GET" action="{{ route('dashboard') }}" class="mt-4">
                        <select name="status">
                            <option value="">All</option>
                            <option value="processed">Processed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                        <button type="submit">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Echo) {
                console.log('Echo initialized:', window.Echo);
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('ImageProcessed', (e) => {
                        console.log('Event received:', e);
                        const image = e.image;
                        const row = document.querySelector(`tr[data-image-id="${image.id}"]`);
                        if (row) {
                            row.querySelector('.status').textContent = image.status.charAt(0).toUpperCase() +
                                image.status.slice(1);
                            row.querySelector('.timestamp').textContent = image.processed_at || row
                                .querySelector('.timestamp').textContent;
                        } else {
                            const tbody = document.querySelector('#image-table tbody');
                            if (tbody) {
                                const newRow = `
                                    <tr data-image-id="${image.id}">
                                        <td>${image.url}</td>
                                        <td class="status">${image.status.charAt(0).toUpperCase() + image.status.slice(1)}</td>
                                        <td class="timestamp">${image.processed_at}</td>
                                    </tr>
                                `;
                                tbody.insertAdjacentHTML('beforeend', newRow);
                            } else {
                                console.error('Table body not found for inserting new row');
                            }
                        }
                    });
            } else {
                console.error('Echo is not defined');
            }

            // Polling fallback
            setInterval(() => {
                const tbody = document.querySelector('#image-table tbody');
                if (tbody) {
                    fetch('{{ route('dashboard.table') }}')
                        .then(response => response.text())
                        .then(html => {
                            tbody.innerHTML = html;
                        })
                        .catch(error => console.error('Polling error:', error));
                } else {
                    console.error('Table body not found for polling');
                }
            }, 10000);
        });
    </script>
@endpush
