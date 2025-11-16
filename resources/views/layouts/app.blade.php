<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sube archivos temporalmente para compartir de forma fácil y segura">
    <title>AF Nube | @yield('title')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('image/system/logo.png') }}">

    <!-- Preload de recursos críticos -->
    <link rel="preload" href="/falcon/public/vendors/simplebar/simplebar.min.css" as="style">
    <link rel="preload" href="/falcon/public/assets/css/theme.css" as="style">
    
    <!-- Fuentes optimizadas -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Estilos -->
    {!! Html::style('/falcon/public/vendors/simplebar/simplebar.min.css') !!}
    {!! Html::style('/falcon/public/assets/css/theme-rtl.css', ['id' => 'style-rtl']) !!}
    {!! Html::style('/falcon/public/assets/css/theme.css', ['id' => 'style-default']) !!}
    {!! Html::style('/falcon/public/assets/css/user-rtl.css', ['id' => 'user-style-rtl']) !!}
    {!! Html::style('/falcon/public/assets/css/user.css', ['id' => 'user-style-default']) !!}

    <style>
        /* Estilos optimizados para mejor rendimiento */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: calc(100vh - 120px);
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .logo {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: inline-block;
            transition: var(--transition);
        }
        
        .logo:hover {
            transform: translateY(-5px);
        }
        
        .page-title {
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .page-subtitle {
            color: #6c757d;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .content-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        
        .footer {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-card {
                padding: 1.5rem;
            }
            
            .logo {
                font-size: 2.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>

    <script>
        // Configuración RTL optimizada
        document.addEventListener('DOMContentLoaded', function() {
            var isRTL = JSON.parse(localStorage.getItem('isRTL'));
            if (isRTL) {
                var linkDefault = document.getElementById('style-default');
                var userLinkDefault = document.getElementById('user-style-default');
                if (linkDefault) linkDefault.setAttribute('disabled', true);
                if (userLinkDefault) userLinkDefault.setAttribute('disabled', true);
                document.querySelector('html').setAttribute('dir', 'rtl');
            } else {
                var linkRTL = document.getElementById('style-rtl');
                var userLinkRTL = document.getElementById('user-style-rtl');
                if (linkRTL) linkRTL.setAttribute('disabled', true);
                if (userLinkRTL) userLinkRTL.setAttribute('disabled', true);
            }
        });
    </script>
    
    @stack('styles')
</head>
<body>
    <div class="container py-4 py-md-5">
        <div class="main-container">
            {{-- Header --}}
            <header class="header-section">
                <div class="logo">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                </div>
                <h1 class="page-title">AF Nube</h1>
                <p class="page-subtitle">Sube, comparte y olvida. Comparte archivos de forma temporal, fácil y segura.</p>
            </header>

            {{-- Main content --}}
            <main>
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10 col-xl-8">
                        <div class="content-card">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </main>
        </div>

        {{-- Footer --}}
        <footer >
            <div class="row g-0 justify-content-between align-items-center fs-10">
                <div class="col-12 col-sm-auto text-center text-sm-start mb-2 mb-sm-0">
                    <p class="mb-0 text-600">Copyright <span class="d-none d-sm-inline-block">| </span><br
                            class="d-sm-none" /> 2025 &copy;
                        <a href="https://www.afdeveloper.com/" class="text-decoration-none">AF</a>
                    </p>
                </div>
                <div class="col-12 col-sm-auto text-center text-sm-end">
                    <p class="mb-0 text-600">Desarrollado con&nbsp;
                        <span class="text-danger">&#10084;</span>&nbsp; por&nbsp;
                        <a href="https://www.afdeveloper.com/" class="text-decoration-none">AF Developer</a>
                    </p>
                </div>
            </div>
        </footer>
    </div>
    {!! Html::script('melody/vendors/js/vendor.bundle.base.js') !!}
    {!! Html::script('melody/vendors/js/vendor.bundle.addons.js') !!}
    {{-- Scripts con carga diferida --}}
    <script>
        // Cargar scripts no críticos después de que la página esté lista
        window.addEventListener('load', function() {
            var scripts = [
                '/falcon/public/assets/js/config.js',
                '/falcon/public/vendors/simplebar/simplebar.min.js',
                '/falcon/public/vendors/popper/popper.min.js',
                '/falcon/public/vendors/bootstrap/bootstrap.min.js',
                '/falcon/public/vendors/anchorjs/anchor.min.js',
                '/falcon/public/vendors/is/is.min.js',
                '/falcon/public/vendors/fontawesome/all.min.js',
                '/falcon/public/vendors/lodash/lodash.min.js',
                '/falcon/public/vendors/list.js/list.min.js',
                '/falcon/public/assets/js/theme.js',
                '/falcon/public/assets/js/sweetalert2.js'
            ];
            
            scripts.forEach(function(src) {
                var script = document.createElement('script');
                script.src = src;
                script.async = true;
                document.body.appendChild(script);
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>