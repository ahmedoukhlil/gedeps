@php
    $userSignature = $document->sequentialSignatures()->where('user_id', auth()->id())->first();
    $progress = $document->current_signature_index;
    $total = $document->sequentialSignatures->count();
    $percentage = $total > 0 ? ($progress / $total) * 100 : 0;
@endphp

<div class="document-card-responsive bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1 min-w-0">
                <!-- En-tête du document - Responsive -->
                <div class="document-header flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0
                            @if($status === 'to_sign') bg-red-500
                            @elseif($status === 'waiting') bg-indigo-600
                            @else bg-emerald-700 @endif">
                            <i class="fas fa-file-pdf text-white text-lg sm:text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1 truncate">{{ $document->document_name }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium truncate">Créé par {{ $document->uploader->name }}</p>
                        </div>
                    </div>
                    <!-- Badge de statut avec style EPS-One - Responsive -->
                    <div class="flex-shrink-0">
                        @if($status === 'to_sign')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-300 shadow-sm">
                                <i class="fas fa-pen-fancy mr-2 text-red-600"></i>
                                À signer
                            </span>
                        @elseif($status === 'waiting')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 border border-indigo-300 shadow-sm">
                                <i class="fas fa-clock mr-2 text-indigo-600"></i>
                                En attente
                            </span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">
                                <i class="fas fa-check-circle mr-2 text-emerald-600"></i>
                                Signé
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Informations du document - Responsive -->
                <div class="bg-gray-50 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-gray-200 shadow-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                                <i class="fas fa-file-alt text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</span>
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $document->type_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                                <i class="fas fa-calendar-plus text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Créé le</span>
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md flex-shrink-0">
                                <i class="fas fa-edit text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Modifié le</span>
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progression des signatures - Responsive -->
                <div class="bg-blue-50 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-blue-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 mb-3">
                        <div class="flex items-center">
                            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                            <span class="text-sm font-semibold text-gray-800">Progression des signatures</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-blue-900">{{ $progress }}</span>
                            <span class="text-sm text-gray-600 mx-1">/</span>
                            <span class="text-lg font-bold text-gray-700">{{ $total }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                        <div class="h-3 rounded-full shadow-sm transition-all duration-500 ease-out
                            @if($status === 'to_sign') bg-red-500
                            @elseif($status === 'waiting') bg-indigo-600
                            @else bg-emerald-700 @endif" 
                            style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="mt-2 text-center">
                        <span class="text-xs font-medium text-gray-600">{{ number_format($percentage, 1) }}% complété</span>
                    </div>
                </div>

                <!-- Liste des signataires - Responsive -->
                <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-center mb-3 sm:mb-4">
                        <i class="fas fa-users text-gray-600 mr-2"></i>
                        <h4 class="text-sm font-semibold text-gray-800">Ordre des signatures</h4>
                    </div>
                    <div class="signers-grid">
                        @foreach($document->sequentialSignatures->sortBy('signature_order') as $signature)
                            <div class="signer-item
                                @if($signature->status === 'signed') signer-signed
                                @elseif($signature->signature_order == $document->current_signature_index + 1) signer-current
                                @else signer-waiting @endif">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs font-bold mr-2 sm:mr-3 shadow-sm flex-shrink-0
                                    @if($signature->status === 'signed') bg-emerald-700 text-white
                                    @elseif($signature->signature_order == $document->current_signature_index + 1) bg-red-500 text-white
                                    @else bg-indigo-600 text-white @endif">
                                    {{ $signature->signature_order }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ $signature->user->name }}</p>
                                    @if($signature->status === 'signed' && $signature->signed_at)
                                        <p class="text-xs text-emerald-600">{{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m H:i') }}</p>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <p class="text-xs text-red-600 font-medium">En cours</p>
                                    @else
                                        <p class="text-xs text-indigo-600">En attente</p>
                                    @endif
                                </div>
                                <div class="ml-1 sm:ml-2 flex-shrink-0">
                                    @if($signature->status === 'signed')
                                        <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <i class="fas fa-clock text-red-500 text-sm"></i>
                                    @else
                                        <i class="fas fa-circle text-indigo-400 text-sm"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Informations spécifiques au statut -->
                @if($status === 'to_sign')
                    <div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Action requise</p>
                                <p class="text-xs text-red-700">C'est votre tour de signer ce document</p>
                            </div>
                        </div>
                    </div>
                @elseif($status === 'waiting')
                    <div class="bg-indigo-50 border border-indigo-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-info-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">En attente</p>
                                <p class="text-xs text-indigo-700">Vous devez attendre que les signataires précédents aient signé</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-check-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Terminé</p>
                                <p class="text-xs text-emerald-700">Vous avez déjà signé ce document</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions - Responsive -->
            <div class="document-actions flex flex-col sm:flex-row lg:flex-col gap-3 mt-4 lg:mt-0 lg:ml-6 lg:min-w-[200px]">
                @if($status === 'to_sign')
                    <a href="{{ route('signatures.simple.show', $document) }}" 
                       class="action-button bg-red-600 text-white hover:bg-red-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-pen-fancy mr-2 text-white"></i>
                        <span class="hidden sm:inline">Signer maintenant</span>
                        <span class="sm:hidden">Signer</span>
                    </a>
                @elseif($status === 'waiting')
                    <button disabled class="action-button bg-gray-400 text-gray-600 cursor-not-allowed">
                        <i class="fas fa-clock mr-2"></i>
                        <span class="hidden sm:inline">En attente</span>
                        <span class="sm:hidden">Attente</span>
                    </button>
                @else
                    <a href="{{ route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']) }}" 
                       class="action-button bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-eye mr-2 text-white"></i>
                        <span class="hidden sm:inline">Voir le document signé</span>
                        <span class="sm:hidden">Voir</span>
                    </a>
                @endif
                
                <a href="{{ route('documents.view', $document) }}" 
                   class="action-button bg-gray-600 text-white hover:bg-gray-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-file-alt mr-2 text-white"></i>
                    <span class="hidden sm:inline">Document original</span>
                    <span class="sm:hidden">Original</span>
                </a>
            </div>
        </div>
    </div>
</div>
