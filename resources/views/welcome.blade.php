@extends('layouts.app')

@section('title', 'Compartir archivos fácilmente')

@section('content')
    <div class="card shadow-lg border-0 upload-card">

        {{-- Header con tabs --}}
        <div class="card-header bg-primary text-white py-0">
            <ul class="nav nav-tabs nav-tabs-custom" id="modeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-file" data-bs-toggle="tab" data-bs-target="#panel-file"
                        type="button" role="tab">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Archivo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-note" data-bs-toggle="tab" data-bs-target="#panel-note"
                        type="button" role="tab">
                        <i class="bi bi-sticky me-2"></i>Nota
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-link" data-bs-toggle="tab" data-bs-target="#panel-link"
                        type="button" role="tab">
                        <i class="bi bi-link-45deg me-2"></i>Link
                    </button>
                </li>
                <li class="nav-item ms-auto" role="presentation">
                    <button class="nav-link" id="tab-search" data-bs-toggle="tab" data-bs-target="#panel-search"
                        type="button" role="tab">
                        <i class="bi bi-search me-2"></i>Buscar
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">

            {{-- ══════════════════════════════════════════
                 TAB ARCHIVO
            ══════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="panel-file" role="tabpanel">
                <div class="card-body p-4">

                    <div id="upload-progress" class="progress-container d-none mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Subiendo archivo...</span>
                            <span class="small text-muted" id="progress-percentage">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <form id="upload-form" action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="file-drop-area mb-4 p-4 border-2 border-dashed rounded-3 text-center"
                            id="file-drop-area">
                            <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3"></i>
                            <p class="mb-2">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                            <p class="small text-muted mb-3">Tamaño máximo: 50 MB</p>
                            <input type="file" name="file" id="file" class="file-input" required>
                            <div class="file-info mt-3" id="file-info"></div>
                        </div>

                        <div class="settings-card p-3 bg-light rounded-3 mb-4">
                            <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Configuración</h5>

                            <div class="mb-3">
                                <label for="expire_days" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Tiempo de expiración
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="range" name="expire_days" id="expire_days"
                                        class="form-range me-3" min="1" max="30" value="3">
                                    <span class="badge bg-primary fs-6" id="days-display">3 días</span>
                                </div>
                                <div class="form-text">El archivo se eliminará automáticamente después del tiempo seleccionado</div>
                            </div>

                            <div class="mb-3">
                                <label for="custom_slug_file" class="form-label fw-semibold">
                                    <i class="bi bi-link me-1"></i>URL amigable <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted small">/f/</span>
                                    <input type="text" name="custom_slug" id="custom_slug_file"
                                        class="form-control" placeholder="mi-archivo-genial"
                                        pattern="[a-zA-Z0-9\-_]+" maxlength="80">
                                    <span class="input-group-text slug-status" id="slug-status-file">
                                        <i class="bi bi-dash text-muted"></i>
                                    </span>
                                </div>
                                <div class="form-text">Solo letras, números y guiones. Vacío = se genera uno automático.</div>
                            </div>

                            <div class="mb-0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="password-protect">
                                    <label class="form-check-label" for="password-protect">Proteger con contraseña</label>
                                </div>
                                <div id="password-field" class="mt-2 d-none">
                                    <input type="password" class="form-control" placeholder="Ingresa contraseña"
                                        id="file_password" name="file_password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 py-2 upload-btn" id="submit-btn" disabled>
                            <i class="bi bi-upload me-2"></i>
                            <span class="btn-text">Subir archivo</span>
                            <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="submit-spinner">
                                <span class="visually-hidden">Subiendo...</span>
                            </div>
                        </button>
                    </form>
                </div>

                <div id="upload-result" class="card-footer d-none p-4">
                    <div class="success-card p-3 bg-success bg-opacity-10 border border-success rounded-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success fs-2 me-3"></i>
                            <div>
                                <h4 class="text-success mb-1">¡Archivo subido con éxito!</h4>
                                <p class="text-muted mb-0">Tu archivo está listo para compartir</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Enlace para compartir:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="result-link-input" readonly>
                                    <button class="btn btn-outline-success" type="button" id="copy-link-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Token de eliminación:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="result-token-input" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copy-token-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                                <div class="form-text text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Guarda este token para eliminar el archivo antes de su expiración
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary" id="new-upload-btn">
                                <i class="bi bi-plus-circle me-1"></i>Subir otro
                            </button>
                            <a href="#" class="btn btn-outline-success" id="share-btn" target="_blank">
                                <i class="bi bi-eye me-1"></i>Ver archivo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 TAB NOTA
            ══════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="panel-note" role="tabpanel">
                <div class="card-body p-4">
                    <form id="note-form">
                        @csrf

                        <div class="mb-3">
                            <label for="note_title" class="form-label fw-semibold">
                                <i class="bi bi-fonts me-1"></i>Título <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text" id="note_title" name="title" class="form-control"
                                placeholder="Título de la nota...">
                        </div>

                        <div class="mb-3">
                            <label for="note_content" class="form-label fw-semibold">
                                <i class="bi bi-sticky me-1"></i>Contenido
                            </label>
                            <textarea id="note_content" name="content" class="form-control note-textarea"
                                placeholder="Escribe tu nota aquí..." rows="10" required></textarea>
                            <div class="form-text d-flex justify-content-between">
                                <span>Máximo 500.000 caracteres</span>
                                <span id="note-char-count">0 caracteres</span>
                            </div>
                        </div>

                        <div class="settings-card p-3 bg-light rounded-3 mb-4">
                            <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Configuración</h5>

                            <div class="mb-3">
                                <label for="note_expire_days" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Tiempo de expiración
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="range" name="expire_days" id="note_expire_days"
                                        class="form-range me-3" min="1" max="30" value="3">
                                    <span class="badge bg-warning text-dark fs-6" id="note-days-display">3 días</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="custom_slug_note" class="form-label fw-semibold">
                                    <i class="bi bi-link me-1"></i>URL amigable <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted small">/f/</span>
                                    <input type="text" name="custom_slug" id="custom_slug_note"
                                        class="form-control" placeholder="mi-nota-secreta"
                                        pattern="[a-zA-Z0-9\-_]+" maxlength="80">
                                    <span class="input-group-text slug-status" id="slug-status-note">
                                        <i class="bi bi-dash text-muted"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="note-password-protect">
                                    <label class="form-check-label" for="note-password-protect">Proteger con contraseña</label>
                                </div>
                                <div id="note-password-field" class="mt-2 d-none">
                                    <input type="password" class="form-control" placeholder="Ingresa contraseña"
                                        id="note_password" name="file_password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-lg w-100 py-2" id="note-submit-btn">
                            <i class="bi bi-sticky me-2"></i>
                            <span class="btn-text">Guardar nota</span>
                            <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="note-spinner"></div>
                        </button>
                    </form>
                </div>

                <div id="note-result" class="card-footer d-none p-4">
                    <div class="success-card p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-warning fs-2 me-3"></i>
                            <div>
                                <h4 class="text-warning mb-1">¡Nota guardada!</h4>
                                <p class="text-muted mb-0">Tu nota está lista para compartir</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Enlace de la nota:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="note-result-link" readonly>
                                    <button class="btn btn-outline-warning" type="button" id="copy-note-link-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Token de eliminación:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="note-result-token" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copy-note-token-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                                <div class="form-text text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Guarda este token para poder eliminar la nota
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-warning" id="new-note-btn">
                                <i class="bi bi-plus-circle me-1"></i>Nueva nota
                            </button>
                            <a href="#" class="btn btn-outline-success" id="note-share-btn" target="_blank">
                                <i class="bi bi-eye me-1"></i>Ver nota
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 TAB LINK
            ══════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="panel-link" role="tabpanel">
                <div class="card-body p-4">
                    <form id="link-form">
                        @csrf

                        <div class="mb-3">
                            <label for="link_url" class="form-label fw-semibold">
                                <i class="bi bi-globe2 me-1"></i>URL a guardar
                            </label>
                            <input type="url" id="link_url" name="url" class="form-control form-control-lg"
                                placeholder="https://ejemplo.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="link_title" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>Título <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text" id="link_title" name="title" class="form-control"
                                placeholder="Nombre descriptivo del enlace...">
                        </div>

                        <div class="settings-card p-3 bg-light rounded-3 mb-4">
                            <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Configuración</h5>

                            <div class="mb-3">
                                <label for="link_expire_days" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Tiempo de expiración
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="range" name="expire_days" id="link_expire_days"
                                        class="form-range me-3" min="1" max="30" value="3">
                                    <span class="badge bg-info text-dark fs-6" id="link-days-display">3 días</span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="custom_slug_link" class="form-label fw-semibold">
                                    <i class="bi bi-link me-1"></i>URL amigable <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted small">/f/</span>
                                    <input type="text" name="custom_slug" id="custom_slug_link"
                                        class="form-control" placeholder="mi-link-favorito"
                                        pattern="[a-zA-Z0-9\-_]+" maxlength="80" autocomplete="one-time-code">
                                    <span class="input-group-text slug-status" id="slug-status-link">
                                        <i class="bi bi-dash text-muted"></i>
                                    </span>
                                </div>
                                <div class="form-text">Cuando alguien acceda a este slug será redirigido directamente a la URL.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info text-white btn-lg w-100 py-2" id="link-submit-btn">
                            <i class="bi bi-link-45deg me-2"></i>
                            <span class="btn-text">Guardar link</span>
                            <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="link-spinner"></div>
                        </button>
                    </form>
                </div>

                <div id="link-result" class="card-footer d-none p-4">
                    <div class="success-card p-3 bg-info bg-opacity-10 border border-info rounded-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-info fs-2 me-3"></i>
                            <div>
                                <h4 class="text-info mb-1">¡Link guardado!</h4>
                                <p class="text-muted mb-0">El enlace está listo para compartir</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Enlace corto:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="link-result-link" readonly>
                                    <button class="btn btn-outline-info" type="button" id="copy-link-link-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Token de eliminación:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="link-result-token" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copy-link-token-btn">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                                <div class="form-text text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Guarda este token para poder eliminar el link
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-info" id="new-link-btn">
                                <i class="bi bi-plus-circle me-1"></i>Nuevo link
                            </button>
                            <a href="#" class="btn btn-outline-success" id="link-share-btn" target="_blank">
                                <i class="bi bi-eye me-1"></i>Probar enlace
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 TAB BUSCAR
            ══════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="panel-search" role="tabpanel">
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Si ya conoces el slug de un archivo, nota o link, búscalo directamente aquí.
                    </p>

                    <div class="mb-4">
                        <label for="search_slug" class="form-label fw-semibold">
                            <i class="bi bi-search me-1"></i>Slug o URL amigable
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text text-muted">/f/</span>
                            <input type="text" id="search_slug" class="form-control"
                                placeholder="rojo-gato-1234 o mi-slug-personalizado">
                            <button class="btn btn-primary" type="button" id="search-btn">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                        <div id="search-feedback" class="mt-2"></div>
                    </div>
                </div>
            </div>

        </div>{{-- fin tab-content --}}
    </div>

    {{-- Info cards --}}
    <div class="row mt-4 g-3">
        <div class="col-md-4">
            <div class="info-card p-3 h-100 border-start border-4 border-primary bg-white rounded">
                <i class="bi bi-shield-check text-primary fs-4 mb-2"></i>
                <h5 class="fw-semibold">Seguro</h5>
                <p class="small text-muted mb-0">Tus archivos se eliminan automáticamente después del tiempo establecido</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card p-3 h-100 border-start border-4 border-success bg-white rounded">
                <i class="bi bi-lightning text-success fs-4 mb-2"></i>
                <h5 class="fw-semibold">Rápido</h5>
                <p class="small text-muted mb-0">Subida y descarga de archivos con alta velocidad</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card p-3 h-100 border-start border-4 border-info bg-white rounded">
                <i class="bi bi-infinity text-info fs-4 mb-2"></i>
                <h5 class="fw-semibold">Sin registro</h5>
                <p class="small text-muted mb-0">Comparte archivos sin necesidad de crear una cuenta</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .nav-tabs-custom {
        border-bottom: none;
        padding-top: 0.25rem;
    }
    .nav-tabs-custom .nav-link {
        color: rgba(255,255,255,0.7);
        border: none;
        border-radius: 0;
        padding: 0.85rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s;
        border-bottom: 3px solid transparent;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .nav-tabs-custom .nav-link.active {
        color: #fff;
        background: transparent;
        border-bottom: 3px solid #fff;
    }
    .file-drop-area {
        border-color: #dee2e6;
        transition: all 0.3s ease;
        background: #f8f9fa;
        cursor: pointer;
    }
    .file-drop-area:hover   { border-color: #4361ee; background: #f0f4ff; }
    .file-drop-area.dragover { border-color: #4361ee; background: #e8edff; transform: scale(1.02); }
    .file-input { display: none; }
    .file-info  { font-weight: 500; }
    .file-info .file-name { color: #4361ee; }
    .file-info .file-size  { color: #6c757d; font-size: 0.875rem; }
    .upload-btn:disabled { cursor: not-allowed; }
    .settings-card { border-left: 4px solid #4361ee; }
    .note-textarea {
        font-family: inherit;
        font-size: 1rem;
        line-height: 1.7;
        resize: vertical;
        min-height: 200px;
    }
    .slug-status {
        background: transparent;
        border-left: none;
        min-width: 38px;
        justify-content: center;
    }
    .info-card { transition: transform 0.2s ease; }
    .info-card:hover { transform: translateY(-2px); }
    .success-card { animation: fadeInUp 0.5s ease; }
    .progress-container { transition: all 0.3s ease; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 576px) {
        .nav-tabs-custom .nav-link { padding: 0.75rem 0.75rem; font-size: 0.85rem; }
        .nav-tabs-custom .nav-link i { display: none; }
    }
</style>
@endpush

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── ARCHIVO ─────────────────────────────────────────────────────────
    const form              = document.getElementById('upload-form');
    const fileInput         = document.getElementById('file');
    const fileDropArea      = document.getElementById('file-drop-area');
    const fileInfo          = document.getElementById('file-info');
    const expireDays        = document.getElementById('expire_days');
    const daysDisplay       = document.getElementById('days-display');
    const submitBtn         = document.getElementById('submit-btn');
    const submitSpinner     = document.getElementById('submit-spinner');
    const btnText           = form.querySelector('.btn-text');
    const progressContainer = document.getElementById('upload-progress');
    const progressBar       = document.getElementById('progress-bar');
    const progressPercentage= document.getElementById('progress-percentage');
    const result            = document.getElementById('upload-result');
    const resultLinkInput   = document.getElementById('result-link-input');
    const resultTokenInput  = document.getElementById('result-token-input');
    const copyLinkBtn       = document.getElementById('copy-link-btn');
    const copyTokenBtn      = document.getElementById('copy-token-btn');
    const newUploadBtn      = document.getElementById('new-upload-btn');
    const shareBtn          = document.getElementById('share-btn');
    const passwordProtect   = document.getElementById('password-protect');
    const passwordField     = document.getElementById('password-field');

    expireDays.addEventListener('input', () => {
        daysDisplay.textContent = `${expireDays.value} día${expireDays.value > 1 ? 's' : ''}`;
    });

    ['dragenter','dragover','dragleave','drop'].forEach(ev =>
        fileDropArea.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); })
    );
    ['dragenter','dragover'].forEach(ev => fileDropArea.addEventListener(ev, () => fileDropArea.classList.add('dragover')));
    ['dragleave','drop'].forEach(ev => fileDropArea.addEventListener(ev, () => fileDropArea.classList.remove('dragover')));
    fileDropArea.addEventListener('click', () => fileInput.click());
    fileDropArea.addEventListener('drop', e => { fileInput.files = e.dataTransfer.files; handleFiles(fileInput.files); });
    fileInput.addEventListener('change', function () { handleFiles(this.files); });

    function handleFiles(files) {
        if (!files.length) return;
        const file = files[0];
        if (file.size > parseInt(fileInput.dataset.maxSize)) {
            showAlert('El archivo es demasiado grande. Máximo 50MB', 'danger');
            resetFileInput(); return;
        }
        fileInfo.innerHTML = `<div class="file-name"><i class="bi bi-file-earmark me-1"></i>${file.name}</div><div class="file-size">${formatFileSize(file.size)}</div>`;
        submitBtn.disabled = false;
    }

    function resetFileInput() { fileInput.value = ''; fileInfo.innerHTML = ''; submitBtn.disabled = true; }

    passwordProtect.addEventListener('change', function () {
        passwordField.classList.toggle('d-none', !this.checked);
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!fileInput.files.length) { showAlert('Por favor, selecciona un archivo', 'warning'); return; }
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
        btnText.textContent = 'Subiendo...';
        progressContainer.classList.remove('d-none');
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || data.message || 'Error inesperado');
            resultLinkInput.value  = data.url;
            resultTokenInput.value = data.delete_token;
            shareBtn.href = data.url;
            result.classList.remove('d-none');
            form.reset(); resetFileInput();
            result.scrollIntoView({ behavior: 'smooth' });
        } catch (err) {
            showAlert(err.message, 'danger');
        } finally {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
            btnText.textContent = 'Subir archivo';
            progressContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressPercentage.textContent = '0%';
        }
    });

    copyLinkBtn.addEventListener('click',  () => copyAndFlash(resultLinkInput,  copyLinkBtn));
    copyTokenBtn.addEventListener('click', () => copyAndFlash(resultTokenInput, copyTokenBtn));
    newUploadBtn.addEventListener('click', () => { result.classList.add('d-none'); form.scrollIntoView({ behavior: 'smooth' }); });

    // ── NOTA ────────────────────────────────────────────────────────────
    const noteForm            = document.getElementById('note-form');
    const noteContent         = document.getElementById('note_content');
    const noteCharCount       = document.getElementById('note-char-count');
    const noteExpireDays      = document.getElementById('note_expire_days');
    const noteDaysDisplay     = document.getElementById('note-days-display');
    const noteSubmitBtn       = document.getElementById('note-submit-btn');
    const noteSpinner         = document.getElementById('note-spinner');
    const noteResult          = document.getElementById('note-result');
    const noteResultLink      = document.getElementById('note-result-link');
    const noteResultToken     = document.getElementById('note-result-token');
    const notePasswordProtect = document.getElementById('note-password-protect');
    const notePasswordField   = document.getElementById('note-password-field');

    noteContent.addEventListener('input', () => {
        noteCharCount.textContent = `${noteContent.value.length.toLocaleString()} caracteres`;
    });
    noteExpireDays.addEventListener('input', () => {
        noteDaysDisplay.textContent = `${noteExpireDays.value} día${noteExpireDays.value > 1 ? 's' : ''}`;
    });
    notePasswordProtect.addEventListener('change', function () {
        notePasswordField.classList.toggle('d-none', !this.checked);
    });

    noteForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!noteContent.value.trim()) { showAlert('La nota no puede estar vacía', 'warning'); return; }
        noteSubmitBtn.disabled = true;
        noteSpinner.classList.remove('d-none');
        try {
            const response = await fetch('{{ route("files.storeNote") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    content:      noteContent.value,
                    title:        document.getElementById('note_title').value,
                    expire_days:  noteExpireDays.value,
                    file_password:document.getElementById('note_password')?.value || null,
                    custom_slug:  document.getElementById('custom_slug_note').value || null,
                })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Error inesperado');
            noteResultLink.value  = data.url;
            noteResultToken.value = data.delete_token;
            document.getElementById('note-share-btn').href = data.url;
            noteResult.classList.remove('d-none');
            noteForm.reset();
            noteCharCount.textContent = '0 caracteres';
            noteResult.scrollIntoView({ behavior: 'smooth' });
        } catch (err) {
            showAlert(err.message, 'danger');
        } finally {
            noteSubmitBtn.disabled = false;
            noteSpinner.classList.add('d-none');
        }
    });

    document.getElementById('copy-note-link-btn').addEventListener('click',  () => copyAndFlash(noteResultLink,  document.getElementById('copy-note-link-btn')));
    document.getElementById('copy-note-token-btn').addEventListener('click', () => copyAndFlash(noteResultToken, document.getElementById('copy-note-token-btn')));
    document.getElementById('new-note-btn').addEventListener('click', () => { noteResult.classList.add('d-none'); noteForm.scrollIntoView({ behavior: 'smooth' }); });

    // ── LINK ────────────────────────────────────────────────────────────
    const linkForm        = document.getElementById('link-form');
    const linkExpireDays  = document.getElementById('link_expire_days');
    const linkDaysDisplay = document.getElementById('link-days-display');
    const linkSubmitBtn   = document.getElementById('link-submit-btn');
    const linkSpinner     = document.getElementById('link-spinner');
    const linkResult      = document.getElementById('link-result');
    const linkResultLink  = document.getElementById('link-result-link');
    const linkResultToken = document.getElementById('link-result-token');

    linkExpireDays.addEventListener('input', () => {
        linkDaysDisplay.textContent = `${linkExpireDays.value} día${linkExpireDays.value > 1 ? 's' : ''}`;
    });

    linkForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const url = document.getElementById('link_url').value.trim();
        if (!url) { showAlert('Ingresa una URL válida', 'warning'); return; }
        linkSubmitBtn.disabled = true;
        linkSpinner.classList.remove('d-none');
        try {
            const response = await fetch('{{ route("files.storeLink") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    url,
                    title:       document.getElementById('link_title').value,
                    expire_days: linkExpireDays.value,
                    custom_slug: document.getElementById('custom_slug_link').value || null,
                })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Error inesperado');
            linkResultLink.value  = data.url;
            linkResultToken.value = data.delete_token;
            document.getElementById('link-share-btn').href = data.url;
            linkResult.classList.remove('d-none');
            linkForm.reset();
            linkResult.scrollIntoView({ behavior: 'smooth' });
        } catch (err) {
            showAlert(err.message, 'danger');
        } finally {
            linkSubmitBtn.disabled = false;
            linkSpinner.classList.add('d-none');
        }
    });

    document.getElementById('copy-link-link-btn').addEventListener('click',  () => copyAndFlash(linkResultLink,  document.getElementById('copy-link-link-btn')));
    document.getElementById('copy-link-token-btn').addEventListener('click', () => copyAndFlash(linkResultToken, document.getElementById('copy-link-token-btn')));
    document.getElementById('new-link-btn').addEventListener('click', () => { linkResult.classList.add('d-none'); linkForm.scrollIntoView({ behavior: 'smooth' }); });

    // ── BÚSQUEDA ─────────────────────────────────────────────────────────
    document.getElementById('search-btn').addEventListener('click', async function () {
        const slug     = document.getElementById('search_slug').value.trim();
        const feedback = document.getElementById('search-feedback');
        if (!slug) { feedback.innerHTML = '<div class="alert alert-warning py-2">Ingresa un slug para buscar.</div>'; return; }
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const response = await fetch(`{{ url('/search') }}?slug=${encodeURIComponent(slug)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'No encontrado');
            feedback.innerHTML = `
                <div class="alert alert-success py-2 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-check-circle me-1"></i>Encontrado — <strong>${data.slug}</strong> (${data.type})</span>
                    <a href="${data.url}" class="btn btn-sm btn-success" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>`;
        } catch (err) {
            feedback.innerHTML = `<div class="alert alert-danger py-2"><i class="bi bi-x-circle me-1"></i>${err.message}</div>`;
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-search me-1"></i>Buscar';
        }
    });

    document.getElementById('search_slug').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') document.getElementById('search-btn').click();
    });

    // ── VERIFICACIÓN SLUG EN TIEMPO REAL ─────────────────────────────────
    ['file', 'note', 'link'].forEach(type => {
        const input  = document.getElementById(`custom_slug_${type}`);
        const status = document.getElementById(`slug-status-${type}`);
        let timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            const val = this.value.trim();
            status.innerHTML = '<i class="bi bi-dash text-muted"></i>';
            if (!val) return;
            timer = setTimeout(async () => {
                try {
                    const r = await fetch(`{{ url('/slug-check') }}?slug=${encodeURIComponent(val)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const d = await r.json();
                    status.innerHTML = d.available
                        ? '<i class="bi bi-check-circle-fill text-success"></i>'
                        : '<i class="bi bi-x-circle-fill text-danger"></i>';
                } catch {
                    status.innerHTML = '<i class="bi bi-dash text-muted"></i>';
                }
            }, 500);
        });
    });

    // ── UTILIDADES ───────────────────────────────────────────────────────
    function formatFileSize(bytes) {
        if (!bytes) return '0 Bytes';
        const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function copyAndFlash(input, btn) {
        navigator.clipboard.writeText(input.value);
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado';
        btn.classList.add('btn-success');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('btn-success'); }, 2000);
    }

    function showAlert(message, type) {
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        el.style.zIndex = 9999;
        el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }
});
</script>
@endsection