<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParafService
{
    /**
     * Simpan file paraf atau decoded base64 canvas ke disk storage publik.
     * Mengembalikan path relatif yang tersimpan di disk 'public' atau data URL jika serverless readonly.
     */
    public static function processParaf(
        ?string $base64Data = null,
        ?UploadedFile $uploadedFile = null,
        bool $shouldRemove = false,
        ?string $currentPath = null,
        string $prefix = 'paraf'
    ): ?string {
        // 1. Jika pengguna meminta menghapus paraf yang ada
        if ($shouldRemove) {
            self::deleteParaf($currentPath);

            return null;
        }

        // 2. Jika ada data base64 (dari pemrosesan canvas browser transparan & ringan)
        if (! empty($base64Data) && str_starts_with($base64Data, 'data:image/')) {
            $commaPos = strpos($base64Data, ',');
            if ($commaPos !== false) {
                $rawBase64 = substr($base64Data, $commaPos + 1);
                $decoded = base64_decode($rawBase64, true);

                if ($decoded !== false && strlen($decoded) > 0) {
                    $ext = 'png';
                    if (str_contains($base64Data, 'image/webp')) {
                        $ext = 'webp';
                    }

                    $filename = "{$prefix}s/{$prefix}_".Str::random(16).'_'.time().".{$ext}";
                    try {
                        Storage::disk('public')->put($filename, $decoded);
                        self::deleteParaf($currentPath);

                        return $filename;
                    } catch (\Throwable $e) {
                        // Fallback jika filesystem readonly di serverless
                        return $base64Data;
                    }
                }
            }
        }

        // 3. Jika ada file upload fisik melalui form multipart
        if ($uploadedFile && $uploadedFile->isValid()) {
            self::deleteParaf($currentPath);
            try {
                return $uploadedFile->store("{$prefix}s", 'public');
            } catch (\Throwable $e) {
                $content = file_get_contents($uploadedFile->getRealPath());
                $mime = $uploadedFile->getMimeType() ?: 'image/png';

                return 'data:'.$mime.';base64,'.base64_encode($content);
            }
        }

        // 4. Pertahankan data yang sudah ada jika tidak diubah
        return $currentPath;
    }

    /**
     * Hapus file paraf dari disk public jika ada.
     */
    public static function deleteParaf(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'data:') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
