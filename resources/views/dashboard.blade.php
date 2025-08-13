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
                    <!-- Filter by Status (Simple Form) -->
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
