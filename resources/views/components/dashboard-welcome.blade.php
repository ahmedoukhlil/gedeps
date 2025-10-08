@props(['user' => null, 'stats' => []])

<div class="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-700 rounded-3xl p-8 text-white mb-8 shadow-2xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold mb-2">
                Bienvenue, {{ $user->name ?? 'Utilisateur' }} ! 👋
            </h1>
            <p class="text-xl text-blue-100 mb-4">
                Gestion Électronique de Documents - Tableau de Bord
            </p>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    <span>{{ now()->format('H:i') }}</span>
                </div>
                @if($user)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-tag"></i>
                        <span>
                            @if($user->isAdmin())
                                Administrateur
                            @elseif($user->isSignataire())
                                Signataire
                            @else
                                Agent
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="hidden lg:block">
            <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <i class="fas fa-file-signature text-6xl text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<x-dashboard-stats :stats="$stats" />

<!-- Actions Rapides -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <i class="fas fa-bolt text-yellow-500"></i>
        Actions Rapides
    </h2>
    <x-dashboard-quick-actions :user="$user" :stats="$stats" />
</div>

<!-- Section d'information -->
<div class="bg-gradient-to-r from-blue-400 to-blue-600 rounded-2xl p-6 text-white">
    <div class="flex items-start gap-4">
        <div class="bg-white bg-opacity-20 rounded-full p-3">
            <i class="fas fa-lightbulb text-2xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold mb-2">💡 Conseils d'utilisation</h3>
            <ul class="space-y-2 text-blue-100">
                <li class="flex items-center gap-2">
                    <i class="fas fa-check text-blue-300"></i>
                    <span>Utilisez les actions rapides pour accéder rapidement aux fonctions principales</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check text-blue-300"></i>
                    <span>Les documents en attente nécessitent votre attention</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check text-blue-300"></i>
                    <span>Consultez l'historique pour suivre l'évolution de vos documents</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
// Animation d'entrée pour la bannière de bienvenue
document.addEventListener('DOMContentLoaded', function() {
    const welcomeBanner = document.querySelector('.bg-gradient-to-r');
    if (welcomeBanner) {
        welcomeBanner.style.opacity = '0';
        welcomeBanner.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            welcomeBanner.style.transition = 'all 0.8s ease';
            welcomeBanner.style.opacity = '1';
            welcomeBanner.style.transform = 'translateY(0)';
        }, 200);
    }
});

// Effet de particules pour la bannière de bienvenue
function createParticles() {
    const banner = document.querySelector('.bg-gradient-to-r');
    if (!banner) return;
    
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'absolute w-2 h-2 bg-white bg-opacity-30 rounded-full';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animation = `float ${3 + Math.random() * 4}s infinite ease-in-out`;
        particle.style.animationDelay = Math.random() * 2 + 's';
        banner.appendChild(particle);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
        opacity: 0.3;
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
        opacity: 0.8;
    }
}

// Démarrer les particules après le chargement
setTimeout(createParticles, 1000);
</script>
