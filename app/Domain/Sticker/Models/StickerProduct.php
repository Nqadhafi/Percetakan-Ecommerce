<?php
// app/Domain/Sticker/Models/StickerProduct.php
namespace App\Domain\Sticker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StickerProduct extends Model
{
    protected $table = 'sticker_products';
    protected $fillable = ['name','slug','is_active','thumbnail_url','images_json'];
    protected $casts = ['images_json' => 'array'];

    public function specs(): HasMany { return $this->hasMany(StickerSpec::class, 'product_id'); }
}
