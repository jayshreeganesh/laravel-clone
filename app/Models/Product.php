<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model {
    protected string $table = 'products';

    protected array $fillable = [
        'user_id',
        'name',
        'sku',
        'price',
        'stock',
        'description',
        'is_active',
    ];
    
    public function delete(): bool {
        $pdo = \App\Core\Database::connect();
        $stmt = $pdo->prepare("UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$this->attributes['id']]);
    }

    public function forceDelete(): bool {
        return parent::delete();
    }

    public function restore(): bool {
        $pdo = \App\Core\Database::connect();
        $stmt = $pdo->prepare("UPDATE {$this->table} SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$this->attributes['id']]);
    }
}