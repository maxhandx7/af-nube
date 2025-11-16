@extends('layouts.app')

@section('title', $file->original_name)

@section('content')
    <div class="card shadow-lg border-0 file-preview-card">
        {{-- Header con información del archivo --}}
        <div class="card-header bg-gradient-primary text-white py-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="file-icon-wrapper">
                        <i class="bi {{ $file->getFileIcon($file->mime, $file->original_name) }} display-6"></i>
                    </div>
                </div>
                <div class="col">
                    <h1 class="h3 mb-1 file-title" style="color: #fff">{{ $file->original_name }}</h1>
                    <p class="mb-2">{{ $file->slug }}</p>
                    <div class="file-meta d-flex flex-wrap gap-3">
                        <span class="file-meta-item">
                            <i class="bi bi-hdd me-1"></i>{{ $file->formatFileSize($file->size) }}
                        </span>
                        <span class="file-meta-item">
                            <i class="bi bi-clock me-1"></i>Subido {{ $file->created_at->diffForHumans() }}
                        </span>
                        <span class="file-meta-item">
                            <i class="bi bi-download me-1"></i>{{ $file->downloads_count }} descargas
                        </span>
                        <span class="file-meta-item">
                            <i class="bi bi-calendar-x me-1"></i>Expira {{ $file->expires_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- Previsualización del archivo --}}
            <div class="preview-section mb-4">
                <h5 class="section-title mb-3">
                    <i class="bi bi-eye me-2"></i>Vista previa
                </h5>

                <div class="preview-container border rounded-3 bg-light">
                    @if (Str::startsWith($file->mime, 'image/'))
                        {{-- Previsualización de imágenes --}}
                        <div class="image-preview text-center p-3">
                            <img src="{{ route('files.stream', $file->slug) }}"
                                class="img-fluid rounded shadow-sm preview-image"
                                alt="Preview de {{ $file->original_name }}" id="preview-image" style="max-height: 500px;">
                            <div class="image-controls mt-3 d-flex justify-content-center gap-2 flex-wrap">
                                <button class="btn btn-outline-primary btn-sm" onclick="zoomImage(1.2)">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="zoomImage(1)">
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
                        {{-- Previsualización de PDF --}}
                        <div class="pdf-preview">
                            <embed src="{{ route('files.stream', $file->slug) }}#toolbar=1&view=FitH" type="application/pdf"
                                width="100%" height="600px" class="pdf-embed">
                            <div
                                class="pdf-controls p-3 bg-white border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">Usa los controles del navegador para navegar el PDF</small>
                                <button class="btn btn-primary btn-sm" onclick="downloadFile()">
                                    <i class="bi bi-download me-1"></i>Descargar PDF
                                </button>
                            </div>
                        </div>
                    @elseif (Str::contains($file->mime, 'text/') ||
                            in_array(pathinfo($file->original_name, PATHINFO_EXTENSION), ['txt', 'csv', 'json', 'xml']))
                        {{-- Previsualización de texto --}}
                        <div class="text-preview">
                            <div
                                class="preview-header bg-dark text-white px-3 py-2 d-flex justify-content-between align-items-center">
                                <span class="small">{{ $file->original_name }}</span>
                                <button class="btn btn-sm btn-outline-light" onclick="downloadFile()">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                            <pre class="p-3 mb-0 bg-dark text-light" style="max-height: 400px; overflow: auto;"><code id="text-content">Cargando contenido...</code></pre>
                        </div>
                    @elseif (Str::contains($file->mime, 'video/'))
                        {{-- Previsualización de video --}}
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
                        {{-- Previsualización de audio --}}
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
                        {{-- Archivo no previsualizable --}}
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

            {{-- Acciones principales --}}
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

            {{-- Información de compartir --}}
            <div class="share-section mb-4">
                <h5 class="section-title mb-3">
                    <i class="bi bi-link me-2"></i>Compartir archivo
                </h5>

                <div class="share-card bg-light rounded-3 p-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="share-url" value="{{ url()->current() }}"
                            readonly>
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
                        {{ $file->slug }}
                    </div>
                </div>

                {{-- Eliminación del archivo --}}
                <div class="delete-section">
                    <h5 class="section-title mb-3 text-danger">
                        <i class="bi bi-trash me-2"></i>Eliminar archivo
                    </h5>

                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                            <div>
                                <strong>Acción irreversible:</strong> Esta acción eliminará permanentemente el archivo
                                y no podrá ser recuperado. Solo el propietario con el token correcto puede eliminar el
                                archivo.
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
            </div>
        </div>
    @endsection

    @push('styles')
        <style>
            .modal {
                position: fixed !important;
            }

            .file-preview-card {
                border: none;
            }

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

            .file-title {
                word-break: break-word;
            }

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
                transition: all 0.3s ease;
                border: none;
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .share-card {
                border-left: 4px solid #28a745;
            }

            @media (max-width: 768px) {
                .file-meta {
                    flex-direction: column;
                    gap: 0.5rem !important;
                }

                .file-meta-item {
                    width: fit-content;
                }

                .action-btn .display-6 {
                    font-size: 2rem !important;
                }
            }
        </style>
    @endpush

    @section('scripts')
        <!-- Agrega esto antes de tu script principal -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Cargar contenido de texto para archivos de texto
                @if (Str::contains($file->mime, 'text/') ||
                        in_array(pathinfo($file->original_name, PATHINFO_EXTENSION), ['txt', 'csv', 'json', 'xml']))
                    loadTextContent();
                @endif

                // Configurar botón de copiar enlace
                const copyUrlBtn = document.getElementById('copy-url-btn');
                copyUrlBtn.addEventListener('click', function() {
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
            });



            function loadTextContent() {
                fetch('{{ route('files.stream', $file->slug) }}')
                    .then(response => {
                        if (!response.ok) throw new Error('Error al cargar el contenido');
                        return response.text();
                    })
                    .then(text => {
                        // Limitar el contenido para archivos muy grandes
                        if (text.length > 100000) {
                            text = text.substring(0, 100000) + '\n\n... (contenido truncado, archivo muy grande)';
                        }
                        document.getElementById('text-content').textContent = text;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('text-content').textContent = 'Error al cargar el contenido del archivo.';
                    });
            }

            function downloadFile() {
                window.location.href = '{{ route('files.download', $file->slug) }}';
            }

            function shareFile() {
                const shareUrl = document.getElementById('share-url');

                if (navigator.share) {
                    navigator.share({
                        title: 'Compartir archivo: {{ $file->original_name }}',
                        text: 'Mira este archivo compartido con AF Nube',
                        url: shareUrl.value
                    });
                } else {
                    shareUrl.select();
                    navigator.clipboard.writeText(shareUrl.value).then(() => {
                        alert('Enlace copiado al portapapeles');
                    });
                }
            }

            // Funciones para manipulación de imágenes
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

            function confirmDelete() {
                const token = document.getElementById('delete-token').value;

                if (!token) {
                    Swal.fire("Error", "Por favor ingresa el token de eliminación", "error");
                    return;
                }

                Swal.fire({
                    title: "¿Está seguro?",
                    text: "Una vez eliminado, no podrá recuperar este archivo.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, borrarlo",
                    cancelButtonText: "No, cancelarlo"
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitDelete(token);
                    }
                });
            }


            function submitDelete(token) {
                const form = document.getElementById('delete-form');
                const url = form.action;

                fetch(url, {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            token: token
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "error") {
                            Swal.fire("Error", data.message, "error");
                        } else {
                            Swal.fire("Eliminado", data.message, "success").then(() => {
                                window.location.href = data.redirect; // Redirige después de cerrar SweetAlert
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire("Error", "Ocurrió un error inesperado", "error");
                    });
            }
        </script>
    @endsection
