<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organizer_id',
        'name',
        'description',
        'category',
        'location',
        'date_time',
        'capacity',
        'event_type',
        'status',
        'require_additional_info',
        'custom_form_spec',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'require_additional_info' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
