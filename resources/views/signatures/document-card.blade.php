@php
    $userSignature = $document->sequentialSignatures()->where('user_id', auth()->id())->first();
    $progress = $document->current_signature_index;
    $total = $document->sequentialSignatures->count();
    $percentage = $total > 0 ? ($progress / $total) * 100 : 0;
@endphp

<div class="bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <!-- En-tête du document -->
                <div class="flex items-center mb-6">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mr-4 shadow-lg
                        @if($status === 'to_sign') bg-red-500
                        @elseif($status === 'waiting') bg-indigo-600
                        @else bg-emerald-700 @endif">
                        <i class="fas fa-file-pdf text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $document->document_name }}</h3>
                        <p class="text-sm text-gray-600 font-medium">Créé par {{ $document->uploader->name }}</p>
                    </div>
                    <!-- Badge de statut avec style EPS-One -->
                    <div class="ml-4">
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

                <!-- Informations du document -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-alt text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</span>
                                <p class="text-sm font-semibold text-gray-900">{{ $document->type_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-plus text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Créé le</span>
                                <p class="text-sm font-semibold text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-edit text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Modifié le</span>
                                <p class="text-sm font-semibold text-gray-900">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progression des signatures -->
                <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                    <div class="flex items-center justify-between mb-3">
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

                <!-- Liste des signataires -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-users text-gray-600 mr-2"></i>
                        <h4 class="text-sm font-semibold text-gray-800">Ordre des signatures</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($document->sequentialSignatures->sortBy('signature_order') as $signature)
                            <div class="flex items-center p-3 rounded-lg border transition-all duration-200
                                @if($signature->status === 'signed') bg-emerald-50 border-emerald-200 shadow-sm
                                @elseif($signature->signature_order == $document->current_signature_index + 1) bg-red-50 border-red-200 shadow-md
                                @else bg-indigo-50 border-indigo-200 @endif">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mr-3 shadow-sm
                                    @if($signature->status === 'signed') bg-emerald-700 text-white
                                    @elseif($signature->signature_order == $document->current_signature_index + 1) bg-red-500 text-white
                                    @else bg-indigo-600 text-white @endif">
                                    {{ $signature->signature_order }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $signature->user->name }}</p>
                                    @if($signature->status === 'signed' && $signature->signed_at)
                                        <p class="text-xs text-emerald-600">{{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m H:i') }}</p>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <p class="text-xs text-red-600 font-medium">En cours</p>
                                    @else
                                        <p class="text-xs text-indigo-600">En attente</p>
                                    @endif
                                </div>
                                <div class="ml-2">
                                    @if($signature->status === 'signed')
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <i class="fas fa-clock text-red-500"></i>
                                    @else
                                        <i class="fas fa-circle text-indigo-400"></i>
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
                            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-white"></i>
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
                            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-white"></i>
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
                            <div class="w-10 h-10 bg-emerald-700 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Terminé</p>
                                <p class="text-xs text-emerald-700">Vous avez déjà signé ce document</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 ml-6 min-w-[200px]">
                @if($status === 'to_sign')
                    <a href="{{ route('signatures.simple.show', $document) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-pen-fancy mr-2"></i>
                        Signer maintenant
                    </a>
                @elseif($status === 'waiting')
                    <button disabled class="inline-flex items-center justify-center px-6 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed font-semibold">
                        <i class="fas fa-clock mr-2"></i>
                        En attente
                    </button>
                @else
                    <a href="{{ route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-emerald-700 text-white rounded-xl hover:bg-emerald-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-eye mr-2"></i>
                        Voir le document signé
                    </a>
                @endif
                
                <a href="{{ route('documents.view', $document) }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 font-medium border border-gray-300">
                    <i class="fas fa-file-alt mr-2"></i>
                    Document original
                </a>
            </div>
        </div>
    </div>
</div>
