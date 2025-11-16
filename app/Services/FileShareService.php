<?php
namespace App\Services;

use App\Models\File as FileShare;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class FileShareService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('uploads'); // 'uploads' debe estar en config/filesystems.php
    }

    // Guarda archivo y retorna modelo
    public function storeUploadedFile($uploadedFile, int $expireDays = 3, $requestMeta = [], $filePassword = null)
    {
        
        // Generar nombre de archivo seguro
        $filename = Str::random(40) . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('', $filename, 'uploads'); // guarda en root del disk uploads

        $slug = $this->generateHumanSlug();

        // Asegurarse que slug sea único
        while (FileShare::where('slug', $slug)->exists()) {
            $slug = $this->generateHumanSlug();
        }

        $deleteToken = hash('sha256', Str::random(40) . now()->timestamp);

        $expiresAt = Carbon::now()->addDays($expireDays);

        $file = FileShare::create([
            'slug' => $slug,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime' => $uploadedFile->getClientMimeType(),
            'size' => $uploadedFile->getSize(),
            'delete_token' => $deleteToken,
            'expires_at' => $expiresAt,
            'ip' => $requestMeta['ip'] ?? null,
            'user_agent' => $requestMeta['ua'] ?? null,
            'file_password' => filled($filePassword) ? Hash::make($filePassword) : null,
        ]);

        return $file;
    }

    // Generador de slug "humano"
    protected function generateHumanSlug()
    {
        // Listas pequeñas, puedes expandir
        $adjectives = ['rojo','azul','verde','brillante','rapido','silencioso','feliz','lento','firme','suave','alto','bajo'];
        $nouns = ['gato','lobo','puma','libro','nube','sol','lago','rueda','puente','lirio','cafe','sombra'];

        $adj = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        $num = random_int(1000, 9999);

        return "{$adj}-{$noun}-{$num}";
    }
}
