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
        'slug',
        'original_name',
        'path',
        'mime',
        'size',
        'delete_token',
        'expires_at',
        'ip',
        'user_agent',
        'file_password',
    ];

    protected $dates = ['expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // URL pública al archivo
    public function getPublicUrlAttribute()
    {
        return route('files.show', $this->slug);
    }

    // Borra archivo del storage y registro
    public function removeFileFromStorage()
    {
        if ($this->path && Storage::disk('uploads')->exists($this->path)) {
            Storage::disk('uploads')->delete($this->path);
        }
        $this->delete();
    }


    public static function formatFileSize($bytes): string
    {
        if ($bytes == 0) {
            return '0 Bytes';
        }
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }


    public static function getFileIcon($mime, $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Mapeo de extensiones/MIME types a iconos de Bootstrap Icons
        $iconMap = [
            // Imágenes
            'image' => 'bi-file-image',
            'jpeg' => 'bi-file-image',
            'jpg' => 'bi-file-image',
            'png' => 'bi-file-image',
            'gif' => 'bi-file-image',
            'bmp' => 'bi-file-image',
            'svg' => 'bi-file-image',
            'webp' => 'bi-file-image',
            
            // Documentos
            'pdf' => 'bi-file-pdf',
            'doc' => 'bi-file-word',
            'docx' => 'bi-file-word',
            'odt' => 'bi-file-word',
            
            // Hojas de cálculo
            'xls' => 'bi-file-excel',
            'xlsx' => 'bi-file-excel',
            'csv' => 'bi-file-excel',
            'ods' => 'bi-file-excel',
            
            // Presentaciones
            'ppt' => 'bi-file-ppt',
            'pptx' => 'bi-file-ppt',
            'odp' => 'bi-file-ppt',
            
            // Texto
            'txt' => 'bi-file-text',
            'text' => 'bi-file-text',
            'log' => 'bi-file-text',
            
            // Código
            'php' => 'bi-file-code',
            'js' => 'bi-file-code',
            'html' => 'bi-file-code',
            'css' => 'bi-file-code',
            'json' => 'bi-file-code',
            'xml' => 'bi-file-code',
            'py' => 'bi-file-code',
            'java' => 'bi-file-code',
            'cpp' => 'bi-file-code',
            'c' => 'bi-file-code',
            'sql' => 'bi-file-code',
            
            // Archivos comprimidos
            'zip' => 'bi-file-zip',
            'rar' => 'bi-file-zip',
            'tar' => 'bi-file-zip',
            'gz' => 'bi-file-zip',
            '7z' => 'bi-file-zip',
            
            // Audio
            'audio' => 'bi-file-music',
            'mp3' => 'bi-file-music',
            'wav' => 'bi-file-music',
            'ogg' => 'bi-file-music',
            'flac' => 'bi-file-music',
            
            // Video
            'video' => 'bi-file-play',
            'mp4' => 'bi-file-play',
            'avi' => 'bi-file-play',
            'mov' => 'bi-file-play',
            'wmv' => 'bi-file-play',
            'mkv' => 'bi-file-play',
        ];
        
        // Primero buscar por MIME type
        foreach ($iconMap as $key => $icon) {
            if (Str::contains($mime, $key)) {
                return $icon;
            }
        }
        
        // Si no encuentra por MIME, buscar por extensión
        if (isset($iconMap[$extension])) {
            return $iconMap[$extension];
        }
        
        // Icono por defecto
        return 'bi-file-earmark';
    }
}
