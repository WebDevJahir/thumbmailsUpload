<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImage;
use App\Models\BulkRequest;
use App\Models\Image;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $urls = array_filter(explode("\n", trim($request->input('urls'))));
        $quota = match ($user->tier) {
            'free' => 50,
            'pro' => 100,
            'enterprise' => 200,
        };

        if (count($urls) > $quota) {
            return back()->withErrors(['urls' => "Exceeded quota: Max $quota URLs allowed."]);
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

        return redirect()->route('dashboard')->with('success', 'Request submitted for processing.');
    }
}
