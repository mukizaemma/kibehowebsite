<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomClosure extends Model
{
    protected $fillable = ['room_id', 'night', 'note', 'created_by'];

    protected $casts = [
        'night' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
