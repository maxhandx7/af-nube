@extends('layouts.app')

@section('title', 'Archivo Protegido - ' . $file->original_name)

@section('content')

            <div class="card shadow-lg border-0 password-card">
                <div class="card-header bg-warning text-dark py-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="lock-icon-wrapper">
                                <i class="bi bi-shield-lock display-4"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="h4 mb-2">Archivo Protegido</h1>
                            <p class="mb-0 text-dark">Este archivo está protegido con contraseña</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Información del archivo --}}
                    <div class="file-info mb-4 p-3 bg-light rounded-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="bi {{ $file->getFileIcon($file->mime, $file->original_name) }} fs-2 text-primary"></i>
                            </div>
                            <div class="col">
                                <h5 class="mb-1">{{ $file->original_name }}</h5>
                                <div class="file-meta small text-muted">
                                    <span class="me-3">
                                        <i class="bi bi-hdd me-1"></i>{{ $file->formatFileSize($file->size) }}
                                    </span>
                                    <span>
                                        <i class="bi bi-clock me-1"></i>Expira {{ $file->expires_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario de contraseña --}}
                    <form action="{{ route('files.validate-password', $file->slug) }}" method="POST" id="password-form">
                        @csrf
                        
                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(session('attempts') && session('attempts') > 0)
                            <div class="alert alert-warning">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                Intentos fallidos: {{ session('attempts') }} de 5

                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-key me-1"></i>Contraseña del archivo
                            </label>
                            
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control form-control-lg"
                                       placeholder="Ingresa la contraseña"
                                       required
                                       autofocus
                                       autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary" id="toggle-password">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Contacta al propietario del archivo si no conoces la contraseña
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg py-2" id="submit-btn">
                                <i class="bi bi-unlock me-2"></i>
                                <span class="btn-text">Desbloquear archivo</span>
                                <div class="spinner-border spinner-border-sm ms-2 d-none" id="submit-spinner">
                                    <span class="visually-hidden">Verificando...</span>
                                </div>
                            </button>
                        </div>
                    </form>

                    {{-- Información adicional --}}
                    <div class="mt-4 pt-3 border-top">
                        <div class="row text-center g-3">
                            <div class="col-6">
                                <div class="security-info">
                                    <i class="bi bi-shield-check text-success fs-4 mb-2"></i>
                                    <h6 class="fw-semibold mb-1">Seguro</h6>
                                    <small class="text-muted">Protección adicional</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="security-info">
                                    <i class="bi bi-clock text-warning fs-4 mb-2"></i>
                                    <h6 class="fw-semibold mb-1">Temporal</h6>
                                    <small class="text-muted">Expira automáticamente</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enlace de regreso --}}
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver al inicio
                </a>
            </div>

@endsection

@push('styles')
<style>
    .password-card {
        border: none;
        border-radius: 1rem;
    }
    
    .lock-icon-wrapper {
        background: rgba(255, 193, 7, 0.2);
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .file-info {
        border-left: 4px solid #4361ee;
    }
    
    .security-info {
        transition: transform 0.2s ease;
    }
    
    .security-info:hover {
        transform: translateY(-2px);
    }
    
    #submit-btn:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1.1rem;
    }
    
    @media (max-width: 576px) {
        .lock-icon-wrapper {
            width: 60px;
            height: 60px;
        }
        
        .lock-icon-wrapper .display-4 {
            font-size: 2rem;
        }
    }
</style>
@endpush

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordForm = document.getElementById('password-form');
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('toggle-password');
        const passwordIcon = document.getElementById('password-icon');
        const submitBtn = document.getElementById('submit-btn');
        const submitSpinner = document.getElementById('submit-spinner');
        const btnText = document.querySelector('.btn-text');
        
        let isPasswordVisible = false;

        // Alternar visibilidad de contraseña
        togglePasswordBtn.addEventListener('click', function() {
            isPasswordVisible = !isPasswordVisible;
            passwordInput.type = isPasswordVisible ? 'text' : 'password';
            passwordIcon.className = isPasswordVisible ? 'bi bi-eye-slash' : 'bi bi-eye';
            togglePasswordBtn.classList.toggle('btn-primary', isPasswordVisible);
            togglePasswordBtn.classList.toggle('btn-outline-secondary', !isPasswordVisible);
        });

        // Validación del formulario
        passwordForm.addEventListener('submit', function(e) {
            if (!passwordInput.value.trim()) {
                e.preventDefault();
                showAlert('Por favor, ingresa la contraseña', 'warning');
                passwordInput.focus();
                return;
            }

            // Mostrar estado de carga
            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            btnText.textContent = 'Verificando...';
        });

        // Auto-enfocar el campo de contraseña
        passwordInput.focus();

        // Mostrar alertas temporales
        function showAlert(message, type) {
            // Crear alerta temporal
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
            alertDiv.innerHTML = `
                <i class="bi bi-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            passwordForm.insertBefore(alertDiv, passwordForm.firstChild);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Limpiar mensajes de error al empezar a escribir
        passwordInput.addEventListener('input', function() {
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                errorAlert.remove();
            }
            
            const warningAlert = document.querySelector('.alert-warning');
            if (warningAlert && warningAlert.textContent.includes('Intentos fallidos')) {
                warningAlert.remove();
            }
        });

        // Prevenir envío con Enter sin contraseña
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !this.value.trim()) {
                e.preventDefault();
                showAlert('Por favor, ingresa la contraseña primero', 'warning');
            }
        });
    });
</script>
@endsection