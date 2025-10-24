<?php
// database/seeders/StickerFinishSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Sticker\Models\StickerFinishOption;

class StickerFinishSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Laminasi (global)
            ['kind'=>'lamination','code'=>'doff','label'=>'Laminasi Doff','pricing_mode'=>'per_sheet','amount'=>500],
            ['kind'=>'lamination','code'=>'glossy','label'=>'Laminasi Glossy','pricing_mode'=>'per_sheet','amount'=>500],
            // Finishing (global)
            ['kind'=>'finish','code'=>'straight_cut','label'=>'Potong Lurus','pricing_mode'=>'flat_job','amount'=>2000],
            ['kind'=>'finish','code'=>'kiss_cut','label'=>'Kiss-cut','pricing_mode'=>'per_sheet','amount'=>300],
            ['kind'=>'finish','code'=>'die_cut','label'=>'Die-cut','pricing_mode'=>'per_sheet','amount'=>600],
        ];
        foreach ($rows as $r) { StickerFinishOption::create($r + ['is_active'=>true]); }
    }
}
