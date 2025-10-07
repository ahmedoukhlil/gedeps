@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo et titre -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-primary rounded-full flex items-center justify-center mb-4">
                <img src="{{ asset('images/logo-eps-sarl.svg') }}" alt="EPS.SARL" class="h-8 w-auto filter brightness-0 invert">
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Connexion à EPS.SARL
            </h2>
            <p class="text-gray-600">
                Gestion Électronique de Documents
            </p>
        </div>

        <!-- Formulaire de connexion -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Adresse email
                        </label>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="votre@email.com"
                               value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <div class="password-input-wrapper">
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required 
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Votre mot de passe">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input id="remember-me" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="remember-checkbox"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember-me" class="remember-label">
                                <span class="checkmark"></span>
                                Se souvenir de moi
                            </label>
                        </div>

                        <div class="forgot-password">
                            <a href="#" class="forgot-link">
                                <i class="fas fa-key"></i>
                                Mot de passe oublié ?
                            </a>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Se connecter</span>
                        </button>
                    </div>

                    <div class="form-footer">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <p>Seuls les administrateurs peuvent créer des comptes.</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                © {{ date('Y') }} EPS.SARL - Tous droits réservés
            </p>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la page de connexion */
.password-input-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-gray-500);
    cursor: pointer;
    padding: 4px;
    transition: all 0.2s ease;
}

.password-toggle:hover {
    color: var(--primary);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 24px 0;
    flex-wrap: wrap;
    gap: 16px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
}

.remember-checkbox {
    display: none;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--text-gray-500);
    user-select: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    position: relative;
}

.remember-checkbox:checked + .remember-label .checkmark {
    background: var(--primary);
    border-color: var(--primary);
}

.remember-checkbox:checked + .remember-label .checkmark::after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.forgot-link {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.forgot-link:hover {
    color: var(--primary-dark);
    text-decoration: none;
}

.form-actions {
    margin: 32px 0;
}

.form-footer {
    margin-top: 24px;
}

/* Responsive */
@media (max-width: 480px) {
    .form-options {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .remember-me {
        order: 2;
    }
    
    .forgot-password {
        order: 1;
    }
}
</style>

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
</script>
@endsection