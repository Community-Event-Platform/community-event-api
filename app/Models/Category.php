<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // PHẢI CÓ HÀM NÀY ĐỂ BACKEND ĐẾM ĐƯỢC SỰ KIỆN
    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }
}