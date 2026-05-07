<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'type',           // 'file' | 'note' | 'link'
        'slug',
        'custom_slug',
        'title',
        'original_name',
        'path',
        'mime',
        'size',
        'content',        // texto de la nota o URL del link
        'delete_token',
        'expires_at',
        'ip',
        'user_agent',
        'file_password',
        'downloads_count',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'custom_slug'  => 'boolean',
    ];

    // ─── Helpers de tipo ───────────────────────────────────────────────

    public function isFile(): bool  { return $this->type === 'file'; }
    public function isNote(): bool  { return $this->type === 'note'; }
    public function isLink(): bool  { return $this->type === 'link'; }

    // ─── URL pública ────────────────────────────────────────────────────

    public function getPublicUrlAttribute(): string
    {
        return route('files.view', $this->slug);
    }

    // ─── Borrar del storage y BD ────────────────────────────────────────

    public function removeFileFromStorage(): void
    {
        if ($this->isFile() && $this->path) {
            Storage::disk('uploads')->exists($this->path)
                && Storage::disk('uploads')->delete($this->path);
        }
        $this->delete();
    }

    // ─── Helpers estáticos (icono y tamaño) ─────────────────────────────

    public static function formatFileSize($bytes): string
    {
        if ($bytes == 0) return '0 Bytes';

        $k     = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i     = (int) floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public static function getFileIcon($mime, $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $iconMap = [
            'image'  => 'bi-file-image',
            'jpeg'   => 'bi-file-image', 'jpg' => 'bi-file-image',
            'png'    => 'bi-file-image', 'gif' => 'bi-file-image',
            'bmp'    => 'bi-file-image', 'svg' => 'bi-file-image', 'webp' => 'bi-file-image',
            'pdf'    => 'bi-file-pdf',
            'doc'    => 'bi-file-word',  'docx' => 'bi-file-word', 'odt' => 'bi-file-word',
            'xls'    => 'bi-file-excel', 'xlsx' => 'bi-file-excel',
            'csv'    => 'bi-file-excel', 'ods'  => 'bi-file-excel',
            'ppt'    => 'bi-file-ppt',   'pptx' => 'bi-file-ppt',  'odp' => 'bi-file-ppt',
            'txt'    => 'bi-file-text',  'text' => 'bi-file-text',  'log' => 'bi-file-text',
            'php'    => 'bi-file-code',  'js'   => 'bi-file-code',  'html' => 'bi-file-code',
            'css'    => 'bi-file-code',  'json' => 'bi-file-code',  'xml'  => 'bi-file-code',
            'py'     => 'bi-file-code',  'java' => 'bi-file-code',  'sql'  => 'bi-file-code',
            'zip'    => 'bi-file-zip',   'rar'  => 'bi-file-zip',   'tar'  => 'bi-file-zip',
            'gz'     => 'bi-file-zip',   '7z'   => 'bi-file-zip',
            'audio'  => 'bi-file-music', 'mp3'  => 'bi-file-music', 'wav' => 'bi-file-music',
            'ogg'    => 'bi-file-music', 'flac' => 'bi-file-music',
            'video'  => 'bi-file-play',  'mp4'  => 'bi-file-play',  'avi' => 'bi-file-play',
            'mov'    => 'bi-file-play',  'wmv'  => 'bi-file-play',  'mkv' => 'bi-file-play',
        ];

        foreach ($iconMap as $key => $icon) {
            if (Str::contains($mime ?? '', $key)) return $icon;
        }

        return $iconMap[$extension] ?? 'bi-file-earmark';
    }
}