<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'GEDEPS') - Gestion Électronique de Documents</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/modern-theme.css') }}">
    
    <!-- Corporate Colors CSS -->
    <link rel="stylesheet" href="{{ asset('css/corporate-colors.css') }}">
    
    <!-- Sophisticated Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/sophisticated-theme.css') }}">

    <!-- Menu Simple CSS -->
    <link rel="stylesheet" href="{{ asset('css/menu-simple.css') }}">

    <!-- Color Fixes CSS - Corrections de couleurs -->
    <link rel="stylesheet" href="{{ asset('css/color-fixes.css') }}">

    <!-- Icon Sizes CSS - Corrections de tailles d'icônes -->
    <link rel="stylesheet" href="{{ asset('css/icon-sizes.css') }}">

    <!-- Search Improvements CSS - Améliorations de la recherche -->
    <link rel="stylesheet" href="{{ asset('css/search-improvements.css') }}">
    
    <!-- Signatures Responsive CSS - Améliorations responsive pour les signatures -->
    <link rel="stylesheet" href="{{ asset('css/signatures-responsive.css') }}">

    <!-- Scripts -->
    <!-- Vite désactivé temporairement -->
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/notification-system.js') }}" defer></script>
    <script src="{{ asset('js/color-fixes.js') }}" defer></script>
    <script src="{{ asset('js/icon-sizes.js') }}" defer></script>
    <script src="{{ asset('js/search-improvements.js') }}" defer></script>
    <script src="{{ asset('js/signatures-responsive.js') }}" defer></script>
    
    <!-- Optimisations de performance -->
    <script>
        // Lazy loading pour les images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });

        // Optimisation des animations
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.style.setProperty('--transition-fast', '0.01ms');
            document.documentElement.style.setProperty('--transition-normal', '0.01ms');
            document.documentElement.style.setProperty('--transition-slow', '0.01ms');
        }

        // Optimisation des performances
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                // Chargement différé des ressources non critiques
                const nonCriticalResources = document.querySelectorAll('[data-lazy]');
                nonCriticalResources.forEach(resource => {
                    if (resource.dataset.lazy === 'css') {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = resource.href;
                        document.head.appendChild(link);
                    }
                });
            });
        }
    </script>
    
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <!-- Skip links pour l'accessibilité -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>
    <a href="#navigation" class="skip-link">Aller à la navigation</a>
    
    <div class="min-h-screen" style="background: var(--bg-secondary);">
        <!-- Navigation moderne responsive -->
        <nav class="sophisticated-nav sticky top-0 z-50" id="navigation" role="navigation" aria-label="Navigation principale">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-4 lg:gap-6">
                        <!-- Logo EPS.SARL -->
                        <a href="{{ url('/') }}" class="navbar-brand">
                            <img src="{{ asset('images/logo-eps-sarl.svg') }}" alt="EPS.SARL" class="h-8 w-auto lg:h-10">
                        </a>

                        <!-- Menu hamburger pour mobile -->
                        <button class="lg:hidden sophisticated-nav-link" id="mobileMenuToggle" aria-label="Menu principal">
                            <i class="fas fa-bars text-lg"></i>
                            <span class="font-medium">Menu</span>
                        </button>

                        <!-- Navigation Links -->
                        <div class="hidden lg:flex items-center space-x-2" id="navbarNav">
                            @auth
                                @if(!auth()->user()->isAdmin())
                                    <a href="{{ route('documents.upload') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('documents.upload') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-upload"></i>
                                        <span>Soumettre</span>
                                    </a>

                                    <a href="{{ route('signatures.index') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('signatures.index') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-pen-fancy"></i>
                                        <span>Signatures Simples</span>
                                    </a>
                                    
                                    <a href="{{ route('signatures.simple.index') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('signatures.simple.*') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-list-ol"></i>
                                        <span>Signatures Séquentielles</span>
                                    </a>
                                @endif

                                <a href="{{ route('documents.pending') }}" 
                                   class="sophisticated-nav-link {{ request()->routeIs('documents.pending') ? 'sophisticated-nav-link-active' : '' }}">
                                    <i class="fas fa-clock"></i>
                                    <span>En Attente</span>
                                </a>

                                <a href="{{ route('documents.history') }}" 
                                   class="sophisticated-nav-link {{ request()->routeIs('documents.history') ? 'sophisticated-nav-link-active' : '' }}">
                                    <i class="fas fa-history"></i>
                                    <span>Historique</span>
                                </a>

                                <a href="{{ route('profile.index') }}" 
                                   class="sophisticated-nav-link {{ request()->routeIs('profile.*') ? 'sophisticated-nav-link-active' : '' }}">
                                    <i class="fas fa-user"></i>
                                    <span>Profil</span>
                                </a>

                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('admin.*') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-cog"></i>
                                        <span>Administration</span>
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        @auth
                            <!-- User info - masqué sur mobile -->
                            <div class="hidden md:flex items-center gap-3">
                                <div class="flex items-center gap-2 user-info">
                                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                                        <span class="text-xs text-white text-opacity-80">
                                            @if(auth()->user()->isAdmin())
                                                <i class="fas fa-crown mr-1"></i>Administrateur
                                            @elseif(auth()->user()->isSignataire())
                                                <i class="fas fa-pen-fancy mr-1"></i>Signataire
                                            @else
                                                <i class="fas fa-user mr-1"></i>Agent
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bouton de déconnexion -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="sophisticated-nav-link">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="hidden sm:inline">Déconnexion</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="sophisticated-btn-secondary">
                                <i class="fas fa-sign-in-alt"></i>
                                <span class="hidden sm:inline">Connexion</span>
                            </a>
                        @endauth
                    </div>
                </div>
                
                <!-- Menu mobile amélioré -->
                <div class="lg:hidden fixed inset-0 z-50 hidden" id="navbarNavMobile">
                    <!-- Overlay -->
                    <div class="fixed inset-0 bg-black bg-opacity-50" id="mobileMenuOverlay"></div>
                    
                    <!-- Menu Panel -->
                    <div class="fixed top-0 left-0 h-full w-80 max-w-[80vw] shadow-lg">
                        @auth
                            <!-- Header mobile avec bouton fermer -->
                            <div class="flex items-center justify-between p-4 border-b border-white border-opacity-20">
                                <div class="flex items-center gap-3 user-info">
                                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'Utilisateur' }}</div>
                                        <div class="text-xs text-blue-100">
                                            @if(auth()->user()->isAdmin())
                                                <i class="fas fa-crown mr-1"></i>Administrateur
                                            @elseif(auth()->user()->isSignataire())
                                                <i class="fas fa-pen-fancy mr-1"></i>Signataire
                                            @else
                                                <i class="fas fa-user mr-1"></i>Agent
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button class="sophisticated-nav-link" id="mobileMenuClose" aria-label="Fermer le menu">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>
                        
                        <!-- Navigation mobile organisée -->
                        <div class="flex-1 overflow-y-auto">
                            <!-- Section Actions Principales -->
                            <div class="p-4">
                                <h3 class="text-xs font-semibold text-white text-opacity-80 uppercase tracking-wider mb-3">Actions Principales</h3>
                                <div class="space-y-1">
                                    @if(!auth()->user()->isAdmin())
                                        <a href="{{ route('documents.upload') }}" 
                                           class="sophisticated-nav-link {{ request()->routeIs('documents.upload') ? 'sophisticated-nav-link-active' : '' }}">
                                            <i class="fas fa-upload text-white"></i>
                                            <span>Soumettre Document</span>
                                            <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                        </a>

                                        <a href="{{ route('signatures.index') }}" 
                                           class="sophisticated-nav-link {{ request()->routeIs('signatures.index') ? 'sophisticated-nav-link-active' : '' }}">
                                            <i class="fas fa-pen-fancy text-white"></i>
                                            <span>Signatures Simples</span>
                                            <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                        </a>
                                        
                                        <a href="{{ route('signatures.simple.index') }}" 
                                           class="sophisticated-nav-link {{ request()->routeIs('signatures.simple.*') ? 'sophisticated-nav-link-active' : '' }}">
                                            <i class="fas fa-list-ol text-white"></i>
                                            <span>Signatures Séquentielles</span>
                                            <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Section Navigation -->
                            <div class="p-4 border-t border-white border-opacity-20">
                                <h3 class="text-xs font-semibold text-white text-opacity-80 uppercase tracking-wider mb-3">Navigation</h3>
                                <div class="space-y-1">
                                    <a href="{{ route('documents.pending') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('documents.pending') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-clock text-white"></i>
                                        <span>En Attente</span>
                                        <span class="ml-auto sophisticated-badge">{{ $stats['pending_documents'] ?? 0 }}</span>
                                    </a>

                                    <a href="{{ route('documents.history') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('documents.history') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-history text-white"></i>
                                        <span>Historique</span>
                                        <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                    </a>

                                    <a href="{{ route('profile.index') }}" 
                                       class="sophisticated-nav-link {{ request()->routeIs('profile.*') ? 'sophisticated-nav-link-active' : '' }}">
                                        <i class="fas fa-user text-white"></i>
                                        <span>Mon Profil</span>
                                        <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" 
                                           class="sophisticated-nav-link {{ request()->routeIs('admin.*') ? 'sophisticated-nav-link-active' : '' }}">
                                            <i class="fas fa-cog text-white"></i>
                                            <span>Administration</span>
                                            <i class="fas fa-chevron-right ml-auto text-blue-200"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="fixed top-4 right-4 sophisticated-primary px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 sophisticated-animate" id="flash-message">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-white">{{ session('success') }}</span>
                <button onclick="document.getElementById('flash-message').remove()" class="ml-2 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="fixed top-4 right-4 sophisticated-alert-error px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 sophisticated-animate" id="flash-message">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-white">{{ session('error') }}</span>
                <button onclick="document.getElementById('flash-message').remove()" class="ml-2 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Page Content -->
        <main id="main-content" role="main" aria-label="Contenu principal" class="min-h-screen bg-blue-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Auto-hide flash messages -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.style.transition = 'opacity 0.5s ease-out';
                flashMessage.style.opacity = '0';
                setTimeout(() => flashMessage.remove(), 500);
            }
        }, 5000);

        // Loading indicator for forms - Éviter les conflits
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si le module n'est pas déjà initialisé
            if (window.formHandlersInitialized) {
                return;
            }
            window.formHandlersInitialized = true;
            
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Éviter la double soumission
                    if (this.dataset.submitting === 'true') {
                        e.preventDefault();
                        return false;
                    }
                    
                    this.dataset.submitting = 'true';
                    const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        const originalText = submitBtn.textContent || submitBtn.value;
                        submitBtn.textContent = 'Chargement...';
                        submitBtn.value = 'Chargement...';
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                            submitBtn.value = originalText;
                            this.dataset.submitting = 'false';
                        }, 10000);
                    }
                });
            });
            
            // Protection contre les double-clics sur les liens (exclure les inputs file)
            const links = document.querySelectorAll('a:not([href^="javascript:"])');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.dataset.clicked === 'true') {
                        e.preventDefault();
                        return false;
                    }
                    this.dataset.clicked = 'true';
                    
                    // Réinitialiser après 1 seconde
                    setTimeout(() => {
                        this.dataset.clicked = 'false';
                    }, 1000);
                });
            });
            
            // Protection spéciale pour les zones de téléchargement
            const uploadZones = document.querySelectorAll('.upload-zone, [data-upload-zone]');
            uploadZones.forEach(zone => {
                zone.addEventListener('click', function(e) {
                    // Ne pas bloquer les clics sur les zones de téléchargement
                    const fileInput = this.querySelector('input[type="file"]');
                    if (fileInput && !fileInput.disabled) {
                        fileInput.click();
                    }
                });
            });
        });
    </script>
    
    <style>
        /* Styles pour le logo EPS.SARL amélioré */
        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .logo-link:hover {
            transform: scale(1.02);
        }
        
        .logo-image {
            height: 45px;
            width: auto;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
            transition: all 0.3s ease;
        }
        
        .logo-link:hover .logo-image {
            filter: drop-shadow(0 2px 4px rgba(30, 64, 175, 0.2));
        }
        
        
        /* Navigation avec fond blanc et boutons bleus */
        .modern-nav {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-bottom: 2px solid var(--eps-light-blue, #eff6ff);
        }
        
        .nav-link {
            color: var(--eps-primary, #1e40af) !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            background: transparent;
            border: 2px solid transparent;
        }
        
        .nav-link:hover {
            background: var(--eps-bg-accent, #eff6ff);
            color: var(--eps-primary, #1e40af) !important;
            border-color: var(--eps-secondary, #2563eb);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.15);
        }
        
        .nav-link.active {
            background: var(--eps-secondary, #2563eb);
            color: white !important;
            border-color: var(--eps-primary, #1e40af);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        
        /* Boutons de déconnexion */
        .btn-logout {
            background: var(--eps-accent, #dc2626);
            color: white;
            border: 2px solid var(--eps-accent, #dc2626);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #b91c1c;
            border-color: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }
        
        /* Navigation mobile améliorée */
        .navbar-nav-mobile {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-top: 1px solid #e2e8f0;
            padding: 1rem;
            z-index: 50;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
        
        .mobile-user-info {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }
        
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-blue-700);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            border: 2px solid transparent;
        }
        
        .mobile-nav-link:hover {
            background: var(--bg-blue-50);
            color: var(--primary);
            border-color: var(--primary);
            transform: translateX(4px);
        }
        
        .mobile-nav-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary-dark);
        }
        
        .mobile-nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Menu hamburger amélioré */
        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            border-radius: 8px;
            color: var(--text-blue-700);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.2rem;
        }
        
        .mobile-menu-toggle:hover {
            background: var(--bg-blue-100);
            color: var(--primary);
        }
        
        .mobile-menu-toggle:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        
        /* Responsive pour le logo */
        @media (max-width: 768px) {
            .logo-image {
                height: 38px;
            }
        }
        
        @media (max-width: 480px) {
            .logo-image {
                height: 32px;
            }
        }
        
        /* Amélioration des flash messages sur mobile */
        @media (max-width: 640px) {
            .fixed.top-4.right-4 {
                top: 1rem;
                right: 1rem;
                left: 1rem;
                padding: 1rem;
                font-size: 0.875rem;
            }
        }
    </style>

    <!-- JavaScript pour le menu mobile amélioré -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const navbarNavMobile = document.getElementById('navbarNavMobile');
            
            if (mobileMenuToggle && navbarNavMobile) {
                // Ouvrir le menu
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    navbarNavMobile.classList.remove('hidden');
                    navbarNavMobile.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
                
                // Fermer le menu avec le bouton fermer
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeMobileMenu();
                    });
                }
                
                // Fermer le menu en cliquant à l'extérieur
                document.addEventListener('click', function(event) {
                    if (!navbarNavMobile.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                        closeMobileMenu();
                    }
                });
                
                // Fermer le menu en cliquant sur un lien mobile
                const mobileNavLinks = navbarNavMobile.querySelectorAll('.mobile-nav-link');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        closeMobileMenu();
                    });
                });
                
                // Fermer le menu avec Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !navbarNavMobile.classList.contains('hidden')) {
                        closeMobileMenu();
                    }
                });
                
                // Gestion du redimensionnement de la fenêtre
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024) { // lg breakpoint
                        closeMobileMenu();
                    }
                });
            }
            
            function closeMobileMenu() {
                if (navbarNavMobile) {
                    navbarNavMobile.classList.remove('show');
                    setTimeout(() => {
                        navbarNavMobile.classList.add('hidden');
                    }, 300);
                    document.body.style.overflow = '';
                }
            }
        });
    </script>
    
    <!-- Toast Notifications System -->
    <script src="{{ asset('js/toast-notifications.js') }}"></script>
</body>
</html>
