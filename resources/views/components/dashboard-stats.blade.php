@props(['stats' => []])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Statistique Documents en Attente -->
    <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">En Attente</p>
                <p class="text-3xl font-bold">{{ $stats['pending_documents'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full p-3">
                <i class="fas fa-clock text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Statistique Documents Signés -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Signés</p>
                <p class="text-3xl font-bold">{{ $stats['signed_documents'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Statistique Signatures Séquentielles -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Séquentielles</p>
                <p class="text-3xl font-bold">{{ $stats['sequential_documents'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full p-3">
                <i class="fas fa-list-ol text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Statistique Total Documents -->
    <div class="bg-gradient-to-br from-indigo-500 to-blue-800 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Total</p>
                <p class="text-3xl font-bold">{{ $stats['total_documents'] ?? 0 }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full p-3">
                <i class="fas fa-file-alt text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<style>
/* Animation d'entrée pour les cartes de statistiques */
.dashboard-stats-card {
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Effet de brillance au survol */
.dashboard-stats-card:hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shine 0.8s ease-in-out;
}

@keyframes shine {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}
</style>
