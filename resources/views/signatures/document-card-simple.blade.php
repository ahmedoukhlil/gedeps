@php
    $status = $document->status;
    $statusColors = [
        'pending' => ['bg' => 'bg-red-600', 'text' => 'text-red-700', 'border' => 'border-red-200'],
        'in_progress' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
        'signed' => ['bg' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200']
    ];
    $colors = $statusColors[$status] ?? $statusColors['pending'];
@endphp

<div class="bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 lg:gap-6">
            <div class="flex-1">
                <!-- En-tête du document -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg {{ $colors['bg'] }}">
                            <i class="fas fa-file-pdf text-white text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1 truncate">{{ $document->document_name }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium truncate">Créé par {{ $document->uploader->name }}</p>
                        </div>
                    </div>
                    <!-- Badge de statut -->
                    <div class="flex-shrink-0">
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
                <div class="bg-gray-50 rounded-lg sm:rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-gray-200 shadow-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-file-alt text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</span>
                                <p class="text-xs sm:text-sm font-semibold text-gray-900 truncate">{{ $document->type_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-calendar-plus text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Créé le</span>
                                <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center sm:col-span-2 lg:col-span-1">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-edit text-white text-xs sm:text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Modifié le</span>
                                <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations spécifiques au statut -->
                @if($status === 'pending')
                    <div class="bg-red-50 border border-red-300 rounded-lg sm:rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-600 rounded-full flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-exclamation-triangle text-white text-sm sm:text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-semibold text-red-800">Action requise</p>
                                <p class="text-xs text-red-700">Ce document nécessite votre signature</p>
                            </div>
                        </div>
                    </div>
                @elseif($status === 'in_progress')
                    <div class="bg-indigo-50 border border-indigo-300 rounded-lg sm:rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-600 rounded-full flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-info-circle text-white text-sm sm:text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-semibold text-indigo-800">En cours</p>
                                <p class="text-xs text-indigo-700">Ce document est en cours de traitement</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 border border-emerald-300 rounded-lg sm:rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-600 rounded-full flex items-center justify-center mr-2 sm:mr-3 shadow-md">
                                <i class="fas fa-check-circle text-white text-sm sm:text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-semibold text-emerald-800">Terminé</p>
                                <p class="text-xs text-emerald-700">Ce document a été signé</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 sm:gap-3 w-full lg:w-auto lg:min-w-[180px]">
                @if($status === 'pending')
                    <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                       class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-xs sm:text-sm">
                        <i class="fas fa-pen-fancy mr-1.5 sm:mr-2 text-white text-sm"></i>
                        <span class="hidden sm:inline">Signer maintenant</span>
                        <span class="sm:hidden">Signer</span>
                    </a>
                @elseif($status === 'in_progress')
                    <button disabled class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-400 text-gray-600 rounded-lg cursor-not-allowed font-semibold text-xs sm:text-sm">
                        <i class="fas fa-clock mr-1.5 sm:mr-2 text-sm"></i>
                        <span class="hidden sm:inline">En cours</span>
                        <span class="sm:hidden">En cours</span>
                    </button>
                @else
                    <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                       class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-xs sm:text-sm">
                        <i class="fas fa-eye mr-1.5 sm:mr-2 text-white text-sm"></i>
                        <span class="hidden sm:inline">Voir le document signé</span>
                        <span class="sm:hidden">Voir</span>
                    </a>
                @endif
                
                <a href="{{ route('documents.view', $document) }}" 
                   class="inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-semibold text-xs sm:text-sm">
                    <i class="fas fa-file-alt mr-1.5 sm:mr-2 text-white text-sm"></i>
                    <span class="hidden sm:inline">Document original</span>
                    <span class="sm:hidden">Original</span>
                </a>
            </div>
        </div>
    </div>
</div>
