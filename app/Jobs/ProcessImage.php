<?php

namespace App\Jobs;

use App\Models\Image;
use App\Events\ImageProcessed;
use App\Notifications\ThumbnailsReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function handle()
    {
        sleep(rand(1, 5));

        $success = rand(1, 10) <= 8;

        $this->image->update([
            'status' => $success ? 'processed' : 'failed',
            'processed_at' => now(),
        ]);
        event(new ImageProcessed($this->image));

        // Check if all images in the bulk request are processed
        $bulkRequest = $this->image->bulkRequest;
        if ($bulkRequest->images()->where('status', 'pending')->doesntExist()) {
            $bulkRequest->user->notify(new ThumbnailsReady($bulkRequest));
        }
    }
}
