@php
    $userSignature = $document->sequentialSignatures()->where('user_id', auth()->id())->first();
    $progress = $document->current_signature_index;
    $total = $document->sequentialSignatures->count();
    $percentage = $total > 0 ? ($progress / $total) * 100 : 0;
@endphp

<div class="card card-hover overflow-hidden group">
    <div class="p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 lg:gap-6">
            <div class="flex-1 min-w-0">
                <!-- En-tête du document élégant -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                        <!-- Icône PDF avec animation -->
                        <div class="relative flex-shrink-0">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br 
                                @if($status === 'to_sign') from-danger-400 to-danger-600
                                @elseif($status === 'waiting') from-warning-400 to-warning-600
                                @else from-success-400 to-success-600 @endif
                                rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                                <i class="fas fa-file-pdf text-white text-2xl"></i>
                            </div>
                            <!-- Badge de statut animé -->
                            @if($status === 'to_sign')
                                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                                </span>
                            @endif
                        </div>
                        
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1 truncate">{{ $document->document_name }}</h3>
                            <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
                                <i class="fas fa-user text-info-500"></i>
                                <span class="truncate">{{ $document->uploader->name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Badge de statut moderne -->
                    <div class="flex-shrink-0">
                        @if($status === 'to_sign')
                            <span class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold bg-danger-100 text-danger-800 border-2 border-danger-300 shadow-sm">
                                <i class="fas fa-pen-fancy"></i>
                                <span>À signer</span>
                            </span>
                        @elseif($status === 'waiting')
                            <span class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold bg-warning-100 text-warning-800 border-2 border-warning-300 shadow-sm">
                                <i class="fas fa-clock"></i>
                                <span>En attente</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold bg-success-100 text-success-800 border-2 border-success-300 shadow-sm">
                                <i class="fas fa-check-circle"></i>
                                <span>Signé</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Informations du document élégantes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="bg-gradient-to-br from-white to-primary-50 rounded-lg p-3 border border-primary-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-tag text-primary-500 text-sm"></i>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</span>
                        </div>
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $document->type_name }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-white to-success-50 rounded-lg p-3 border border-success-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-calendar-plus text-success-500 text-sm"></i>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Créé le</span>
                        </div>
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-white to-info-50 rounded-lg p-3 border border-info-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-edit text-info-500 text-sm"></i>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Modifié le</span>
                        </div>
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Progression des signatures élégante -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200 mb-4 sm:mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chart-line 
                                @if($status === 'to_sign') text-danger-500
                                @elseif($status === 'waiting') text-warning-500
                                @else text-success-500 @endif"></i>
                            <span class="text-sm font-bold text-gray-900">Progression</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xl font-bold 
                                @if($status === 'to_sign') text-danger-600
                                @elseif($status === 'waiting') text-warning-600
                                @else text-success-600 @endif">{{ $progress }}</span>
                            <span class="text-sm text-gray-500">/</span>
                            <span class="text-xl font-bold text-gray-700">{{ $total }}</span>
                        </div>
                    </div>
                    <div class="relative w-full bg-gray-200 rounded-full h-3 shadow-inner overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r 
                            @if($status === 'to_sign') from-danger-500 to-danger-600
                            @elseif($status === 'waiting') from-warning-500 to-warning-600
                            @else from-success-500 to-success-600 @endif
                            h-full rounded-full shadow-sm transition-all duration-500 ease-out" 
                            style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="mt-2 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold
                            @if($status === 'to_sign') bg-danger-100 text-danger-700
                            @elseif($status === 'waiting') bg-warning-100 text-warning-700
                            @else bg-success-100 text-success-700 @endif">
                            <i class="fas fa-percentage text-[10px]"></i>
                            {{ number_format($percentage, 1) }}% complété
                        </span>
                    </div>
                </div>

                <!-- Liste des signataires élégante -->
                <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-center gap-2 mb-3 sm:mb-4">
                        <i class="fas fa-users text-primary-500"></i>
                        <h4 class="text-sm font-bold text-gray-900">Ordre des signatures</h4>
                    </div>
                    <div class="space-y-2 max-h-48 sm:max-h-64 overflow-y-auto">
                        @foreach($document->sequentialSignatures->sortBy('signature_order') as $signature)
                            <div class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg transition-all
                                @if($signature->status === 'signed') bg-success-50 border border-success-200
                                @elseif($signature->signature_order == $document->current_signature_index + 1) bg-danger-50 border border-danger-200
                                @else bg-gray-50 border border-gray-200 @endif">
                                <!-- Numéro avec dégradé -->
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0
                                    @if($signature->status === 'signed') bg-gradient-to-br from-success-500 to-success-700 text-white
                                    @elseif($signature->signature_order == $document->current_signature_index + 1) bg-gradient-to-br from-danger-500 to-danger-700 text-white
                                    @else bg-gradient-to-br from-gray-400 to-gray-600 text-white @endif">
                                    {{ $signature->signature_order }}
                                </div>
                                
                                <!-- Info utilisateur -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $signature->user->name }}</p>
                                    @if($signature->status === 'signed' && $signature->signed_at)
                                        <p class="text-xs text-success-600 font-medium">
                                            <i class="fas fa-check text-[10px]"></i>
                                            {{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m H:i') }}
                                        </p>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <p class="text-xs text-danger-600 font-bold">
                                            <i class="fas fa-clock text-[10px]"></i>
                                            En cours
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-hourglass-half text-[10px]"></i>
                                            En attente
                                        </p>
                                    @endif
                                </div>
                                
                                <!-- Badge de statut -->
                                <div class="flex-shrink-0">
                                    @if($signature->status === 'signed')
                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-success-500 flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-danger-500 flex items-center justify-center animate-pulse">
                                            <i class="fas fa-pen text-white text-xs"></i>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-ellipsis-h text-gray-600 text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Alerte selon le statut -->
                @if($status === 'to_sign')
                    <div class="alert bg-danger-50 border-2 border-danger-300 rounded-xl p-3 sm:p-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-danger-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-white text-sm sm:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-danger-800">Action requise</p>
                                <p class="text-xs text-danger-700">C'est votre tour de signer ce document</p>
                            </div>
                        </div>
                    </div>
                @elseif($status === 'waiting')
                    <div class="alert bg-warning-50 border-2 border-warning-300 rounded-xl p-3 sm:p-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-warning-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-info-circle text-white text-sm sm:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-warning-800">En attente</p>
                                <p class="text-xs text-warning-700">Attendez que les signataires précédents signent</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert bg-success-50 border-2 border-success-300 rounded-xl p-3 sm:p-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-success-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check-circle text-white text-sm sm:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-success-800">Terminé</p>
                                <p class="text-xs text-success-700">Vous avez déjà signé ce document</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Boutons d'action élégants -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 lg:w-40 w-full sm:w-auto">
                @if($status === 'to_sign')
                    <a href="{{ route('signatures.simple.show', $document) }}" 
                       class="group flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-danger-500 to-danger-600 hover:from-danger-600 hover:to-danger-700 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 min-h-[48px]">
                        <i class="fas fa-pen-fancy group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold text-sm sm:text-base">Signer maintenant</span>
                    </a>
                @elseif($status === 'waiting')
                    <button disabled class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed opacity-60 min-h-[48px]">
                        <i class="fas fa-clock"></i>
                        <span class="font-semibold text-sm sm:text-base">En attente</span>
                    </button>
                @else
                    <a href="{{ route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']) }}" 
                       class="group flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-success-500 to-success-600 hover:from-success-600 hover:to-success-700 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 min-h-[48px]">
                        <i class="fas fa-eye group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold text-sm sm:text-base">Voir signé</span>
                    </a>
                @endif
                
                <a href="{{ route('documents.view', $document) }}" 
                   class="group flex items-center justify-center gap-2 px-4 py-3 bg-white hover:bg-gray-50 border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 min-h-[48px]">
                    <i class="fas fa-file-alt text-sm group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-sm sm:text-base">Original</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Améliorations de responsivité pour la carte document */
@media (max-width: 640px) {
    .card {
        margin: 0 -0.5rem;
        border-radius: 0;
    }
    
    .card .p-4 {
        padding: 1rem;
    }
    
    /* Améliorer la lisibilité des textes sur mobile */
    .text-sm {
        font-size: 0.875rem;
    }
    
    .text-xs {
        font-size: 0.75rem;
    }
    
    /* Optimiser les boutons pour le tactile */
    .min-h-\[48px\] {
        min-height: 48px;
        touch-action: manipulation;
    }
    
    /* Améliorer l'espacement des éléments */
    .gap-2 {
        gap: 0.5rem;
    }
    
    .gap-3 {
        gap: 0.75rem;
    }
    
    /* Optimiser la grille des informations */
    .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    
    /* Améliorer la progression */
    .h-3 {
        height: 0.75rem;
    }
    
    /* Optimiser les badges de statut */
    .w-7 {
        width: 1.75rem;
    }
    
    .h-7 {
        height: 1.75rem;
    }
    
    /* Améliorer la lisibilité des alertes */
    .alert {
        margin-bottom: 1rem;
    }
    
    .alert .w-8 {
        width: 2rem;
    }
    
    .alert .h-8 {
        height: 2rem;
    }
}

/* Améliorations pour les très petits écrans */
@media (max-width: 480px) {
    .card .p-4 {
        padding: 0.75rem;
    }
    
    /* Réduire les espacements sur très petits écrans */
    .mb-4 {
        margin-bottom: 0.75rem;
    }
    
    .mb-6 {
        margin-bottom: 1rem;
    }
    
    /* Optimiser les boutons */
    .px-4 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    .py-3 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    /* Améliorer la lisibilité des textes */
    .text-base {
        font-size: 0.875rem;
    }
    
    .text-lg {
        font-size: 1rem;
    }
    
    /* Optimiser les icônes */
    .text-2xl {
        font-size: 1.25rem;
    }
    
    .text-3xl {
        font-size: 1.5rem;
    }
}

/* Améliorations pour les écrans tactiles */
@media (hover: none) and (pointer: coarse) {
    .card-hover:hover {
        transform: none;
    }
    
    .group-hover\:scale-110:hover {
        transform: none;
    }
    
    .group-hover\:-translate-y-1:hover {
        transform: none;
    }
    
    /* Améliorer la réactivité tactile */
    .min-h-\[48px\] {
        min-height: 48px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
    }
    
    /* Optimiser les interactions tactiles */
    .flex-shrink-0 {
        flex-shrink: 0;
    }
    
    .min-w-0 {
        min-width: 0;
    }
}

/* Améliorations pour les écrans haute résolution */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .shadow-lg {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
}

/* Mode sombre */
@media (prefers-color-scheme: dark) {
    .card {
        background-color: #1f2937;
        border-color: #374151;
    }
    
    .text-gray-900 {
        color: #f9fafb;
    }
    
    .text-gray-600 {
        color: #d1d5db;
    }
    
    .bg-white {
        background-color: #1f2937;
    }
    
    .border-gray-200 {
        border-color: #374151;
    }
}
</style>
