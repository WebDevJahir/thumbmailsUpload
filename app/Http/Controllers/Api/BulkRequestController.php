<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulkRequest;
use App\Models\Image;
use App\Jobs\ProcessImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'urls' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $urls = array_filter(array_map('trim', explode("\n", $value)));
                    $urlCount = count($urls);

                    $user = Auth::user();
                    if ($user->tier === 'free' && $urlCount > 50) {
                        $fail('Free users can upload a maximum of 50 images at a time.');
                    }

                    if ($urlCount === 0) {
                        $fail('Please provide at least one URL.');
                    }

                    $uniqueUrls = array_unique($urls);
                    if (count($uniqueUrls) !== $urlCount) {
                        $fail('Duplicate URLs are not allowed.');
                    }

                    foreach ($urls as $url) {
                        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $url)) {
                            $fail("Invalid URL format: {$url}. URLs must start with http:// or https://");
                        }
                    }
                },
            ],
        ]);

        $urls = array_filter(array_map('trim', explode("\n", $request->urls)));
        $user = Auth::user();
        $bulkRequest = BulkRequest::create(
            ['user_id' => $user->id, 'image_count' => count($urls)]
        );

        foreach ($urls as $url) {
            $image = $bulkRequest->images()->create([
                'url' => $url,
                'status' => 'pending',
            ]);
            $priority = match ($user->tier) {
                'free' => 1,
                'pro' => 2,
                'enterprise' => 3,
            };
            ProcessImage::dispatch($image)->onQueue("priority-$priority");
        }

        return response()->json(['message' => 'URLs submitted successfully'], 201);
    }


    public function index(Request $request)
    {
        $status = $request->query('status');
        $queries = BulkRequest::query();
        if ($status) {
            $queries->whereHas('images', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }
        $requests = $queries->with('images')->get();
        return response()->json($requests);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->unreadNotifications()->get();
        return response()->json($notifications);
    }

    public function markNotificationAsRead($id, Request $request)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => 'Notification marked as read']);
    }
}
