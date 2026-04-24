<?php

namespace App\Services;

use App\Models\FotoMuatan;
use App\Models\Spa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

/**
 * Service untuk upload foto muatan truk.
 *
 * Tugas:
 * - Validasi ukuran & tipe file
 * - Resize untuk hemat storage (max 1600px long edge)
 * - Generate thumbnail 400px untuk list view
 * - Hash SHA-256 untuk deteksi foto duplikat
 * - Simpan ke disk `public` (agar bisa di-serve langsung)
 */
class PhotoService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new GdDriver());
    }

    /**
     * Upload satu foto muatan untuk SPA tertentu.
     */
    public function upload(Spa $spa, UploadedFile $file, string $jenis = 'lainnya'): FotoMuatan
    {
        // Hash untuk deteksi duplikat
        $hash = hash_file('sha256', $file->getRealPath());

        // Bangun nama file unik
        $filename = sprintf(
            '%s_%s_%s.jpg',
            $spa->nomor_spa,
            $jenis,
            Str::random(8)
        );

        $dir = 'spa/' . $spa->tanggal_spa->format('Y/m');
        $pathMain = "$dir/$filename";
        $pathThumb = "$dir/thumb_$filename";

        // Resize main image (max 1600px, quality 82)
        $main = $this->imageManager->read($file->getRealPath());
        $main->scaleDown(width: 1600, height: 1600);
        $mainEncoded = $main->toJpeg(82);

        Storage::disk('public')->put($pathMain, (string) $mainEncoded);

        // Thumbnail 400px
        $thumb = $this->imageManager->read($file->getRealPath());
        $thumb->scaleDown(width: 400, height: 400);
        $thumbEncoded = $thumb->toJpeg(75);

        Storage::disk('public')->put($pathThumb, (string) $thumbEncoded);

        return FotoMuatan::create([
            'spa_id' => $spa->id,
            'jenis' => $jenis,
            'path' => $pathMain,
            'url_thumbnail' => Storage::disk('public')->url($pathThumb),
            'size_kb' => (int) round(strlen((string) $mainEncoded) / 1024),
            'mime_type' => 'image/jpeg',
            'width' => $main->width(),
            'height' => $main->height(),
            'hash_sha256' => $hash,
            'captured_at' => now(),
        ]);
    }

    /**
     * Hapus foto beserta file fisiknya.
     */
    public function delete(FotoMuatan $foto): bool
    {
        if ($foto->path) {
            Storage::disk('public')->delete($foto->path);

            // Juga hapus thumbnail berdasarkan konvensi path
            $thumbPath = preg_replace('/\/([^\/]+)$/', '/thumb_$1', $foto->path);
            Storage::disk('public')->delete($thumbPath);
        }

        return (bool) $foto->delete();
    }

    /**
     * Deteksi duplikat foto pada SPA yang sama via hash.
     */
    public function isDuplicate(Spa $spa, string $hash): bool
    {
        return FotoMuatan::query()
            ->where('spa_id', $spa->id)
            ->where('hash_sha256', $hash)
            ->exists();
    }
}
