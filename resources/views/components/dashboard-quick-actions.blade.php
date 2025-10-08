@props(['user' => null, 'stats' => []])

<div class="dashboard-actions-grid">
    @if($user && !$user->isAdmin())
        <!-- Actions pour les utilisateurs non-admin -->
        <a href="{{ route('documents.upload') }}" 
           class="dashboard-quick-action dashboard-action-upload"
           title="Soumettre un nouveau document">
            <i class="fas fa-upload text-xl"></i>
            <span>Soumettre</span>
        </a>

        <a href="{{ route('signatures.index') }}" 
           class="dashboard-quick-action dashboard-action-sign"
           title="Signatures simples">
            <i class="fas fa-pen-fancy text-xl"></i>
            <span>Signatures</span>
        </a>
        
        <a href="{{ route('signatures.simple.index') }}" 
           class="dashboard-quick-action dashboard-action-sequential"
           title="Signatures séquentielles">
            <i class="fas fa-list-ol text-xl"></i>
            <span>Séquentielles</span>
        </a>
    @endif

    <!-- Actions communes -->
    <a href="{{ route('documents.pending') }}" 
       class="dashboard-quick-action dashboard-action-pending"
       title="Documents en attente">
        <i class="fas fa-clock text-xl"></i>
        <span>En Attente</span>
        @if(isset($stats['pending_documents']) && $stats['pending_documents'] > 0)
            <span class="bg-white bg-opacity-20 px-2 py-1 rounded-full text-xs font-bold">
                {{ $stats['pending_documents'] }}
            </span>
        @endif
    </a>

    <a href="{{ route('documents.history') }}" 
       class="dashboard-quick-action dashboard-action-history"
       title="Historique des documents">
        <i class="fas fa-history text-xl"></i>
        <span>Historique</span>
    </a>

    <a href="{{ route('profile.index') }}" 
       class="dashboard-quick-action dashboard-action-profile"
       title="Mon profil">
        <i class="fas fa-user text-xl"></i>
        <span>Profil</span>
    </a>

    @if($user && $user->isAdmin())
        <!-- Actions pour les administrateurs -->
        <a href="{{ route('admin.dashboard') }}" 
           class="dashboard-quick-action dashboard-action-admin"
           title="Administration">
            <i class="fas fa-cog text-xl"></i>
            <span>Administration</span>
        </a>
    @endif

    <!-- Déconnexion -->
    <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" 
                class="dashboard-quick-action dashboard-action-logout"
                title="Se déconnecter">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Déconnexion</span>
        </button>
    </form>
</div>

<script>
// Animation d'entrée pour les boutons
document.addEventListener('DOMContentLoaded', function() {
    const actions = document.querySelectorAll('.dashboard-quick-action');
    actions.forEach((action, index) => {
        action.style.opacity = '0';
        action.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            action.style.transition = 'all 0.5s ease';
            action.style.opacity = '1';
            action.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Effet de pulsation pour les boutons avec notifications
document.querySelectorAll('.dashboard-quick-action').forEach(button => {
    const notification = button.querySelector('.bg-white');
    if (notification) {
        button.style.animation = 'pulse 2s infinite';
    }
});

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}
</script>
