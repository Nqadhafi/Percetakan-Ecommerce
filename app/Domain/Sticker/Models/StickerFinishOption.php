<?php
// app/Domain/Sticker/Models/StickerFinishOption.php
namespace App\Domain\Sticker\Models;

use Illuminate\Database\Eloquent\Model;

class StickerFinishOption extends Model
{
    protected $table = 'sticker_finish_options';
    protected $fillable = [
        'product_id','material','kind','code','label','pricing_mode','amount','is_active'
    ];
    protected $casts = ['is_active'=>'boolean'];
}
