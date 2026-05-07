<?php

namespace App\Services;

use App\Models\File as FileShare;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class FileShareService
{
    // El disco se resuelve desde config: 'uploads' apunta a S3 o local según .env
    protected string $disk = 'uploads';

    // ────────────────────────────────────────────────────────────────────
    // SUBIR ARCHIVO
    // ────────────────────────────────────────────────────────────────────

    public function storeUploadedFile(
        $uploadedFile,
        int    $expireDays    = 3,
        array  $requestMeta   = [],
        ?string $filePassword = null,
        ?string $customSlug   = null
    ): FileShare {
        $filename = Str::random(40) . '.' . $uploadedFile->getClientOriginalExtension();

        // S3 o local — transparente gracias al filesystem driver
        $path = $uploadedFile->storeAs('', $filename, $this->disk);

        $slug = $this->resolveSlug($customSlug);

        return FileShare::create([
            'type'          => 'file',
            'slug'          => $slug,
            'custom_slug'   => filled($customSlug),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path'          => $path,
            'mime'          => $uploadedFile->getClientMimeType(),
            'size'          => $uploadedFile->getSize(),
            'delete_token'  => $this->generateDeleteToken(),
            'expires_at'    => Carbon::now()->addDays($expireDays),
            'ip'            => $requestMeta['ip']  ?? null,
            'user_agent'    => $requestMeta['ua']  ?? null,
            'file_password' => filled($filePassword) ? Hash::make($filePassword) : null,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // GUARDAR NOTA
    // ────────────────────────────────────────────────────────────────────

    public function storeNote(
        string  $content,
        ?string $title        = null,
        int     $expireDays   = 3,
        array   $requestMeta  = [],
        ?string $filePassword = null,
        ?string $customSlug   = null
    ): FileShare {
        return FileShare::create([
            'type'          => 'note',
            'slug'          => $this->resolveSlug($customSlug),
            'custom_slug'   => filled($customSlug),
            'title'         => $title ?: 'Nota sin título',
            'original_name' => ($title ?: 'nota') . '.txt',
            'content'       => $content,
            'mime'          => 'text/plain',
            'size'          => strlen($content),
            'delete_token'  => $this->generateDeleteToken(),
            'expires_at'    => Carbon::now()->addDays($expireDays),
            'ip'            => $requestMeta['ip'] ?? null,
            'user_agent'    => $requestMeta['ua'] ?? null,
            'file_password' => filled($filePassword) ? Hash::make($filePassword) : null,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // GUARDAR LINK
    // ────────────────────────────────────────────────────────────────────

    public function storeLink(
        string  $url,
        ?string $title       = null,
        int     $expireDays  = 3,
        array   $requestMeta = [],
        ?string $customSlug  = null
    ): FileShare {
        return FileShare::create([
            'type'          => 'link',
            'slug'          => $this->resolveSlug($customSlug),
            'custom_slug'   => filled($customSlug),
            'title'         => $title ?: $url,
            'original_name' => $title ?: $url,
            'content'       => $url,
            'mime'          => 'text/uri-list',
            'size'          => 0,
            'delete_token'  => $this->generateDeleteToken(),
            'expires_at'    => Carbon::now()->addDays($expireDays),
            'ip'            => $requestMeta['ip'] ?? null,
            'user_agent'    => $requestMeta['ua'] ?? null,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ────────────────────────────────────────────────────────────────────

    /**
     * Si el usuario pasó un slug personalizado y está disponible, úsalo.
     * Si está ocupado, lanza excepción (el controller la maneja).
     * Si no pasó nada, genera uno random "human-readable".
     */
    protected function resolveSlug(?string $custom): string
    {
        if (filled($custom)) {
            $clean = Str::slug($custom); // sanitiza

            if (FileShare::where('slug', $clean)->exists()) {
                throw new \Exception("El slug '{$clean}' ya está en uso. Elige otro.");
            }

            return $clean;
        }

        return $this->generateUniqueRandomSlug();
    }

    protected function generateUniqueRandomSlug(): string
    {
        do {
            $slug = $this->generateHumanSlug();
        } while (FileShare::where('slug', $slug)->exists());

        return $slug;
    }

    protected function generateHumanSlug(): string
    {
        $adjectives = ['rojo','azul','verde','brillante','rapido','silencioso','feliz','lento','firme','suave','alto','bajo'];
        $nouns      = ['gato','lobo','puma','libro','nube','sol','lago','rueda','puente','lirio','cafe','sombra'];

        return $adjectives[array_rand($adjectives)]
            . '-' . $nouns[array_rand($nouns)]
            . '-' . random_int(1000, 9999);
    }

    protected function generateDeleteToken(): string
    {
        return hash('sha256', Str::random(40) . now()->timestamp);
    }
}