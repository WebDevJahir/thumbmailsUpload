<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulkRequest;
use App\Models\Image;
use App\Jobs\ProcessImage;
use Illuminate\Http\Request;

class BulkRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $urls = array_filter(explode("\n", trim($request->input('urls'))));
        $quota = match ($user->tier) {
            'free' => 50,
            'pro' => 100,
            'enterprise' => 200,
        };

        if (count($urls) > $quota) {
            return response()->json(['error' => "Exceeded quota: Max $quota URLs allowed."], 422);
        }

        $bulkRequest = BulkRequest::create([
            'user_id' => $user->id,
            'image_count' => count($urls),
        ]);

        foreach ($urls as $url) {
            $image = Image::create([
                'bulk_request_id' => $bulkRequest->id,
                'url' => trim($url),
            ]);

            $priority = match ($user->tier) {
                'free' => 1,
                'pro' => 2,
                'enterprise' => 3,
            };
            ProcessImage::dispatch($image)->onQueue("priority-$priority");
        }

        return response()->json(['success' => 'Request submitted for processing.', 'bulkRequest' => $bulkRequest]);
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
