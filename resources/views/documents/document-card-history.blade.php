@php
    $statusColors = [
        'pending' => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-200', 'label' => 'En attente'],
        'signed' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'label' => 'Signé'],
        'paraphed' => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200', 'label' => 'Paraphé'],
        'signed_and_paraphed' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-200', 'label' => 'Signé et paraphé']
    ];
    $colors = $statusColors[$document->status] ?? $statusColors['pending'];
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
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $document->document_name ?? $document->filename_original }}</h3>
                        <p class="text-sm text-gray-600 font-medium">
                            @if(auth()->user()->isAdmin())
                                Uploadé par {{ $document->uploader->name ?? 'Utilisateur inconnu' }}
                            @elseif(auth()->user()->isAgent())
                                Signataire: {{ $document->signer->name ?? 'Non assigné' }}
                            @else
                                Uploadé par {{ $document->uploader->name ?? 'Utilisateur inconnu' }}
                            @endif
                        </p>
                    </div>
                    <!-- Badge de statut -->
                    <div class="ml-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold {{ $colors['text'] }} bg-opacity-10 border {{ $colors['border'] }} shadow-sm">
                            <i class="fas fa-{{ $document->status === 'signed' ? 'check-circle' : ($document->status === 'pending' ? 'clock' : ($document->status === 'paraphed' ? 'edit' : 'check-double')) }} mr-2"></i>
                            {{ $colors['label'] }}
                        </span>
                    </div>
                </div>

                <!-- Informations du document -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-tag text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</span>
                                <p class="text-sm font-semibold text-gray-900">{{ ucfirst($document->type) }}</p>
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
                                <i class="fas fa-weight text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Taille</span>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description si disponible -->
                @if($document->description)
                    <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>
                            <span class="text-sm font-medium text-blue-800">Description</span>
                        </div>
                        <p class="text-sm text-blue-700">{{ $document->description }}</p>
                    </div>
                @endif

                <!-- Informations spécifiques au statut -->
                @if($document->status === 'pending')
                    <div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">En attente de signature</p>
                                <p class="text-xs text-red-700">Ce document attend d'être signé</p>
                            </div>
                        </div>
                    </div>
                @elseif($document->status === 'signed')
                    <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Document signé</p>
                                <p class="text-xs text-emerald-700">Ce document a été signé avec succès</p>
                            </div>
                        </div>
                    </div>
                @elseif($document->status === 'paraphed')
                    <div class="bg-indigo-50 border border-indigo-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-edit text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">Document paraphé</p>
                                <p class="text-xs text-indigo-700">Ce document a été paraphé</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-purple-50 border border-purple-300 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check-double text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-purple-800">Document complet</p>
                                <p class="text-xs text-purple-700">Ce document a été signé et paraphé</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3 ml-6 min-w-[200px]">
                @if($document->status === 'pending')
                    <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-pen-fancy mr-2"></i>
                        Signer maintenant
                    </a>
                @else
                    <a href="{{ route('documents.view', $document) }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-eye mr-2"></i>
                        Voir le document
                    </a>
                @endif
                
                <a href="{{ route('documents.download', $document) }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 font-medium border border-gray-300">
                    <i class="fas fa-download mr-2"></i>
                    Télécharger
                </a>
            </div>
        </div>
    </div>
</div>
