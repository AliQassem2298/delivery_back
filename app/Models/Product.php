<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'expiry_date',
        'order_id'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function markets()
    {
        return $this->belongsToMany(Market::class, 'amounts');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

}
