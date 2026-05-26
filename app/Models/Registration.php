<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'attendee_id',
        'status',
        'additional_info',
    ];

    protected $casts = [
        'additional_info' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee()
    {
        return $this->belongsTo(User::class, 'attendee_id');
    }
}
