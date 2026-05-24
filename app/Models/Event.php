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
        'category_id',
        'location',
        'date_time',
        'capacity',
        'attendees',
        'rating',
        'price',
        'event_type',
        'status',
        'require_additional_info',
        'custom_form_spec',
        'image_url',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'require_additional_info' => 'boolean',
        'custom_form_spec' => 'array',
    ];
    public function category()
        {
            return $this->belongsTo(Category::class, 'category_id');
        }
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

