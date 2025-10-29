<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaUploadController extends Controller
{
    /**
     * Terima file image dari admin panel, simpan ke storage publik,
     * dan balikin URL yang bisa langsung dipakai di frontend.
     *
     * Dipakai oleh semua modul katalog (biz/merch/pop/mmt/...),
     * bedanya cuma folder.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file'   => ['required','file','image','max:5120'], // 5 MB
            'folder' => ['nullable','string','max:50'],
        ]);

        // default fallback supaya gak kosongan
        $folder = $request->input('folder', 'general');

        // Simpan file ke disk 'public'
        // Hasilnya misal: uploads/biz/asdf1234.jpg
        $path = $request->file('file')->store("uploads/{$folder}", 'public');

        // URL publiknya (misal /storage/uploads/biz/asdf1234.jpg)
        $publicUrl = Storage::disk('public')->url($path);

        return response()->json([
            'ok'   => true,
            'url'  => $publicUrl,
            'path' => $path,
        ]);
    }
}
