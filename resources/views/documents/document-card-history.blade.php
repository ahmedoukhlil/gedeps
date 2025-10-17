@php
    $statusColors = [
        'pending' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'label' => 'En attente', 'bg_light' => 'bg-orange-50'],
        'signed' => ['bg' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Signé', 'bg_light' => 'bg-emerald-50'],
        'paraphed' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'label' => 'Paraphé', 'bg_light' => 'bg-purple-50'],
        'signed_and_paraphed' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'label' => 'Signé et paraphé', 'bg_light' => 'bg-indigo-50'],
        'in_progress' => ['bg' => 'bg-blue-600', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'label' => 'En cours', 'bg_light' => 'bg-blue-50']
    ];
    
    // Gérer les signatures séquentielles
    if ($document->sequential_signatures) {
        if ($document->status === 'in_progress') {
            $colors = $statusColors['in_progress'];
        } else {
            $colors = $statusColors['signed'];
        }
    } else {
        $colors = $statusColors[$document->status] ?? $statusColors['pending'];
    }
@endphp

<div class="bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-4 sm:p-6">
        <!-- En-tête du document -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4 sm:mb-6">
            <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center shadow-lg {{ $colors['bg'] }} flex-shrink-0">
                    <i class="fas fa-file-pdf text-white text-xl sm:text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-1 truncate">{{ $document->document_name ?? $document->filename_original }}</h3>
                    <p class="text-xs sm:text-sm text-gray-600 font-medium">
                        @if($document->sequential_signatures)
                            <i class="fas fa-users text-purple-600 mr-1"></i>
                            <span class="hidden sm:inline">Signatures séquentielles</span>
                            <span class="sm:hidden">Séq.</span>
                            @if($document->sequentialSignatures && $document->sequentialSignatures->count() > 0)
                                ({{ $document->sequentialSignatures->where('status', 'signed')->count() }}/{{ $document->sequentialSignatures->count() }})
                            @endif
                        @elseif(auth()->user()->isAdmin())
                            <i class="fas fa-user-upload text-blue-600 mr-1"></i>
                            <span class="hidden sm:inline">Uploadé par {{ $document->uploader->name ?? 'Utilisateur inconnu' }}</span>
                            <span class="sm:hidden">{{ $document->uploader->name ?? 'Inconnu' }}</span>
                        @elseif(auth()->user()->isAgent())
                            <i class="fas fa-user-check text-green-600 mr-1"></i>
                            <span class="hidden sm:inline">Signataire: {{ $document->signer->name ?? 'Non assigné' }}</span>
                            <span class="sm:hidden">{{ $document->signer->name ?? 'N/A' }}</span>
                        @else
                            <i class="fas fa-user-upload text-blue-600 mr-1"></i>
                            <span class="hidden sm:inline">Uploadé par {{ $document->uploader->name ?? 'Utilisateur inconnu' }}</span>
                            <span class="sm:hidden">{{ $document->uploader->name ?? 'Inconnu' }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <!-- Badge de statut -->
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-semibold {{ $colors['text'] }} {{ $colors['bg_light'] }} border {{ $colors['border'] }} shadow-sm">
                    <i class="fas fa-{{ $document->status === 'signed' ? 'check-circle' : ($document->status === 'pending' ? 'clock' : ($document->status === 'paraphed' ? 'edit' : 'check-double')) }} mr-1 sm:mr-2"></i>
                    {{ $colors['label'] }}
                </span>
            </div>
        </div>

        <!-- Informations du document -->
        <div class="bg-gray-50 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-gray-200 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-tag text-white text-xs sm:text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</span>
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ ucfirst($document->type) }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-calendar-plus text-white text-xs sm:text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Créé le</span>
                        <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-weight text-white text-xs sm:text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Taille</span>
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description si disponible -->
        @if($document->description)
            <div class="bg-blue-50 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-blue-200">
                <div class="flex items-center mb-2">
                    <i class="fas fa-align-left text-blue-600 mr-2"></i>
                    <span class="text-xs sm:text-sm font-medium text-blue-800">Description</span>
                </div>
                <p class="text-xs sm:text-sm text-blue-700 break-words">{{ $document->description }}</p>
            </div>
        @endif

        <!-- Informations spécifiques au statut -->
        @if($document->sequential_signatures && $document->status === 'in_progress')
            <div class="bg-blue-50 border border-blue-300 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-full flex items-center justify-center mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-users text-white text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-blue-800">Signatures séquentielles en cours</p>
                        <p class="text-xs text-blue-700">
                            @if($document->sequentialSignatures)
                                {{ $document->sequentialSignatures->where('status', 'signed')->count() }}/{{ $document->sequentialSignatures->count() }} signatures terminées
                            @else
                                Processus de signature en cours
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @elseif($document->status === 'pending')
            <div class="bg-orange-50 border border-orange-300 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-600 rounded-full flex items-center justify-center mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-clock text-white text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-orange-800">En attente de signature</p>
                        <p class="text-xs text-orange-700">Ce document attend d'être signé</p>
                    </div>
                </div>
            </div>
        @elseif($document->status === 'signed')
            <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-600 rounded-full flex items-center justify-center mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-check-circle text-white text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-emerald-800">Document signé</p>
                        <p class="text-xs text-emerald-700">Ce document a été signé avec succès</p>
                    </div>
                </div>
            </div>
        @elseif($document->status === 'paraphed')
            <div class="bg-purple-50 border border-purple-300 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-600 rounded-full flex items-center justify-center mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-edit text-white text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-purple-800">Document paraphé</p>
                        <p class="text-xs text-purple-700">Ce document a été paraphé</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-indigo-50 border border-indigo-300 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-600 rounded-full flex items-center justify-center mr-3 shadow-md flex-shrink-0">
                        <i class="fas fa-check-double text-white text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-indigo-800">Document complet</p>
                        <p class="text-xs text-indigo-700">Ce document a été signé et paraphé</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4 sm:mt-0">
            @if($document->sequential_signatures)
                @if($document->status === 'in_progress')
                    <a href="{{ route('signatures.simple.show', $document) }}"
                       class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
                        <i class="fas fa-users mr-2 text-white"></i>
                        <span class="hidden sm:inline">Voir le processus</span>
                        <span class="sm:hidden">Processus</span>
                    </a>
                @else
                    <a href="{{ route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']) }}"
                       class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
                        <i class="fas fa-eye mr-2 text-white"></i>
                        Voir
                    </a>
                @endif
            @elseif($document->status === 'pending')
                <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
                    <i class="fas fa-pen-fancy mr-2 text-white"></i>
                    Signer
                </a>
            @else
                <a href="{{ route('documents.view', $document) }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
                    <i class="fas fa-eye mr-2 text-white"></i>
                    Voir
                </a>
            @endif

            <a href="{{ route('documents.download', $document) }}"
               class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-sm sm:text-base">
                <i class="fas fa-download mr-2 text-white"></i>
                <span class="hidden sm:inline">Télécharger</span>
                <span class="sm:hidden">DL</span>
            </a>
        </div>
    </div>
</div>
