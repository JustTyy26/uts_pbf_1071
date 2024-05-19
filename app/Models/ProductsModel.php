<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'categories_id',
        'expired_at',
        'modified_by'
    ];

    //Relationship with User model for modified_by
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    //Relationship with User model for modified_by
       public function category()
    {
    return $this->belongsTo(CategoriesModel::class);
    }
}
