@extends('layouts.app')

@section('title', $file->title ?? $file->original_name)

@section('content')
    <div class="card shadow-lg border-0 file-preview-card">

        {{-- Header --}}
        <div class="card-header bg-gradient-primary text-white py-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="file-icon-wrapper">
                        @if($file->isNote())
                            <i class="bi bi-sticky display-6"></i>
                        @elseif($file->isLink())
                            <i class="bi bi-link-45deg display-6"></i>
                        @else
                            <i class="bi {{ $file->getFileIcon($file->mime, $file->original_name) }} display-6"></i>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        {{-- Badge de tipo --}}
                        @if($file->isNote())
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-sticky me-1"></i>Nota
                            </span>
                        @elseif($file->isLink())
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-link-45deg me-1"></i>Link
                            </span>
                        @else
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-file-earmark me-1"></i>Archivo
                            </span>
                        @endif
                    </div>
                    <h1 class="h3 mb-1 file-title" style="color: #fff">
                        {{ $file->title ?? $file->original_name }}
                    </h1>
                    <p class="mb-2 opacity-75">{{ $file->slug }}</p>
                    <div class="file-meta d-flex flex-wrap gap-3">
                        @if($file->isFile())
                            <span class="file-meta-item">
                                <i class="bi bi-hdd me-1"></i>{{ $file->formatFileSize($file->size) }}
                            </span>
                            <span class="file-meta-item">
                                <i class="bi bi-download me-1"></i>{{ $file->downloads_count }} descargas
                            </span>
                        @elseif($file->isNote())
                            <span class="file-meta-item">
                                <i class="bi bi-fonts me-1"></i>{{ number_format(strlen($file->content)) }} caracteres
                            </span>
                        @endif
                        <span class="file-meta-item">
                            <i class="bi bi-clock me-1"></i>Creado {{ $file->created_at->diffForHumans() }}
                        </span>
                        <span class="file-meta-item">
                            <i class="bi bi-calendar-x me-1"></i>Expira {{ $file->expires_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- ══════════════════════════════════════════════════════
                 CONTENIDO SEGÚN TIPO
            ══════════════════════════════════════════════════════ --}}

            {{-- NOTA --}}
            @if($file->isNote())
                <div class="preview-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-sticky me-2"></i>Contenido de la nota
                    </h5>
                    <div class="note-container border rounded-3 bg-light p-0 overflow-hidden">
                        <div class="preview-header bg-dark text-white px-3 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">
                                <i class="bi bi-sticky me-1"></i>
                                {{ $file->title ?? 'Nota sin título' }}
                            </span>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-light" onclick="copyNoteContent()" title="Copiar contenido">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-light" onclick="downloadFile()" title="Descargar .txt">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                        <pre id="note-content" class="p-4 mb-0 bg-white text-dark note-pre" style="max-height: 500px; overflow: auto; white-space: pre-wrap; word-break: break-word; font-family: inherit; font-size: 1rem;">{{ $file->content }}</pre>
                    </div>
                </div>

                {{-- Acciones nota --}}
                <div class="action-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-lightning me-2"></i>Acciones rápidas
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100 py-3 action-btn" onclick="copyNoteContent()">
                                <i class="bi bi-clipboard display-6 mb-2"></i>
                                <div class="fw-bold">Copiar texto</div>
                                <small class="text-white-50">Copiar al portapapeles</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-success w-100 py-3 action-btn" onclick="downloadFile()">
                                <i class="bi bi-download display-6 mb-2"></i>
                                <div class="fw-bold">Descargar .txt</div>
                                <small class="text-white-50">Guardar como archivo</small>
                            </button>
                        </div>
                    </div>
                </div>

            {{-- LINK --}}
            @elseif($file->isLink())
                <div class="preview-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-link-45deg me-2"></i>Enlace guardado
                    </h5>
                    <div class="link-container border rounded-3 p-4 bg-light">
                        <div class="d-flex align-items-start gap-3">
                            <div class="link-icon-wrapper bg-info bg-opacity-10 rounded-3 p-3 flex-shrink-0">
                                <i class="bi bi-globe2 display-6 text-info"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                @if($file->title && $file->title !== $file->content)
                                    <h6 class="fw-bold mb-1">{{ $file->title }}</h6>
                                @endif
                                <p class="text-muted mb-3 text-break small">{{ $file->content }}</p>
                                <a href="{{ $file->content }}" target="_blank" rel="noopener noreferrer"
                                   class="btn btn-info text-white">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Abrir enlace
                                </a>
                                <button class="btn btn-outline-secondary ms-2" onclick="copyLinkUrl()">
                                    <i class="bi bi-clipboard me-1"></i>Copiar URL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- ARCHIVO --}}
            @else
                <div class="preview-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-eye me-2"></i>Vista previa
                    </h5>

                    <div class="preview-container border rounded-3 bg-light">
                        @if (Str::startsWith($file->mime, 'image/'))
                            <div class="image-preview text-center p-3">
                                <img src="{{ route('files.stream', $file->slug) }}"
                                    class="img-fluid rounded shadow-sm preview-image"
                                    alt="Preview de {{ $file->original_name }}" id="preview-image" style="max-height: 500px;">
                                <div class="image-controls mt-3 d-flex justify-content-center gap-2 flex-wrap">
                                    <button class="btn btn-outline-primary btn-sm" onclick="zoomImage(1.2)">
                                        <i class="bi bi-zoom-in"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="zoomImage(0.8)">
                                        <i class="bi bi-zoom-out"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="rotateImage(90)">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadFile()">
                                        <i class="bi bi-download"></i> Descargar
                                    </button>
                                </div>
                            </div>
                        @elseif (Str::contains($file->mime, 'pdf'))
                            <div class="pdf-preview">
                                <embed src="{{ route('files.stream', $file->slug) }}#toolbar=1&view=FitH" type="application/pdf"
                                    width="100%" height="600px" class="pdf-embed">
                                <div class="pdf-controls p-3 bg-white border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Usa los controles del navegador para navegar el PDF</small>
                                    <button class="btn btn-primary btn-sm" onclick="downloadFile()">
                                        <i class="bi bi-download me-1"></i>Descargar PDF
                                    </button>
                                </div>
                            </div>
                        @elseif (Str::contains($file->mime, 'text/') ||
                                in_array(pathinfo($file->original_name, PATHINFO_EXTENSION), ['txt', 'csv', 'json', 'xml']))
                            <div class="text-preview">
                                <div class="preview-header bg-dark text-white px-3 py-2 d-flex justify-content-between align-items-center">
                                    <span class="small">{{ $file->original_name }}</span>
                                    <button class="btn btn-sm btn-outline-light" onclick="downloadFile()">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                                <pre class="p-3 mb-0 bg-dark text-light" style="max-height: 400px; overflow: auto;"><code id="text-content">Cargando contenido...</code></pre>
                            </div>
                        @elseif (Str::contains($file->mime, 'video/'))
                            <div class="video-preview text-center p-3">
                                <video controls class="video-player rounded" style="max-width: 100%; max-height: 500px;">
                                    <source src="{{ route('files.stream', $file->slug) }}" type="{{ $file->mime }}">
                                    Tu navegador no soporta la reproducción de video.
                                </video>
                                <div class="mt-3">
                                    <button class="btn btn-primary" onclick="downloadFile()">
                                        <i class="bi bi-download me-1"></i>Descargar Video
                                    </button>
                                </div>
                            </div>
                        @elseif (Str::contains($file->mime, 'audio/'))
                            <div class="audio-preview p-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="bi bi-music-note-beamed display-4 text-primary"></i>
                                    </div>
                                    <div class="col">
                                        <audio controls class="w-100">
                                            <source src="{{ route('files.stream', $file->slug) }}" type="{{ $file->mime }}">
                                            Tu navegador no soporta la reproducción de audio.
                                        </audio>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-primary" onclick="downloadFile()">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="no-preview text-center py-5">
                                <i class="bi bi-file-earmark-x display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Vista previa no disponible</h5>
                                <p class="text-muted mb-3">Este tipo de archivo no puede previsualizarse en el navegador.</p>
                                <button class="btn btn-primary" onclick="downloadFile()">
                                    <i class="bi bi-download me-1"></i>Descargar Archivo
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones archivo --}}
                <div class="action-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-lightning me-2"></i>Acciones rápidas
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-primary w-100 py-3 action-btn" onclick="downloadFile()">
                                <i class="bi bi-download display-6 mb-2"></i>
                                <div class="fw-bold">Descargar</div>
                                <small class="text-white-50">Obtener una copia local</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-success w-100 py-3 action-btn" onclick="shareFile()">
                                <i class="bi bi-share display-6 mb-2"></i>
                                <div class="fw-bold">Compartir</div>
                                <small class="text-white-50">Compartir enlace con otros</small>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 COMPARTIR (para notas y archivos, no links)
            ══════════════════════════════════════════════════════ --}}
            @unless($file->isLink())
                <div class="share-section mb-4">
                    <h5 class="section-title mb-3">
                        <i class="bi bi-link me-2"></i>Compartir
                    </h5>
                    <div class="share-card bg-light rounded-3 p-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="share-url" value="{{ url()->current() }}" readonly>
                            <button class="btn btn-outline-primary" type="button" id="copy-url-btn">
                                <i class="bi bi-clipboard me-1"></i>Copiar enlace
                            </button>
                        </div>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Este enlace expirará el {{ $file->expires_at->format('d/m/Y \a \l\a\s H:i') }}
                        </div>
                        <div class="form-text mt-1">
                            <i class="bi bi-shield-lock me-1"></i>
                            Slug: <strong>{{ $file->slug }}</strong>
                            @if($file->custom_slug)
                                <span class="badge bg-success ms-1">personalizado</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endunless

            {{-- ══════════════════════════════════════════════════════
                 ELIMINAR
            ══════════════════════════════════════════════════════ --}}
            <div class="delete-section">
                <h5 class="section-title mb-3 text-danger">
                    <i class="bi bi-trash me-2"></i>Eliminar
                </h5>

                <div class="alert alert-warning">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                        <div>
                            <strong>Acción irreversible:</strong> Solo el propietario con el token correcto puede eliminar este elemento.
                        </div>
                    </div>
                </div>

                <form action="{{ route('files.delete', $file->slug) }}" method="POST" id="delete-form">
                    @csrf
                    @method('DELETE')
                    <div class="row g-2">
                        <div class="col-md-8">
                            <label for="delete-token" class="form-label fw-semibold">Token de eliminación</label>
                            <input type="text" name="token" id="delete-token" class="form-control" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                                <i class="bi bi-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row mt-5">
                            <div class="col-12 text-center mt-xl-2">
                                <a class="btn btn-primary font-weight-medium" href="{{ url('/') }}">Volver a subir</a>
                            </div>
                        </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .modal { position: fixed !important; }

        .file-preview-card { border: none; }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%) !important;
        }

        .file-icon-wrapper {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-title { word-break: break-word; }

        .file-meta-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .section-title {
            color: #4361ee;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        .preview-container {
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            border: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .share-card { border-left: 4px solid #28a745; }

        .note-pre {
            line-height: 1.7;
            font-size: 1rem;
        }

        .link-icon-wrapper {
            min-width: 80px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .file-meta {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            .file-meta-item { width: fit-content; }
            .action-btn .display-6 { font-size: 2rem !important; }
            .link-icon-wrapper { min-width: 60px; min-height: 60px; }
        }
    </style>
@endpush

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Cargar texto para archivos de texto plano
            @if($file->isFile() && (Str::contains($file->mime, 'text/') || in_array(pathinfo($file->original_name, PATHINFO_EXTENSION), ['txt', 'csv', 'json', 'xml'])))
                loadTextContent();
            @endif

            // Copiar enlace
            const copyUrlBtn = document.getElementById('copy-url-btn');
            if (copyUrlBtn) {
                copyUrlBtn.addEventListener('click', function () {
                    const shareUrl = document.getElementById('share-url');
                    shareUrl.select();
                    navigator.clipboard.writeText(shareUrl.value).then(() => {
                        const originalHtml = this.innerHTML;
                        this.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado';
                        this.classList.add('btn-success');
                        setTimeout(() => {
                            this.innerHTML = originalHtml;
                            this.classList.remove('btn-success');
                        }, 2000);
                    });
                });
            }
        });

        // ── Archivo de texto ────────────────────────────────────────────
        function loadTextContent() {
            fetch('{{ route('files.stream', $file->slug) }}')
                .then(r => { if (!r.ok) throw new Error(); return r.text(); })
                .then(text => {
                    if (text.length > 100000) text = text.substring(0, 100000) + '\n\n... (contenido truncado)';
                    document.getElementById('text-content').textContent = text;
                })
                .catch(() => {
                    document.getElementById('text-content').textContent = 'Error al cargar el contenido.';
                });
        }

        // ── Descargar ───────────────────────────────────────────────────
        function downloadFile() {
            window.location.href = '{{ route('files.download', $file->slug) }}';
        }

        // ── Compartir (Web Share API) ────────────────────────────────────
 function shareFile() {
  const shareData = {
    title: '{{ $file->title ?? $file->original_name }}',
    text: '¡Mira este archivo que compartí contigo!',
    url: window.location.href
  };

  if (navigator.share) {
    // Si el navegador es compatible (Móviles, Windows, macOS)
    navigator.share(shareData)
      .then(() => console.log('Compartido'))
      .catch((err) => console.log('Cancelado o error:', err));
  } else {
    // Si NO es compatible (Linux, Navegadores viejos), usamos botones manuales
    showFallbackShare(shareData);
  }
}

function showFallbackShare(data) {
  const urlEncoded = encodeURIComponent(data.url);
  const textEncoded = encodeURIComponent(data.text);

  Swal.fire({
    title: 'Compartir enlace',
    html: `
      <div class="d-flex justify-content-around mt-3">
        <a href="https://wa.me/?text=${textEncoded}%20${urlEncoded}" target="_blank" class="text-success"><i class="bi bi-whatsapp display-6"></i></a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=${urlEncoded}" target="_blank" class="text-primary"><i class="bi bi-facebook display-6"></i></a>
        <a href="https://twitter.com/intent/tweet?text=${textEncoded}&url=${urlEncoded}" target="_blank" class="text-dark"><i class="bi bi-twitter-x display-6"></i></a>
        <button onclick="copyToClipboard('${data.url}')" class="btn btn-light"><i class="bi bi-link-45deg display-6"></i></button>
      </div>
    `,
    showConfirmButton: false,
    showCloseButton: true
  });
}

        // ── Copiar nota ─────────────────────────────────────────────────
        function copyNoteContent() {
            const content = document.getElementById('note-content')?.innerText ?? '';
            navigator.clipboard.writeText(content).then(() => {
                Swal.fire({ icon: 'success', title: '¡Copiado!', text: 'Contenido copiado al portapapeles.', timer: 1500, showConfirmButton: false });
            });
        }

        // ── Copiar URL del link ──────────────────────────────────────────
        function copyLinkUrl() {
            navigator.clipboard.writeText('{{ addslashes($file->content) }}').then(() => {
                Swal.fire({ icon: 'success', title: '¡Copiado!', text: 'URL copiada al portapapeles.', timer: 1500, showConfirmButton: false });
            });
        }

        // ── Zoom / Rotate imagen ─────────────────────────────────────────
        let currentScale = 1;
        let currentRotation = 0;

        function zoomImage(factor) {
            const image = document.getElementById('preview-image');
            if (image) {
                currentScale *= factor;
                image.style.transform = `scale(${currentScale}) rotate(${currentRotation}deg)`;
            }
        }

        function rotateImage(degrees) {
            const image = document.getElementById('preview-image');
            if (image) {
                currentRotation += degrees;
                image.style.transform = `scale(${currentScale}) rotate(${currentRotation}deg)`;
            }
        }

        // ── Eliminar ─────────────────────────────────────────────────────
        function confirmDelete() {
            const token = document.getElementById('delete-token').value;
            if (!token) {
                Swal.fire('Error', 'Por favor ingresa el token de eliminación', 'error');
                return;
            }
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Una vez eliminado, no podrá recuperar este elemento.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) submitDelete(token);
            });
        }

        function submitDelete(token) {
            const form = document.getElementById('delete-form');
            fetch(form.action, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ token })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'error') {
                    Swal.fire('Error', data.message, 'error');
                } else {
                    Swal.fire('Eliminado', data.message, 'success').then(() => {
                        window.location.href = data.redirect;
                    });
                }
            })
            .catch(() => Swal.fire('Error', 'Ocurrió un error inesperado', 'error'));
        }
    </script>
@endsection