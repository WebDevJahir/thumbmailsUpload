<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
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
        // Simulate processing delay (1-5 seconds)
        sleep(rand(1, 5));

        // Simulate success/failure (80% success)
        $success = rand(1, 10) <= 8;

        $this->image->update([
            'status' => $success ? 'processed' : 'failed',
            'processed_at' => now(),
        ]);

        // Simulate NodeJS service call 
        // In real: HTTP call to NodeJS endpoint
    }
}
