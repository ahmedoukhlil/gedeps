@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-blue-50 to-primary-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Bulles décoratives d'arrière-plan -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full blur-3xl opacity-20 -mr-48 -mt-48 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full blur-3xl opacity-20 -ml-40 -mb-40 animate-pulse" style="animation-delay: 1s;"></div>
    <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-10 -translate-x-1/2 -translate-y-1/2 animate-pulse" style="animation-delay: 2s;"></div>
    
    <div class="max-w-md w-full relative z-10">
        <!-- Logo et titre élégants -->
        <div class="text-center mb-8">
            <div class="relative inline-block">
                <!-- Cercle de fond animé -->
                <div class="absolute inset-0 bg-gradient-to-br from-primary-400 to-primary-600 rounded-3xl blur-xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
                
                <!-- Logo container -->
                <div class="relative mx-auto h-20 w-20 sm:h-24 sm:w-24 bg-gradient-to-br from-primary-500 to-primary-700 rounded-3xl flex items-center justify-center mb-4 shadow-2xl transform hover:scale-110 transition-all duration-300">
                    <img src="{{ asset('images/logo-eps-sarl.svg') }}" alt="EPS.SARL" class="h-10 sm:h-12 w-auto filter brightness-0 invert">
                    <!-- Badge animé -->
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-success-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                </div>
            </div>
            
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2 flex items-center justify-center gap-2">
                <i class="fas fa-sparkles text-primary-500 text-2xl sm:text-3xl"></i>
                <span>Connexion</span>
            </h2>
        </div>

        <!-- Formulaire de connexion élégant -->
        <div class="card card-hover overflow-hidden relative group">
            <!-- Fond décoratif -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full blur-3xl opacity-10 -mr-32 -mt-32 group-hover:opacity-20 transition-opacity duration-500"></div>
            
            <div class="relative p-6 sm:p-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email -->
                    <div class="group/input">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center group-hover/input:bg-blue-200 transition-colors">
                                    <i class="fas fa-envelope text-blue-600 text-xs"></i>
                                </div>
                                <span>Adresse email</span>
                                <span class="text-danger-500 text-xs">*</span>
                            </div>
                        </label>
                        <div class="relative">
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   autocomplete="email" 
                                   required 
                                   class="input-elegant pl-10 @error('email') border-danger-500 focus:ring-danger-500 focus:border-danger-500 @enderror"
                                   placeholder="exemple@email.com"
                                   value="{{ old('email') }}">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-envelope text-sm"></i>
                            </div>
                        </div>
                        @error('email')
                            <div class="mt-2 flex items-center gap-2 text-danger-600 text-sm">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Mot de passe -->
                    <div class="group/input">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-warning-100 flex items-center justify-center group-hover/input:bg-warning-200 transition-colors">
                                    <i class="fas fa-lock text-warning-600 text-xs"></i>
                                </div>
                                <span>Mot de passe</span>
                                <span class="text-danger-500 text-xs">*</span>
                            </div>
                        </label>
                        <div class="relative">
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required 
                                   class="input-elegant pl-10 pr-10 @error('password') border-danger-500 focus:ring-danger-500 focus:border-danger-500 @enderror"
                                   placeholder="••••••••">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <button type="button" 
                                    onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye text-sm" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="mt-2 flex items-center gap-2 text-danger-600 text-sm">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center">
                            <input id="remember-me" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember-me" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                                Se souvenir de moi
                            </label>
                        </div>

                        <a href="#" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1">
                            <i class="fas fa-key text-xs"></i>
                            <span>Mot de passe oublié ?</span>
                        </a>
                    </div>

                    <!-- Bouton de connexion -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="group w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl font-semibold shadow-elegant hover:shadow-glow hover:-translate-y-0.5 hover:scale-105 transition-all duration-300">
                            <i class="fas fa-sign-in-alt group-hover:translate-x-1 transition-transform duration-300"></i>
                            <span>Se connecter</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-300"></i>
                        </button>
                    </div>

                    <!-- Info admin -->
                    <div class="pt-4 border-t-2 border-gray-100">
                        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-blue-900 font-medium mb-1">Information</p>
                                <p class="text-xs text-blue-700">Seuls les administrateurs peuvent créer de nouveaux comptes utilisateurs.</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer élégant -->
        <div class="text-center mt-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full shadow-md">
                <i class="fas fa-shield-alt text-primary-500 text-sm"></i>
                <p class="text-sm text-gray-600 font-medium">
                    © {{ date('Y') }} EPS.SARL - Connexion sécurisée
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Auto-focus sur le champ email
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});
</script>
@endsection