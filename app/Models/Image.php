<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['bulk_request_id', 'url', 'status', 'processed_at'];

    public function bulkRequest()
    {
        return $this->belongsTo(BulkRequest::class);
    }
}
