<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'field_name',
        'field_type',
        'response_value',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
