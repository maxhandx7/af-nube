<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\File as FileShare;
use Carbon\Carbon;

class DeleteExpiredFiles extends Command
{
    protected $signature = 'files:cleanup';
    protected $description = 'Eliminar archivos expirados y limpiar storage';

    public function handle()
    {
        $now = Carbon::now();
        $expired = FileShare::whereNotNull('expires_at')->where('expires_at', '<=', $now)->get();

        $this->info('Encontrados: ' . $expired->count());

        foreach ($expired as $file) {
            try {
                $file->removeFileFromStorage();
                $this->info("Eliminado: {$file->slug}");
            } catch (\Throwable $e) {
                $this->error("Error eliminando {$file->slug}: " . $e->getMessage());
            }
        }

        $this->info('Limpieza terminada.');
        return 0;
    }
}
