@extends('layouts.app')

@section('title', 'Af-nube - Compartir archivos fácilmente')

@section('content')
    <div class="card shadow-lg border-0 upload-card">
        <div class="card-header bg-primary text-white py-3">
            <h2 class="h4 mb-0" style="color: #fff"><i class="bi bi-cloud-arrow-up me-2"></i>Subir archivo</h2>
        </div>
        
        <div class="card-body p-4">
            {{-- Indicador de progreso --}}
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

            {{-- Formulario principal --}}
            <form id="upload-form" action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Área de arrastrar y soltar --}}
                <div class="file-drop-area mb-4 p-4 border-2 border-dashed rounded-3 text-center" 
                     id="file-drop-area">
                    <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3"></i>
                    <p class="mb-2">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                    <p class="small text-muted mb-3">Tamaño máximo: 100MB</p>
                    <input type="file" name="file" id="file" class="file-input" required 
                           data-max-size="104857600"> <!-- 100MB en bytes -->
                    <div class="file-info mt-3" id="file-info"></div>
                </div>

                {{-- Configuraciones adicionales --}}
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
                        <div class="form-text">
                            El archivo se eliminará automáticamente después del tiempo seleccionado
                        </div>
                    </div>

                  
                    <div class="mb-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="password-protect">
                            <label class="form-check-label" for="password-protect">
                                Proteger con contraseña
                            </label>
                        </div>
                        <div id="password-field" class="mt-2 d-none">
                            <input type="password" class="form-control" placeholder="Ingresa contraseña" 
                                   id="file_password" name="file_password">
                        </div>
                    </div>
                </div>

                {{-- Botón de envío --}}
                <button type="submit" class="btn btn-primary btn-lg w-100 py-2 upload-btn" id="submit-btn">
                    <i class="bi bi-upload me-2"></i>
                    <span class="btn-text">Subir archivo</span>
                    <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="submit-spinner">
                        <span class="visually-hidden">Subiendo...</span>
                    </div>
                </button>
            </form>
        </div>

        {{-- Resultado de la subida --}}
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
                        <i class="bi bi-plus-circle me-1"></i>Subir otro archivo
                    </button>
                    <a href="#" class="btn btn-outline-success" id="share-btn" target="_blank">
                        <i class="bi bi-eye me-1"></i>Abrir enlace
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Información adicional --}}
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
    .file-drop-area {
        border-color: #dee2e6;
        transition: all 0.3s ease;
        background: #f8f9fa;
        cursor: pointer;
    }
    
    .file-drop-area:hover {
        border-color: #4361ee;
        background: #f0f4ff;
    }
    
    .file-drop-area.dragover {
        border-color: #4361ee;
        background: #e8edff;
        transform: scale(1.02);
    }
    
    .file-input {
        display: none;
    }
    
    .file-info {
        font-weight: 500;
    }
    
    .file-info .file-name {
        color: #4361ee;
    }
    
    .file-info .file-size {
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .upload-btn:disabled {
        cursor: not-allowed;
    }
    
    .progress-container {
        transition: all 0.3s ease;
    }
    
    .info-card {
        transition: transform 0.2s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
    }
    
    .settings-card {
        border-left: 4px solid #4361ee;
    }
    
    .success-card {
        animation: fadeInUp 0.5s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('file');
        const fileDropArea = document.getElementById('file-drop-area');
        const fileInfo = document.getElementById('file-info');
        const expireDays = document.getElementById('expire_days');
        const daysDisplay = document.getElementById('days-display');
        const submitBtn = document.getElementById('submit-btn');
        const submitSpinner = document.getElementById('submit-spinner');
        const btnText = document.querySelector('.btn-text');
        const progressContainer = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const result = document.getElementById('upload-result');
        const resultLinkInput = document.getElementById('result-link-input');
        const resultTokenInput = document.getElementById('result-token-input');
        const copyLinkBtn = document.getElementById('copy-link-btn');
        const copyTokenBtn = document.getElementById('copy-token-btn');
        const newUploadBtn = document.getElementById('new-upload-btn');
        const shareBtn = document.getElementById('share-btn');
        const passwordProtect = document.getElementById('password-protect');
        const passwordField = document.getElementById('password-field');

        // Actualizar display de días
        expireDays.addEventListener('input', function() {
            daysDisplay.textContent = `${this.value} día${this.value > 1 ? 's' : ''}`;
        });

        // Manejar arrastrar y soltar
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileDropArea.classList.add('dragover');
        }

        function unhighlight() {
            fileDropArea.classList.remove('dragover');
        }

        fileDropArea.addEventListener('drop', handleDrop, false);
        fileDropArea.addEventListener('click', () => fileInput.click());

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                const maxSize = parseInt(fileInput.getAttribute('data-max-size'));
                
                if (file.size > maxSize) {
                    showAlert('El archivo es demasiado grande. Tamaño máximo: 100MB', 'danger');
                    resetFileInput();
                    return;
                }
                
                const fileSize = formatFileSize(file.size);
                fileInfo.innerHTML = `
                    <div class="file-name"><i class="bi bi-file-earmark me-1"></i>${file.name}</div>
                    <div class="file-size">${fileSize}</div>
                `;
                
                submitBtn.disabled = false;
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function resetFileInput() {
            fileInput.value = '';
            fileInfo.innerHTML = '';
            submitBtn.disabled = true;
        }

        // Protección con contraseña
        passwordProtect.addEventListener('change', function() {
            if (this.checked) {
                passwordField.classList.remove('d-none');
            } else {
                passwordField.classList.add('d-none');
            }
        });

        // Envío del formulario
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!fileInput.files.length) {
                showAlert('Por favor, selecciona un archivo', 'warning');
                return;
            }

            // Mostrar estado de carga
            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            btnText.textContent = 'Subiendo...';
            progressContainer.classList.remove('d-none');

            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {'Accept': 'application/json'},
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    // Mostrar resultado
                    showResult(data);
                } else {
                    throw new Error(data.message || "Hubo un error inesperado");
                }
            } catch (err) {
                console.error(err);
                showAlert(err.message || "Error de red o servidor", 'danger');
            } finally {
                // Restaurar estado del botón
                submitBtn.disabled = false;
                submitSpinner.classList.add('d-none');
                btnText.textContent = 'Subir archivo';
                progressContainer.classList.add('d-none');
                progressBar.style.width = '0%';
                progressPercentage.textContent = '0%';
            }
        });

        function showResult(data) {
            resultLinkInput.value = data.url;
            resultTokenInput.value = data.delete_token;
            shareBtn.href = data.url;
            
            result.classList.remove('d-none');
            form.reset();
            resetFileInput();
            fileInfo.innerHTML = '';
            
            // Scroll suave al resultado
            result.scrollIntoView({ behavior: 'smooth' });
        }

        // Botones de copiar
        copyLinkBtn.addEventListener('click', function() {
            copyToClipboard(resultLinkInput);
            showTempAlert('Enlace copiado al portapapeles', 'success', this);
        });

        copyTokenBtn.addEventListener('click', function() {
            copyToClipboard(resultTokenInput);
            showTempAlert('Token copiado al portapapeles', 'success', this);
        });

        function copyToClipboard(input) {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
        }

        // Nueva subida
        newUploadBtn.addEventListener('click', function() {
            result.classList.add('d-none');
            form.scrollIntoView({ behavior: 'smooth' });
        });

        // Funciones de utilidad
        function showAlert(message, type) {
            // Puedes implementar SweetAlert2 aquí o usar un sistema de notificaciones
            alert(`${type.toUpperCase()}: ${message}`);
        }

        function showTempAlert(message, type, element) {
            const originalHtml = element.innerHTML;
            element.innerHTML = `<i class="bi bi-check-lg me-1"></i>Copiado`;
            element.classList.add(`btn-${type}`);
            
            setTimeout(() => {
                element.innerHTML = originalHtml;
                element.classList.remove(`btn-${type}`);
            }, 2000);
        }

        // Inicializar
        submitBtn.disabled = true;
    });
</script>
@endpush