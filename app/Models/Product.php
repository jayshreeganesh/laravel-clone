<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model {
    protected string $table = 'products';

    protected array $fillable = [
        'name',
        'sku',
        'price',
        'stock',
        'description',
    ];
}
