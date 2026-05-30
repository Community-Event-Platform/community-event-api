<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Registration Model
 *
 * Represents a user's registration for an event.
 * Status values: 'Pending', 'Approved', 'Rejected', 'Cancelled', 'Waitlisted'
 * waitlist_position: null = confirmed/active seat, 1+ = position in waitlist queue
 */
class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'attendee_id',
        'status',
        'waitlist_position',
        'payment_id',
        'additional_info',
        'waitlist_position',
    ];

    protected $casts = [
        'additional_info' => 'array',
        'waitlist_position' => 'integer',  // CEP-82: null = confirmed, 1+ = waitlist FIFO position
    ];

    public function getStatusAttribute($value)
    {
        return $value === 'Confirmed' ? 'Approved' : $value;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee()
    {
        return $this->belongsTo(User::class, 'attendee_id');
    }

    public function formResponses()
    {
        return $this->hasMany(FormResponse::class);
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    /**
     * CEP-82: Scope to get only confirmed (non-waitlisted) registrations for an event.
     */
    public function scopeConfirmed($query)
    {
        return $query->whereNull('waitlist_position')
                     ->whereNotIn('status', ['Cancelled', 'Rejected']);
    }

    /**
     * CEP-82: Scope to get only waitlisted registrations, ordered by position (FIFO).
     */
    public function scopeWaitlisted($query)
    {
        return $query->whereNotNull('waitlist_position')
                     ->orderBy('waitlist_position', 'asc');
    }
}
