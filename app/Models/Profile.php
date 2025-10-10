<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['user_id','full_name','phone','company','tax_id','address_json'];
    protected $casts = ['address_json' => 'array'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
