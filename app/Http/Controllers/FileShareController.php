<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FileUploadRequest;
use App\Services\FileShareService;
use App\Models\File as FileShare;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Hash;

class FileShareController extends Controller
{
    protected $service;

    public function __construct(FileShareService $service)
    {
        $this->service = $service;
    }

    function index()
    {
        $file = FileShare::all();
        return view('welcome', compact('file'));
    }

    // POST /upload
    public function store(FileUploadRequest $request)
    {

        $expireDays = $request->input('expire_days', $request->expire_days ?? 3);
        $file = $this->service->storeUploadedFile(
            $request->file('file'),
            (int) $expireDays,
            ['ip' => $request->ip(), 'ua' => $request->userAgent()],
            $request->file_password ?? null
        );

        return response()->json([
            'slug' => $file->slug,
            'url' => route('files.view', $file->slug),
            'delete_token' => $file->delete_token, // entregarlo al cliente para poder borrar si lo desea
            'expires_at' => $file->expires_at->toDateTimeString(),
            'original_name' => $file->original_name,
            'size' => $file->size,
            'file_password' => $file->file_password ? true : false,
        ], 201);
    }

    public function view($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        if ($file->expires_at && $file->expires_at->isPast()) {
            $file->removeFileFromStorage();
            abort(404, 'El archivo expiró.');
        }

        if ($file->file_password && !session("file_{$file->id}_authorized")) {
            return view('files.password', compact('file'));
        }

        return view('files.show', compact('file'));
    }


    public function download($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        $disk = Storage::disk('uploads');

        if (!$disk->exists($file->path)) {
            $file->delete();
            abort(404, 'Archivo no encontrado.');
        }

        $file->increment('downloads_count');

        return $disk->download($file->path, $file->original_name);
    }


    // DELETE /f/{slug}?token=xxxxx
    public function destroy(Request $request, $slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        if (!$request->token || !hash_equals($file->delete_token, $request->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ]);
        }

        $file->removeFileFromStorage();

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo eliminado correctamente',
            'redirect' => route('files.index')
        ]);
    }


    public function streamInline($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        $disk = Storage::disk('uploads');

        if (!$disk->exists($file->path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $stream = $disk->readStream($file->path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $file->mime,
            'Content-Length' => $file->size,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }


    public function validatePassword(Request $request, $slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        $request->validate([
            'password' => 'required|string'
        ]);

        $attempts = session('attempts', 0);
        if ($attempts >= 5) {
            return back()->with('error', 'Demasiados intentos fallidos. Intenta más tarde.');
        }

        if (Hash::check($request->password, $file->file_password)) {
            session(["file_{$file->id}_authorized" => true]);
            session()->forget('attempts'); // Limpiar intentos
            return redirect()->route('files.view', $file->slug);
        }

        session(['attempts' => $attempts + 1]);
        $remaining = 5 - ($attempts + 1);

        return back()->with('error', "Contraseña incorrecta. Te quedan {$remaining} intentos.")
            ->withInput();
    }
}
