@php
    $status = $document->status;
    $statusColors = [
        'pending' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-200'],
        'in_progress' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
        'signed' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200']
    ];
    $colors = $statusColors[$status] ?? $statusColors['pending'];
@endphp

<div class="bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <!-- En-tête du document -->
                <div class="flex items-center mb-6">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mr-4 shadow-lg {{ $colors['bg'] }}">
                        <i class="fas fa-file-pdf text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $document->document_name }}</h3>
                        <p class="text-sm text-gray-600 font-medium">Créé par {{ $document->uploader->name }}</p>
                    </div>
                    <!-- Badge de statut -->
                    <div class="ml-4">
                        @if($status === 'pending')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-300 shadow-sm">
                                <i class="fas fa-pen-fancy mr-2 text-red-600"></i>
                                À signer
                            </span>
                        @elseif($status === 'in_progress')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 border border-indigo-300 shadow-sm">
                                <i class="fas fa-clock mr-2 text-indigo-600"></i>
                                En cours
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

                <!-- Informations spécifiques au statut -->
                @if($status === 'pending')
                    <div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Action requise</p>
                                <p class="text-xs text-red-700">Ce document nécessite votre signature</p>
                            </div>
                        </div>
                    </div>
                @elseif($status === 'in_progress')
                    <div class="bg-indigo-50 border border-indigo-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">En cours</p>
                                <p class="text-xs text-indigo-700">Ce document est en cours de traitement</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Terminé</p>
                                <p class="text-xs text-emerald-700">Ce document a été signé</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 ml-6 min-w-[200px]">
                @if($status === 'pending')
                    <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-pen-fancy mr-2"></i>
                        Signer maintenant
                    </a>
                @elseif($status === 'in_progress')
                    <button disabled class="inline-flex items-center justify-center px-6 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed font-semibold">
                        <i class="fas fa-clock mr-2"></i>
                        En cours
                    </button>
                @else
                    <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
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
