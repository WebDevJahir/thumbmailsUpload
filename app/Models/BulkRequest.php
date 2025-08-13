<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkRequest extends Model
{
    protected $fillable = ['user_id', 'image_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
