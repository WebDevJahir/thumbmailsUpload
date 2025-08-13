<?php

namespace App\Events;

use App\Models\Image;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ImageProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->image->bulkRequest->user_id);
    }

    public function broadcastWith()
    {
        return [
            'image' => [
                'id' => $this->image->id,
                'url' => $this->image->url,
                'status' => $this->image->status,
                'processed_at' => $this->image->processed_at ? \Carbon\Carbon::parse($this->image->processed_at)->toDateTimeString() : null,
            ]
        ];
    }
}
