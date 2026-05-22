<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'location',
        'date_time',
        'capacity',
        'event_type',
        'status',
        'require_additional_info',
        'custom_form_spec',
        'image',
        'organizer_id',
    ];

   
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Mối quan hệ: Một Event thì thuộc về một Category duy nhất.
     * Liên kết thông qua khóa ngoại 'category_id' trên bảng 'events'.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'event_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'event_id');
    }
}
