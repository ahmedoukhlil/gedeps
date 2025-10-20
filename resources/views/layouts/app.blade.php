<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'GEDEPS') - Gestion Électronique de Documents</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles - Tailwind CSS uniquement -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/notification-system.js') }}" defer></script>
    <script src="{{ asset('js/search-improvements.js') }}" defer></script>
    <script src="{{ asset('js/signatures-responsive.js') }}" defer></script>
    <script src="{{ asset('js/icon-sizes.js') }}" defer></script>
    
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
<body class="font-sans antialiased bg-gray-50">
    <!-- Skip links pour l'accessibilité -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">Aller au contenu principal</a>
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation élégante et moderne -->
        <nav class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-elegant backdrop-blur-sm bg-opacity-95" id="navigation" role="navigation" aria-label="Navigation principale">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
                <div class="flex items-center justify-between h-14 sm:h-16 md:h-18">
                    <div class="flex items-center gap-2 sm:gap-4 lg:gap-6">
                        <!-- Logo élégant avec badge -->
                        <a href="{{ url('/') }}" class="group flex items-center gap-2 sm:gap-3 flex-shrink-0 transition-transform duration-300 hover:scale-105">
                            <div class="relative">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-glow transition-all duration-300">
                                    <img src="{{ asset('images/logo-eps-sarl.svg') }}" alt="Logo" class="h-5 sm:h-6 lg:h-7 w-auto filter brightness-0 invert">
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-success-500 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="hidden sm:block">
                                <div class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 group-hover:text-primary-600 transition-colors leading-tight">GEDEPS</div>
                                <div class="text-xs text-gray-500 -mt-0.5 leading-tight">Gestion Documentaire</div>
                            </div>
                        </a>

                        <!-- Menu hamburger pour mobile - Amélioré -->
                        <button class="lg:hidden nav-link flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 rounded-md text-sm sm:text-base transition-all duration-200 hover:scale-105" id="mobileMenuToggle" aria-label="Menu principal" aria-expanded="false">
                            <i class="fas fa-bars text-base sm:text-lg"></i>
                            <span class="font-medium text-xs sm:text-sm">Menu</span>
                        </button>

                        <!-- Navigation Links - Élégante -->
                        <div class="hidden lg:flex items-center gap-1 xl:gap-2" id="navbarNav">
                            @auth
                                @if(!auth()->user()->isAdmin())
                                    <a href="{{ route('documents.upload') }}" 
                                       class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-primary-50 {{ request()->routeIs('documents.upload') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">
                                        <i class="fas fa-upload text-sm transition-transform duration-300 group-hover:scale-110"></i>
                                        <span>Soumettre</span>
                                        @if(request()->routeIs('documents.upload'))
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full"></div>
                                        @endif
                                    </a>

                                    <a href="{{ route('signatures.index') }}" 
                                       class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-primary-50 {{ request()->routeIs('signatures.index') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">
                                        <i class="fas fa-pen-fancy text-sm transition-transform duration-300 group-hover:scale-110"></i>
                                        <span>Signatures Simples</span>
                                        @if(request()->routeIs('signatures.index'))
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full"></div>
                                        @endif
                                    </a>
                                    
                                    <a href="{{ route('signatures.simple.index') }}" 
                                       class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-primary-50 {{ request()->routeIs('signatures.simple.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">
                                        <i class="fas fa-list-ol text-sm transition-transform duration-300 group-hover:scale-110"></i>
                                        <span>Signatures Séquentielles</span>
                                        @if(request()->routeIs('signatures.simple.*'))
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full"></div>
                                        @endif
                                    </a>
                                @endif

                                <a href="{{ route('documents.pending') }}" 
                                   class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-primary-50 {{ request()->routeIs('documents.pending') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">
                                    <i class="fas fa-clock text-sm transition-transform duration-300 group-hover:scale-110"></i>
                                    <span>En Attente</span>
                                    @if(request()->routeIs('documents.pending'))
                                        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full"></div>
                                    @endif
                                </a>

                                <a href="{{ route('documents.history') }}" 
                                   class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-primary-50 {{ request()->routeIs('documents.history') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-primary-600' }}">
                                    <i class="fas fa-history text-sm transition-transform duration-300 group-hover:scale-110"></i>
                                    <span>Historique</span>
                                    @if(request()->routeIs('documents.history'))
                                        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full"></div>
                                    @endif
                                </a>


                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="group relative flex items-center gap-2 px-3 xl:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-warning-50 {{ request()->routeIs('admin.*') ? 'text-warning-600 bg-warning-50' : 'text-gray-700 hover:text-warning-600' }}">
                                        <i class="fas fa-crown text-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"></i>
                                        <span>Administration</span>
                                        @if(request()->routeIs('admin.*'))
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-gradient-to-r from-warning-400 to-warning-600 rounded-full"></div>
                                        @endif
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- User Menu - Élégant -->
                    <div class="flex items-center gap-2">
                        @auth
                            <!-- Bouton Profil avec informations utilisateur -->
                            <a href="{{ route('profile.index') }}" 
                               class="group flex items-center gap-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-primary-50 hover:to-primary-100 rounded-xl border border-gray-200 hover:border-primary-300 shadow-sm hover:shadow-md transition-all duration-300 {{ request()->routeIs('profile.*') ? 'border-primary-400 bg-gradient-to-r from-primary-50 to-primary-100' : '' }}">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-300">
                                    <span class="text-white text-xs sm:text-sm font-bold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                                </div>
                                <div class="hidden sm:flex flex-col">
                                    <span class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-primary-600 truncate max-w-[120px] lg:max-w-[150px] transition-colors">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                                    <span class="text-[10px] sm:text-xs text-gray-600 group-hover:text-primary-500 flex items-center gap-1 transition-colors">
                                        @if(auth()->user()->isAdmin())
                                            <i class="fas fa-crown text-warning-500"></i>
                                            <span>Administrateur</span>
                                        @elseif(auth()->user()->isSignataire())
                                            <i class="fas fa-pen-fancy text-primary-500"></i>
                                            <span>Signataire</span>
                                        @else
                                            <i class="fas fa-user text-info-500"></i>
                                            <span>Agent</span>
                                        @endif
                                    </span>
                                </div>
                                <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-primary-600 group-hover:translate-x-0.5 transition-all duration-300 hidden lg:block"></i>
                            </a>
                            
                            <!-- Bouton de déconnexion - Élégant -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="group flex items-center gap-2 px-3 sm:px-4 py-2 bg-danger-500 hover:bg-danger-600 text-white rounded-lg text-xs sm:text-sm font-medium shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                                    <i class="fas fa-sign-out-alt text-sm transition-transform duration-300 group-hover:translate-x-1"></i>
                                    <span class="hidden sm:inline">Déconnexion</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="group flex items-center gap-2 px-3 sm:px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-lg text-xs sm:text-sm font-medium shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                                <i class="fas fa-sign-in-alt text-sm transition-transform duration-300 group-hover:translate-x-1"></i>
                                <span class="hidden sm:inline">Connexion</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Menu mobile élégant - Slide depuis la gauche - SORTI DE LA NAVBAR -->
        <div class="lg:hidden fixed inset-0 hidden" style="z-index: 99999 !important;" id="navbarNavMobile">
            <!-- Overlay avec animation -->
            <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity duration-300 opacity-0" style="z-index: 99998 !important;" id="mobileMenuOverlay"></div>

            <!-- Menu Panel avec slide animation -->
            <div class="fixed top-0 left-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out overflow-y-auto" style="z-index: 99999 !important;" id="mobileMenuPanel">
                @auth
                    <!-- Header mobile élégant -->
                    <div class="sticky top-0 bg-gradient-to-r from-primary-500 to-primary-600 p-6 shadow-lg z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg ring-2 ring-white ring-opacity-50">
                                    <span class="text-white text-lg font-bold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ Auth::user()->name ?? 'Utilisateur' }}</div>
                                    <div class="text-xs text-white text-opacity-90 flex items-center gap-1.5">
                                        @if(auth()->user()->isAdmin())
                                            <i class="fas fa-crown"></i>
                                            <span>Administrateur</span>
                                        @elseif(auth()->user()->isSignataire())
                                            <i class="fas fa-pen-fancy"></i>
                                            <span>Signataire</span>
                                        @else
                                            <i class="fas fa-user"></i>
                                            <span>Agent</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg transition-all duration-200" id="mobileMenuClose" aria-label="Fermer le menu">
                                <i class="fas fa-times text-white text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Navigation mobile organisée -->
                    <div class="p-4 space-y-6">
                        <!-- Section Actions Principales -->
                        @if(!auth()->user()->isAdmin())
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider px-3 mb-3 flex items-center gap-2">
                                <i class="fas fa-bolt text-primary-500"></i>
                                <span>Actions Rapides</span>
                            </h3>
                            <a href="{{ route('documents.upload') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('documents.upload') ? 'bg-gradient-to-r from-success-50 to-success-100 text-success-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('documents.upload') ? 'bg-success-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-success-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <span class="font-medium flex-1">Soumettre</span>
                                @if(request()->routeIs('documents.upload'))
                                    <i class="fas fa-check-circle text-success-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>

                            <a href="{{ route('signatures.index') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('signatures.index') ? 'bg-gradient-to-r from-primary-50 to-primary-100 text-primary-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('signatures.index') ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-primary-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-pen-fancy"></i>
                                </div>
                                <span class="font-medium flex-1">Signatures Simples</span>
                                @if(request()->routeIs('signatures.index'))
                                    <i class="fas fa-check-circle text-primary-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>

                            <a href="{{ route('signatures.simple.index') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('signatures.simple.*') ? 'bg-gradient-to-r from-primary-50 to-primary-100 text-primary-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('signatures.simple.*') ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-primary-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-list-ol"></i>
                                </div>
                                <span class="font-medium flex-1">Signatures Séquentielles</span>
                                @if(request()->routeIs('signatures.simple.*'))
                                    <i class="fas fa-check-circle text-primary-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>
                        </div>
                        @endif

                        <!-- Section Navigation -->
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider px-3 mb-3 flex items-center gap-2">
                                <i class="fas fa-compass text-info-500"></i>
                                <span>Navigation</span>
                            </h3>
                            <a href="{{ route('documents.pending') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('documents.pending') ? 'bg-gradient-to-r from-warning-50 to-warning-100 text-warning-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('documents.pending') ? 'bg-warning-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-warning-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="font-medium flex-1">En Attente</span>
                                @if(request()->routeIs('documents.pending'))
                                    <i class="fas fa-check-circle text-warning-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>

                            <a href="{{ route('documents.history') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('documents.history') ? 'bg-gradient-to-r from-info-50 to-info-100 text-info-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('documents.history') ? 'bg-info-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-info-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-history"></i>
                                </div>
                                <span class="font-medium flex-1">Historique</span>
                                @if(request()->routeIs('documents.history'))
                                    <i class="fas fa-check-circle text-info-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>

                            <a href="{{ route('profile.index') }}"
                               class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-secondary-50 to-secondary-100 text-secondary-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('profile.*') ? 'bg-secondary-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-secondary-500 group-hover:text-white' }} transition-all duration-200">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="font-medium flex-1">Mon Profil</span>
                                @if(request()->routeIs('profile.*'))
                                    <i class="fas fa-check-circle text-secondary-500"></i>
                                @else
                                    <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                @endif
                            </a>

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}"
                                   class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-warning-50 to-warning-100 text-warning-700' : 'hover:bg-gray-50 text-gray-700' }}">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.*') ? 'bg-warning-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-warning-500 group-hover:text-white' }} transition-all duration-200">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <span class="font-medium flex-1">Administration</span>
                                    @if(request()->routeIs('admin.*'))
                                        <i class="fas fa-check-circle text-warning-500"></i>
                                    @else
                                        <i class="fas fa-chevron-right text-gray-400 group-hover:translate-x-1 transition-transform"></i>
                                    @endif
                                </a>
                            @endif
                        </div>

                        <!-- Section Déconnexion -->
                        <div class="pt-4 border-t border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="group w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-danger-50 text-gray-700 hover:text-danger-600 transition-all duration-200">
                                    <div class="w-10 h-10 flex items-center justify-center bg-danger-100 rounded-lg group-hover:bg-danger-500 transition-all duration-200">
                                        <i class="fas fa-sign-out-alt text-danger-600 group-hover:text-white"></i>
                                    </div>
                                    <span class="font-medium flex-1 text-left">Déconnexion</span>
                                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Flash Messages - Hidden elements for Tailwind notifications -->
        @if(session('success'))
            <div data-session-message data-session-type="success" data-session-title="Succès" class="hidden">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div data-session-message data-session-type="error" data-session-title="Erreur" class="hidden">{{ session('error') }}</div>
        @endif

        @if(session('warning'))
            <div data-session-message data-session-type="warning" data-session-title="Attention" class="hidden">{{ session('warning') }}</div>
        @endif

        @if(session('info'))
            <div data-session-message data-session-type="info" data-session-title="Information" class="hidden">{{ session('info') }}</div>
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

    <!-- Form handlers -->
    <script>
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
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            const mobileMenuPanel = document.getElementById('mobileMenuPanel');

            console.log('🔍 Éléments menu mobile:', {
                mobileMenuToggle: !!mobileMenuToggle,
                mobileMenuClose: !!mobileMenuClose,
                navbarNavMobile: !!navbarNavMobile,
                mobileMenuOverlay: !!mobileMenuOverlay,
                mobileMenuPanel: !!mobileMenuPanel
            });
            
            // Fonction pour ouvrir le menu
            function openMenu() {
                console.log('📱 Ouverture du menu mobile');
                navbarNavMobile.classList.remove('hidden');
                navbarNavMobile.style.display = 'block';

                // Délai pour permettre la transition
                setTimeout(() => {
                    mobileMenuOverlay.classList.remove('opacity-0');
                    mobileMenuOverlay.classList.add('opacity-100');
                    mobileMenuPanel.classList.remove('-translate-x-full');
                    mobileMenuPanel.classList.add('translate-x-0');
                    // Forcer le transform avec inline style
                    mobileMenuPanel.style.transform = 'translateX(0)';

                    console.log('📏 Largeur du panel:', mobileMenuPanel.offsetWidth + 'px');
                    console.log('📐 Viewport width:', window.innerWidth + 'px');
                }, 10);

                // Empêcher le scroll du body
                document.body.style.overflow = 'hidden';
                console.log('✅ Menu mobile ouvert');
            }
            
            // Fonction pour fermer le menu
            function closeMenu() {
                console.log('🚪 Fermeture du menu mobile');
                mobileMenuOverlay.classList.remove('opacity-100');
                mobileMenuOverlay.classList.add('opacity-0');
                mobileMenuPanel.classList.remove('translate-x-0');
                mobileMenuPanel.classList.add('-translate-x-full');
                // Forcer le transform avec inline style
                mobileMenuPanel.style.transform = 'translateX(-100%)';

                // Attendre la fin de l'animation avant de cacher
                setTimeout(() => {
                    navbarNavMobile.classList.add('hidden');
                    navbarNavMobile.style.display = 'none';
                }, 300);

                // Réactiver le scroll du body
                document.body.style.overflow = '';
                console.log('✅ Menu mobile fermé');
            }
            
            if (mobileMenuToggle && navbarNavMobile) {
                // Ouvrir le menu
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openMenu();
                    document.body.style.overflow = 'hidden';
                });
                
                // Fermer le menu avec le bouton fermer
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeMenu();
                    });
                }
                
                // Fermer le menu en cliquant sur l'overlay
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', function() {
                        closeMenu();
                    });
                }
                
                // Fermer le menu en cliquant sur un lien mobile
                const mobileNavLinks = navbarNavMobile.querySelectorAll('a');
                mobileNavLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        closeMenu();
                    });
                });
                
                // Fermer le menu avec Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !navbarNavMobile.classList.contains('hidden')) {
                        closeMenu();
                    }
                });
                
                // Gestion du redimensionnement de la fenêtre
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024) { // lg breakpoint
                        closeMenu();
                    }
                });
            }
        });
    </script>
    
    <!-- Tailwind Notifications System -->
    <script src="{{ asset('js/tailwind-notifications.js') }}"></script>
</body>
</html>
