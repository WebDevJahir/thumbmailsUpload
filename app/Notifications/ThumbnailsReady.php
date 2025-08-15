<?php

namespace App\Notifications;

use App\Models\BulkRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ThumbnailsReady extends Notification
{
    use Queueable;

    public $bulkRequest;

    public function __construct(BulkRequest $bulkRequest)
    {
        $this->bulkRequest = $bulkRequest;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Use database and email channels
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Thumbnails Are Ready!')
            ->line('All images in your bulk request (ID: ' . $this->bulkRequest->id . ') have been processed.')
            ->line('Processed: ' . $this->bulkRequest->images()->where('status', 'processed')->count())
            ->line('Failed: ' . $this->bulkRequest->images()->where('status', 'failed')->count())
            ->action('View Results', url('/dashboard'))
            ->line('Thank you for using our service!');
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'bulk_request_id' => $this->bulkRequest->id,
            'message' => 'All images in your bulk request (ID: ' . $this->bulkRequest->id . ') have been processed.',
            'processed_count' => $this->bulkRequest->images()->where('status', 'processed')->count(),
            'failed_count' => $this->bulkRequest->images()->where('status', 'failed')->count(),
            'url' => url('/dashboard'),
        ]);
    }
}
