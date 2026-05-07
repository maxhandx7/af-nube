<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FileUploadRequest;
use App\Services\FileShareService;
use App\Models\File as FileShare;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class FileShareController extends Controller
{
    protected FileShareService $service;

    public function __construct(FileShareService $service)
    {
        $this->service = $service;
    }

    // ────────────────────────────────────────────────────────────────────
    // INDEX
    // ────────────────────────────────────────────────────────────────────

    public function index()
    {
        $file = FileShare::all();
        return view('welcome', compact('file'));
    }

    // ────────────────────────────────────────────────────────────────────
    // BUSCAR POR SLUG (GET /search?slug=xxx)
    // ────────────────────────────────────────────────────────────────────

    public function search(Request $request)
    {
        $request->validate(['slug' => 'required|string|max:100']);

        $slug = $request->slug;
        $file = FileShare::where('slug', $slug)->first();

        if (!$file) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'No se encontró ningún archivo con ese slug.'], 404);
            }
            return back()->with('search_error', "No se encontró ningún archivo con el slug «{$slug}».");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'slug' => $file->slug,
                'url'  => route('files.view', $file->slug),
                'type' => $file->type,
            ]);
        }

        return redirect()->route('files.view', $file->slug);
    }

    // ────────────────────────────────────────────────────────────────────
    // SUBIR ARCHIVO  POST /upload
    // ────────────────────────────────────────────────────────────────────

    public function store(FileUploadRequest $request)
    {
        
        try {

            $file = $this->service->storeUploadedFile(
                $request->file('file'),
                (int) $request->input('expire_days', 3),
                ['ip' => $request->ip(), 'ua' => $request->userAgent()],
                $request->file_password ?? null,
                $request->custom_slug ?? null         // ← slug personalizado
            );
        } catch (\Exception $e) {
            return response()->json(['error' => "llego aqui"], 422);
        }

        return response()->json([
            'slug'          => $file->slug,
            'url'           => route('files.view', $file->slug),
            'delete_token'  => $file->delete_token,
            'expires_at'    => $file->expires_at->toDateTimeString(),
            'original_name' => $file->original_name,
            'size'          => $file->size,
            'has_password'  => (bool) $file->file_password,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────────
    // GUARDAR NOTA  POST /notes
    // ────────────────────────────────────────────────────────────────────

    public function storeNote(Request $request)
    {
        $request->validate([
            'content'      => 'required|string|max:500000',
            'title'        => 'nullable|string|max:255',
            'expire_days'  => 'nullable|integer|min:1|max:30',
            'file_password'=> 'nullable|string|min:4',
            'custom_slug'  => 'nullable|string|max:80|alpha_dash',
        ]);

        try {
            $file = $this->service->storeNote(
                $request->content,
                $request->title,
                (int) $request->input('expire_days', 3),
                ['ip' => $request->ip(), 'ua' => $request->userAgent()],
                $request->file_password ?? null,
                $request->custom_slug   ?? null
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'slug'         => $file->slug,
            'url'          => route('files.view', $file->slug),
            'delete_token' => $file->delete_token,
            'expires_at'   => $file->expires_at->toDateTimeString(),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────────
    // GUARDAR LINK  POST /links
    // ────────────────────────────────────────────────────────────────────

    public function storeLink(Request $request)
    {
        $request->validate([
            'url'          => 'required|url|max:2000',
            'title'        => 'nullable|string|max:255',
            'expire_days'  => 'nullable|integer|min:1|max:30',
            'custom_slug'  => 'nullable|string|max:80|alpha_dash',
        ]);

        try {
            $file = $this->service->storeLink(
                $request->url,
                $request->title,
                (int) $request->input('expire_days', 3),
                ['ip' => $request->ip(), 'ua' => $request->userAgent()],
                $request->custom_slug ?? null
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'slug'         => $file->slug,
            'url'          => route('files.view', $file->slug),
            'delete_token' => $file->delete_token,
            'expires_at'   => $file->expires_at->toDateTimeString(),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────────
    // VER  GET /f/{slug}
    // ────────────────────────────────────────────────────────────────────

    public function view($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        if ($file->expires_at && $file->expires_at->isPast()) {
            $file->removeFileFromStorage();
            abort(404, 'El archivo expiró.');
        }

        // Links redirigen directamente
        if ($file->isLink()) {
            return redirect()->away($file->content);
        }

        if ($file->file_password && !session("file_{$file->id}_authorized")) {
            return view('files.password', compact('file'));
        }

        return view('files.show', compact('file'));
    }

    // ────────────────────────────────────────────────────────────────────
    // DESCARGAR  GET /f/{slug}/download
    // ────────────────────────────────────────────────────────────────────

    public function download($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        // Notas: generar .txt al vuelo
        if ($file->isNote()) {
            return response($file->content, 200, [
                'Content-Type'        => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $file->original_name . '"',
            ]);
        }

        $disk = Storage::disk('uploads');

        if (!$disk->exists($file->path)) {
            $file->delete();
            abort(404, 'Archivo no encontrado.');
        }

        $file->increment('downloads_count');

        return $disk->download($file->path, $file->original_name);
    }

    // ────────────────────────────────────────────────────────────────────
    // ELIMINAR  DELETE /f/{slug}?token=xxx
    // ────────────────────────────────────────────────────────────────────

    public function destroy(Request $request, $slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        if (!$request->token || !hash_equals($file->delete_token, $request->token)) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido']);
        }

        $file->removeFileFromStorage();

        return response()->json([
            'status'   => 'success',
            'message'  => 'Eliminado correctamente.',
            'redirect' => route('files.index'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // STREAM INLINE  GET /f/{slug}/stream
    // ────────────────────────────────────────────────────────────────────

    public function streamInline($slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        if ($file->isNote()) {
            return response($file->content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $disk = Storage::disk('uploads');

        if (!$disk->exists($file->path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $stream = $disk->readStream($file->path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type'        => $file->mime,
            'Content-Length'      => $file->size,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // VALIDAR CONTRASEÑA  POST /f/{slug}/password
    // ────────────────────────────────────────────────────────────────────

    public function validatePassword(Request $request, $slug)
    {
        $file = FileShare::where('slug', $slug)->firstOrFail();

        $request->validate(['password' => 'required|string']);

        $attempts = session('attempts', 0);

        if ($attempts >= 5) {
            return back()->with('error', 'Demasiados intentos. Intenta más tarde.');
        }

        if (Hash::check($request->password, $file->file_password)) {
            session(["file_{$file->id}_authorized" => true]);
            session()->forget('attempts');
            return redirect()->route('files.view', $file->slug);
        }

        session(['attempts' => $attempts + 1]);
        $remaining = 5 - ($attempts + 1);

        return back()->with('error', "Contraseña incorrecta. Te quedan {$remaining} intentos.")->withInput();
    }

    // ────────────────────────────────────────────────────────────────────
    // VERIFICAR DISPONIBILIDAD DE SLUG  GET /slug-check?slug=xxx
    // ────────────────────────────────────────────────────────────────────

    public function checkSlug(Request $request)
    {
        $request->validate(['slug' => 'required|string|max:80|alpha_dash']);

        $taken = FileShare::where('slug', \Illuminate\Support\Str::slug($request->slug))->exists();

        return response()->json(['available' => !$taken]);
    }
}