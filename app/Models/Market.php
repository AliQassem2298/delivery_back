<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'address',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'amounts');
    }
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
