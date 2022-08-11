<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product.
 *
 *
 */
class Product extends Model
{
    protected $fillable = ['name', 'description', 'amount', 'user_id'];

    public $table = "products";

    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
