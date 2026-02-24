<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name', 
        'email', 
        'phone', 
        'address'
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}