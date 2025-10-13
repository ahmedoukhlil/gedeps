@extends('layouts.app')

@section('title', 'Traiter le Document')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Fil d'Ariane Élégant -->
    <nav class="mb-6 sm:mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs sm:text-sm flex-wrap">
            <li>
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                    <i class="fas fa-home text-sm"></i>
                    <span class="hidden sm:inline">Accueil</span>
                </a>
            </li>
            <li class="text-gray-400">
                <i class="fas fa-chevron-right text-xs"></i>
            </li>
            @if(isset($document) && $document->sequential_signatures)
                <li>
                    <a href="{{ route('signatures.simple.index') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                        <i class="fas fa-list-ol text-sm"></i>
                        <span class="hidden sm:inline">Signatures Séquentielles</span>
                    </a>
                </li>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
                <li class="flex items-center gap-1.5 text-primary-600 font-semibold">
                    <i class="fas fa-pen-fancy text-sm"></i>
                    <span class="hidden sm:inline">Signer Document</span>
                </li>
            @else
                <li>
                    <a href="{{ route('signatures.index') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                        <i class="fas fa-pen-fancy text-sm"></i>
                        <span class="hidden sm:inline">Documents à Signer</span>
                    </a>
                </li>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
                <li class="flex items-center gap-1.5 text-primary-600 font-semibold">
                    <i class="fas fa-edit text-sm"></i>
                    <span class="hidden sm:inline">Traiter Document</span>
                </li>
            @endif
        </ol>
    </nav>
    
    <!-- Message de succès pour documents signés -->
    @if($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed())
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <div>
                <h4 class="font-bold">Document traité avec succès !</h4>
                <p>Le document a été {{ $document->status_label }} et est maintenant disponible.</p>
            </div>
        </div>
    @endif
    
    <!-- En-tête du document élégant -->
    <div class="card card-hover mb-6 overflow-hidden relative">
        <!-- Fond décoratif -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full -mr-32 -mt-32 opacity-50"></div>
        
        <div class="relative p-6">
            <div class="flex items-center gap-4 mb-6">
                <!-- Icône document -->
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-file-signature text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $document->document_name }}</h2>
                    <p class="text-sm text-gray-600">Traitement du document</p>
                </div>
            </div>
            
            <!-- Informations du document en grille -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-white to-primary-50 rounded-lg p-4 border border-primary-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-tag text-primary-500"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</span>
                    </div>
                    <div class="text-sm font-bold text-gray-900">{{ $document->type_name }}</div>
                </div>
                
                <div class="bg-gradient-to-br from-white to-info-50 rounded-lg p-4 border border-info-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-user text-info-500"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Uploadé par</span>
                    </div>
                    <div class="text-sm font-bold text-gray-900">{{ $document->uploader->name }}</div>
                </div>
                
                <div class="bg-gradient-to-br from-white to-success-50 rounded-lg p-4 border border-success-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-user-check text-success-500"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Assigné à</span>
                    </div>
                    <div class="text-sm font-bold text-gray-900">{{ $document->signer->name }}</div>
                </div>
                
                <div class="bg-gradient-to-br from-white to-warning-50 rounded-lg p-4 border border-warning-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-calendar text-warning-500"></i>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</span>
                    </div>
                    <div class="text-sm font-bold text-gray-900">{{ $document->created_at->format('d/m/Y') }}</div>
                </div>
                        </div>
                        
                        @if($document->description)
                            <div class="mt-6 bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-3">
                                    <i class="fas fa-align-left text-blue-600"></i>
                                    <span>Description</span>
                                </div>
                                <div class="text-gray-800 leading-relaxed">{{ $document->description }}</div>
                            </div>
                        @endif
                        
                        <!-- État de signature et prochain signataire -->
                        @if(isset($sequentialSignatures) && $sequentialSignatures->count() > 0)
                            <div class="mt-6 bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-4">
                                    <i class="fas fa-users text-blue-600"></i>
                                    <span>État des Signatures Séquentielles</span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- État actuel -->
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                                            <i class="fas fa-info-circle text-blue-600"></i>
                                            <span>État Actuel</span>
                                        </div>
                                        <div class="text-sm text-gray-800">
                                            Signature {{ $document->current_signature_index + 1 }} sur {{ $sequentialSignatures->count() }}
                                        </div>
                                    </div>
                                    
                                    <!-- Prochain signataire -->
                                    @php
                                        $nextSigner = $sequentialSignatures->where('signature_order', $document->current_signature_index + 1)->first();
                                    @endphp
                                    @if($nextSigner)
                                        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                                                <i class="fas fa-user-clock text-yellow-600"></i>
                                                <span>Prochain Signataire</span>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $nextSigner->user->name }}</div>
                                        </div>
                                    @else
                                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                            <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-2">
                                                <i class="fas fa-check-circle text-green-600"></i>
                                                <span>Statut</span>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-800">Toutes les signatures sont terminées</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Liste des signataires -->
                                <div class="mt-4">
                                    <div class="text-sm font-medium text-gray-600 mb-2">Progression des signatures :</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($sequentialSignatures as $signature)
                                            <div class="flex items-center gap-2 px-3 py-1 rounded-full text-xs
                                                {{ $signature->status === 'signed' ? 'bg-green-100 text-green-800' : 
                                                   ($signature->signature_order == $document->current_signature_index + 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                                <i class="fas fa-{{ $signature->status === 'signed' ? 'check-circle' : 
                                                                   ($signature->signature_order == $document->current_signature_index + 1 ? 'clock' : 'circle') }}"></i>
                                                <span>{{ $signature->user->name }}</span>
                                                @if($signature->status === 'signed' && $signature->signed_at)
                                                    <span class="text-xs opacity-75">({{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m H:i') }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed())
                            <div class="detail-item">
                                <label><i class="fas fa-clock"></i> Dernière modification :</label>
                                <span class="detail-value">{{ $document->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($document->isSigned())
                                <div class="detail-item">
                                    <label><i class="fas fa-pen-fancy"></i> Signé par :</label>
                                    <span class="detail-value">{{ $document->signatures()->latest()->first()->signer->name }}</span>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fas fa-calendar-check"></i> Date de signature :</label>
                                    <span class="detail-value">{{ $document->signatures()->latest()->first()->signed_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            @if($document->isParaphed())
                                <div class="detail-item">
                                    <label><i class="fas fa-stamp"></i> Paraphé par :</label>
                                    <span class="detail-value">{{ $document->paraphes()->latest()->first()->parapher->name }}</span>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fas fa-calendar-check"></i> Date de paraphe :</label>
                                    <span class="detail-value">{{ $document->paraphes()->latest()->first()->paraphed_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        @endif
                        
                        @if(isset($sequentialSignatures) && $sequentialSignatures->count() > 0 && !isset($isReadOnly))
                            <!-- Signatures séquentielles -->
                            <div class="mt-6 bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-white mb-4">
                                    <i class="fas fa-users text-white"></i>
                                    <span>Signatures Séquentielles</span>
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($sequentialSignatures as $signature)
                                        <div class="flex items-center justify-between p-3 rounded-lg border {{ $signature->status === 'signed' ? 'bg-green-50 border-green-200' : ($signature->signature_order == $document->current_signature_index + 1 ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50 border-gray-200') }}">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $signature->status === 'signed' ? 'bg-green-500 text-white' : ($signature->signature_order == $document->current_signature_index + 1 ? 'bg-yellow-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                                                    {{ $signature->signature_order }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $signature->user->name }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        @if($signature->status === 'signed')
                                                            <i class="fas fa-check-circle text-green-500"></i>
                                                            Signé le {{ $signature->signed_at->format('d/m/Y H:i') }}
                                                        @elseif($signature->signature_order == $document->current_signature_index + 1)
                                                            <i class="fas fa-clock text-yellow-500"></i>
                                                            En cours
                                                        @else
                                                            <i class="fas fa-hourglass-half text-gray-400"></i>
                                                            En attente
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @if($signature->status === 'signed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Terminé
                                                    </span>
                                                @elseif($signature->signature_order == $document->current_signature_index + 1)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-play mr-1"></i>
                                                        Actuel
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-pause mr-1"></i>
                                                        En attente
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($document->status === 'signed')
                                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center gap-2 text-green-800">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="font-medium">Toutes les signatures sont terminées</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-center gap-2 text-blue-800">
                                            <i class="fas fa-info-circle"></i>
                                            <span class="font-medium">
                                                @if($currentSigner)
                                                    Prochain signataire : {{ $currentSigner->user->name }}
                                                @else
                                                    En attente du prochain signataire
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <!-- Statut du document -->
                    <div class="document-status">
                        <div class="status-badge status-{{ $document->status }}">
                            <i class="fas fa-{{ $statusIcon }}"></i>
                            <span>{{ $statusText }}</span>
                        </div>
                        @if($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed())
                            <div class="completion-info">
                                <i class="fas fa-check-circle"></i>
                                <span>Document traité avec succès</span>
                            </div>
                            
                            <!-- Actions pour documents signés -->
                            <div class="signed-document-actions">
                                <div class="action-buttons">
                                    <a href="{{ route('documents.pending') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i>
                                        Retour à la liste
                                    </a>
                                    <a href="{{ route('storage.signed', ['filename' => basename($pdfUrl)]) }}" 
                                       class="btn btn-primary" target="_blank">
                                        <i class="fas fa-download"></i>
                                        Télécharger le PDF signé
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                </div>
            </div>
            

            <!-- Formulaire caché pour les actions -->
            @if(!isset($isReadOnly) || !$isReadOnly)
            <form id="processForm" action="{{ $formAction }}" method="POST" style="display: block;">
                @csrf
                
                <!-- Champs cachés -->
                <input type="hidden" name="action_type" id="action_type" value="{{ $defaultAction }}">
                <input type="hidden" name="signature_type" id="signature_type" value="png">
                <input type="hidden" name="paraphe_type" id="paraphe_type" value="png">
                <input type="hidden" name="cachet_type" id="cachet_type" value="png">
                <input type="hidden" name="live_signature_data" id="live_signature_data">
                <input type="hidden" name="live_paraphe_data" id="live_paraphe_data">
                <input type="hidden" name="live_cachet_data" id="live_cachet_data">
                <input type="hidden" name="signature_x" id="signature_x">
                <input type="hidden" name="signature_y" id="signature_y">
                <input type="hidden" name="paraphe_x" id="paraphe_x">
                <input type="hidden" name="paraphe_y" id="paraphe_y">
                <input type="hidden" name="cachet_x" id="cachet_x">
                <input type="hidden" name="cachet_y" id="cachet_y">
                <input type="hidden" name="signature_comment" id="signature_comment" value="">
                <input type="hidden" name="paraphe_comment" id="paraphe_comment" value="">
                <input type="hidden" name="cachet_comment" id="cachet_comment" value="">
            </form>
            @endif

            <!-- Zone d'affichage PDF -->
            <div class="modern-card">
                <!-- Interface de traitement simplifiée -->
                <div class="bg-white rounded-lg shadow-sm border sophisticated-border p-4 mb-6">
                    <!-- Barre d'outils principale -->
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-white">
                                <i class="fas fa-file-pdf text-white"></i>
                                <span class="font-semibold text-lg text-white">Document PDF</span>
                            </div>
                        </div>
                        
                        @if(isset($isReadOnly) && $isReadOnly)
                            <!-- Mode lecture seule - Version allégée -->
                            <div class="flex items-center justify-between bg-green-500 text-white px-6 py-3 rounded-lg shadow-sm">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-circle text-white"></i>
                                    <span class="font-medium">Document signé - Mode lecture seule</span>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('signatures.simple.index') }}" class="px-3 py-1 bg-white bg-opacity-20 rounded-md hover:bg-opacity-30 transition-colors text-sm">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Retour
                                    </a>
                                    <a href="{{ route('signatures.simple.show', $document) }}" class="px-3 py-1 bg-white bg-opacity-20 rounded-md hover:bg-opacity-30 transition-colors text-sm">
                                        <i class="fas fa-edit mr-1"></i>
                                        Édition
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Barre d'actions élégante -->
                            <div class="card mb-6 overflow-hidden">
                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 p-4">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                        <!-- Titre de la section -->
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-file-pdf text-white text-lg"></i>
                                            </div>
                                            <span class="font-bold text-lg text-white">Actions sur le document</span>
                                        </div>
                                        
                                        <!-- Boutons d'action -->
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <div class="flex flex-wrap gap-2">
                                                @if($allowSignature)
                                                    <button type="button" id="addSignatureBtn" 
                                                            class="group inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-primary-600 hover:text-primary-700 font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5" 
                                                            aria-label="Ajouter une signature au document"
                                                            aria-describedby="signature-help">
                                                        <i class="fas fa-pen-fancy group-hover:scale-110 transition-transform"></i>
                                                        <span>Signer</span>
                                                    </button>
                                                    <div id="signature-help" class="sr-only">
                                                        Cliquez pour ajouter une signature au document. Vous pourrez ensuite cliquer sur le document pour la positionner.
                                                    </div>
                                                @endif
                                                
                                                @if($allowParaphe)
                                                    <button type="button" id="addParapheBtn" 
                                                            class="group inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-primary-600 hover:text-primary-700 font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5"
                                                            aria-label="Ajouter un paraphe au document"
                                                            aria-describedby="paraphe-help">
                                                        <i class="fas fa-pen-nib group-hover:scale-110 transition-transform"></i>
                                                        <span>Parapher</span>
                                                    </button>
                                                    <div id="paraphe-help" class="sr-only">
                                                        Cliquez pour ajouter un paraphe au document. Vous pourrez ensuite cliquer sur le document pour le positionner.
                                                    </div>
                                                @endif
                                                
                                                @if($allowCachet)
                                                    <button type="button" id="addCachetBtn" 
                                                            class="group inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-secondary-600 hover:text-secondary-700 font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5"
                                                            aria-label="Ajouter un cachet au document"
                                                            aria-describedby="cachet-help">
                                                        <i class="fas fa-stamp group-hover:scale-110 transition-transform"></i>
                                                        <span>Cacheter</span>
                                                    </button>
                                                    <div id="cachet-help" class="sr-only">
                                                        Cliquez pour ajouter un cachet au document. Vous pourrez ensuite cliquer sur le document pour le positionner.
                                                    </div>
                                                @endif
                                                
                                                <button type="button" id="clearAllBtn" 
                                                        class="group inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-danger-50 text-danger-600 hover:text-danger-700 font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
                                                        aria-label="Effacer toutes les signatures, paraphes et cachets"
                                                        aria-describedby="clear-help">
                                                    <i class="fas fa-trash-alt group-hover:scale-110 transition-transform"></i>
                                                    <span>Effacer</span>
                                                </button>
                                                <div id="clear-help" class="sr-only">
                                                    Cliquez pour supprimer toutes les signatures, paraphes et cachets du document.
                                                </div>
                                            </div>
                                            
                                            <div class="flex gap-2">
                                                <button type="submit" form="processForm" id="submitBtn" 
                                                        class="group inline-flex items-center gap-2 px-6 py-2.5 bg-success-500 hover:bg-success-600 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                                        aria-label="Enregistrer le document avec les signatures et paraphes"
                                                        aria-describedby="submit-help">
                                                    <i class="fas fa-check group-hover:scale-110 transition-transform"></i>
                                                    <span>Enregistrer</span>
                                                </button>
                                                <div id="submit-help" class="sr-only">
                                                    Cliquez pour enregistrer le document avec toutes les signatures et paraphes ajoutés.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        @endif
                        
                        <!-- Contrôles PDF élégants -->
                        <div class="card mb-6">
                            <div class="bg-gradient-to-r from-gray-700 to-gray-800 p-4 rounded-lg">
                                <div class="flex flex-wrap items-center justify-center gap-6">
                                    <!-- Navigation de pages -->
                                    <div class="flex items-center gap-3">
                                        <button type="button" id="prevPageBtn" 
                                                class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-lg transition-all duration-300 hover:scale-110 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:scale-100" 
                                                title="Page précédente">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        
                                        <div class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-10 rounded-lg">
                                            <span class="text-white font-bold text-lg"><span id="currentPage">1</span></span>
                                            <span class="text-gray-300">/</span>
                                            <span class="text-gray-300 font-medium"><span id="totalPages">1</span></span>
                                        </div>
                                        
                                        <button type="button" id="nextPageBtn" 
                                                class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-lg transition-all duration-300 hover:scale-110 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:scale-100" 
                                                title="Page suivante">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Séparateur -->
                                    <div class="hidden sm:block w-px h-8 bg-white bg-opacity-20"></div>
                                    
                                    <!-- Contrôles de zoom -->
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="zoomOutBtn" 
                                                class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-lg transition-all duration-300 hover:scale-110" 
                                                title="Zoom arrière">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        
                                        <button type="button" id="resetZoomBtn" 
                                                class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-lg transition-all duration-300 hover:scale-110" 
                                                title="Réinitialiser le zoom">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        
                                        <button type="button" id="zoomInBtn" 
                                                class="w-10 h-10 flex items-center justify-center bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-lg transition-all duration-300 hover:scale-110" 
                                                title="Zoom avant">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                
                <!-- Zone PDF optimisée pour mobile -->
                <div class="pdf-container-mobile">
                    <div id="pdfViewer" class="pdf-viewer-mobile">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                            <div class="text-lg font-medium text-white mb-2">Chargement du PDF...</div>
                            <div class="text-sm text-white">Veuillez patienter</div>
                        </div>
                    </div>
                    
                    <!-- Indicateur de zoom mobile -->
                    <div class="mobile-zoom-indicator hidden" id="mobileZoomIndicator">
                        <span id="zoomLevel">100%</span>
                    </div>
                </div>
                
                <div class="flex justify-center items-center py-4 sophisticated-bg-secondary rounded-lg border sophisticated-border mt-4">
                    <div class="text-sm text-white font-medium">
                        <span id="pageInfo">Page 1 sur 1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="{{ asset('js/pdf-overlay-unified-module.js') }}"></script>

<!-- Enhanced Controls CSS -->
<link rel="stylesheet" href="{{ asset('css/enhanced-controls.css') }}">

<style>
/* Interface de traitement simplifiée */
.document-process-interface {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-bottom: 1px solid #e2e8f0;
    padding: 0;
}

.toolbar-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.toolbar-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.document-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #334155;
    font-size: 1.1rem;
}

.document-title i {
    color: #dc2626;
    font-size: 1.2rem;
}

.read-only-banner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    border-radius: 8px;
    font-weight: 500;
    border: 1px solid #f59e0b;
}

.read-only-banner i {
    color: #f59e0b;
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.primary-actions {
    border-right: 1px solid #e2e8f0;
    padding-right: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 44px;
    min-width: 44px;
    justify-content: center;
}

.action-btn.primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.action-btn.primary:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.action-btn.secondary {
    background: linear-gradient(135deg, #64748b, #475569);
    color: white;
    box-shadow: 0 2px 4px rgba(100, 116, 139, 0.2);
}

.action-btn.secondary:hover {
    background: linear-gradient(135deg, #475569, #334155);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(100, 116, 139, 0.3);
}

.action-btn.danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.action-btn.danger:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.action-btn.success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.action-btn.success:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.toolbar-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-left: auto;
}

.control-group {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* Indicateur de page */
.page-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    min-width: 60px;
    justify-content: center;
}

.page-separator {
    color: #9ca3af;
    font-weight: 400;
}

#currentPageNumber {
    color: var(--color-primary);
    font-weight: 700;
}

#totalPagesNumber {
    color: #6b7280;
    font-weight: 500;
}

.control-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 6px;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.control-btn:hover {
    background: #e2e8f0;
    color: #334155;
    transform: translateY(-1px);
}

.control-btn:active {
    transform: translateY(0);
    background: #cbd5e1;
}

.control-btn:disabled,
.control-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f1f5f9;
    color: #9ca3af;
}

.control-btn:disabled:hover,
.control-btn.disabled:hover {
    transform: none;
    background: #f1f5f9;
    color: #9ca3af;
}

/* Responsive pour l'interface de traitement */
@media (max-width: 768px) {
    .toolbar-main {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        padding: 1rem;
    }
    
    .toolbar-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .action-group {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .primary-actions {
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
        padding-right: 0;
        padding-bottom: 1rem;
    }
    
    .toolbar-controls {
        margin-left: 0;
        justify-content: center;
    }
    
    .action-btn {
        min-width: 120px;
        flex: 1;
    }
}

@media (max-width: 480px) {
    .toolbar-main {
        padding: 0.75rem;
    }
    
    .action-btn {
        padding: 0.625rem 1rem;
        font-size: 0.85rem;
        min-width: 100px;
    }
    
    .control-btn {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
}

/* Responsive pour le contenu des pages */
.pdf-container-mobile {
    width: 100%;
    max-width: 100%;
    overflow: visible;
    position: relative;
    background: #f8fafc;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    /* Amélioration du défilement mobile */
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.pdf-viewer-mobile {
    width: 100%;
    height: auto;
    min-height: 400px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 1rem;
    background: #ffffff;
    border-radius: 8px;
    position: relative;
    /* Amélioration du défilement mobile */
    overflow: visible;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.pdf-viewer-mobile canvas {
    max-width: none !important;
    height: auto !important;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.pdf-viewer-mobile canvas:hover {
    transform: scale(1.02);
}

/* Indicateur de zoom mobile */
.mobile-zoom-indicator {
    position: fixed;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

.mobile-zoom-indicator.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Responsive pour les différentes tailles d'écran */
@media (max-width: 1200px) {
    .pdf-viewer-mobile {
        padding: 0.75rem;
    }
    
    .pdf-viewer-mobile canvas {
        max-width: none;
    }
}

@media (max-width: 768px) {
    .pdf-container-mobile {
        margin: 0 -1rem;
        border-radius: 0;
    }
    
    .pdf-viewer-mobile {
        padding: 0.5rem;
        min-height: 300px;
    }
    
    .pdf-viewer-mobile canvas {
        max-width: none;
        border-radius: 0;
    }
    
    .mobile-zoom-indicator {
        right: 0.5rem;
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
}

@media (max-width: 480px) {
    .pdf-viewer-mobile {
        padding: 0.25rem;
        min-height: 250px;
    }
    
    .pdf-viewer-mobile canvas {
        max-width: none;
    }
    
    .mobile-zoom-indicator {
        right: 0.25rem;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Amélioration de l'expérience tactile */
@media (hover: none) and (pointer: coarse) {
    .pdf-viewer-mobile canvas {
        /* Permettre le défilement naturel sur mobile */
        touch-action: manipulation;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        /* Améliorer la réactivité tactile */
        -webkit-tap-highlight-color: transparent;
        -webkit-touch-callout: none;
    }
    
    .pdf-viewer-mobile canvas:active {
        transform: scale(1.02);
        transition: transform 0.1s ease;
    }
    
    /* Améliorer le défilement du conteneur */
    .pdf-container-mobile {
        touch-action: pan-x pan-y;
        -webkit-overflow-scrolling: touch;
    }
}

/* Support pour les écrans haute résolution */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .pdf-viewer-mobile canvas {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}

/* Amélioration de l'accessibilité */
@media (prefers-reduced-motion: reduce) {
    .pdf-viewer-mobile canvas {
        transition: none;
    }
    
    .pdf-viewer-mobile canvas:hover {
        transform: none;
    }
    
    .mobile-zoom-indicator {
        transition: none;
    }
}

/* Mode sombre pour les pages */
@media (prefers-color-scheme: dark) {
    .pdf-container-mobile {
        background: #1e293b;
    }
    
    .pdf-viewer-mobile {
        background: #0f172a;
    }
    
    .mobile-zoom-indicator {
        background: rgba(255, 255, 255, 0.9);
        color: #1e293b;
    }
}

/* Architecture d'information simplifiée */
.document-info-compact {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.info-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.info-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-label i {
    color: #3b82f6;
    font-size: 0.9rem;
    width: 16px;
    text-align: center;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    word-break: break-word;
}

.description-section {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #10b981;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.description-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.description-label i {
    color: #10b981;
    font-size: 0.9rem;
}

.description-text {
    color: #374151;
    line-height: 1.6;
    font-size: 0.95rem;
}

/* Responsive pour l'architecture d'information */
@media (max-width: 768px) {
    .document-info-compact {
        padding: 1rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .info-item {
        padding: 0.75rem;
    }
    
    .description-section {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .info-label {
        font-size: 0.8rem;
    }
    
    .info-value {
        font-size: 0.9rem;
    }
    
    .description-text {
        font-size: 0.9rem;
    }
}

/* Expérience mobile optimisée */
.pdf-container-mobile {
    position: relative;
    background: #f8fafc;
    border-radius: 12px;
    overflow: visible;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    /* Améliorations pour le défilement mobile */
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    /* Prévenir les conflits de défilement */
    contain: layout style paint;
}

.pdf-viewer-mobile {
    position: relative;
    width: 100%;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 8px;
    margin: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    /* Améliorations pour le défilement mobile */
    overflow: visible;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    /* Optimiser les performances de rendu */
    will-change: transform;
    transform: translateZ(0);
}

.pdf-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    text-align: center;
}

.loading-spinner {
    font-size: 2rem;
    color: #3b82f6;
}

.loading-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.loading-text span {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
}

.loading-text small {
    font-size: 0.9rem;
    color: #6b7280;
}

.mobile-zoom-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 10;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.mobile-zoom-indicator.hidden {
    opacity: 0;
    transform: translateY(-10px);
}

/* Améliorations tactiles pour mobile */
@media (max-width: 768px) {
    .pdf-viewer-mobile {
        min-height: 50vh;
        margin: 0.5rem;
    }
    
    .pdf-container-mobile {
        border-radius: 8px;
    }
    
    .action-btn {
        min-height: 48px;
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
        touch-action: manipulation;
    }
    
    .control-btn {
        width: 44px;
        height: 44px;
        min-height: 44px;
        min-width: 44px;
    }
    
    .toolbar-main {
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .toolbar-actions {
        gap: 0.75rem;
    }
    
    .action-group {
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .pdf-viewer-mobile {
        min-height: 45vh;
        margin: 0.25rem;
    }
    
    .action-btn {
        min-width: 100px;
        flex: 1;
        max-width: 150px;
    }
    
    .toolbar-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-group {
        width: 100%;
        justify-content: center;
    }
    
    .toolbar-controls {
        width: 100%;
        justify-content: center;
    }
    
    .control-group {
        width: 100%;
        justify-content: center;
    }
}

/* Styles améliorés pour l'affichage du document */
.document-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    border: 1px solid #dee2e6;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.detail-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.detail-item label {
    font-weight: 600;
    color: #495057;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.detail-item label i {
    color: #007bff;
    font-size: 0.9rem;
    width: 16px;
    text-align: center;
}

.detail-value {
    color: #2c3e50;
    font-size: 1rem;
    font-weight: 500;
    word-break: break-word;
}

.detail-value.filename {
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
    border: 1px solid #e9ecef;
}

/* Statut du document */
.document-status {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.status-pending {
    background: linear-gradient(135deg, #ffc107, #ff8f00);
    color: white;
}

.status-badge.status-signed {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.status-badge.status-paraphed {
    background: linear-gradient(135deg, #17a2b8, #6f42c1);
    color: white;
}

.status-badge.status-signed_and_paraphed {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
    color: white;
}

.completion-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9rem;
}

.completion-info i {
    color: #28a745;
    font-size: 1.1rem;
}

/* Actions pour documents signés */
.signed-document-actions {
    margin-top: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-buttons .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    min-height: 48px;
    min-width: 120px;
    justify-content: center;
    text-transform: none;
    letter-spacing: 0.5px;
}

.action-buttons .btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.action-buttons .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.action-buttons .btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.action-buttons .btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

/* Message de succès */
.success-message {
    margin-bottom: 24px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border-left: 4px solid #28a745;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
}

.alert-content i {
    font-size: 2rem;
    color: #28a745;
    flex-shrink: 0;
}

.alert-text h4 {
    margin: 0 0 8px 0;
    color: #155724;
    font-size: 1.25rem;
    font-weight: 600;
}

.alert-text p {
    margin: 0;
    color: #155724;
    font-size: 1rem;
    opacity: 0.9;
}

/* Responsive pour les détails */
@media (max-width: 768px) {
    .document-details {
        grid-template-columns: 1fr;
        padding: 16px;
    }
    
    .detail-item {
        padding: 12px;
    }
    
    .document-status {
        padding: 12px;
    }
}

.pdf-header {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.pdf-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    order: 1;
}

.pdf-controls-main {
    order: 2;
    width: 100%;
}

.pdf-controls-secondary {
    order: 3;
    position: relative;
    width: 100%;
}

.main-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
}

.mobile-controls-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1.2rem;
    font-weight: 600;
    min-width: 48px;
}

.mobile-controls-toggle:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

.pdf-controls-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    padding: 16px;
    min-width: 280px;
    z-index: 50;
    display: none;
    border: 1px solid #e2e8f0;
}

.pdf-controls-menu.show {
    display: block;
}

.controls-group {
    margin-bottom: 16px;
}

.controls-group:last-child {
    margin-bottom: 0;
}

.controls-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.controls-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.controls-buttons .btn-eps-secondary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 0.9rem;
    min-height: 48px;
    justify-content: center;
    min-width: 120px;
    font-weight: 600;
    border-radius: 8px;
    text-transform: none;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.controls-buttons .btn-eps-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.controls-buttons .btn-eps-secondary:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.controls-buttons .btn-eps-secondary i {
    font-size: 0.9rem;
}

/* Boutons EPS harmonisés */
.btn-eps-primary, .btn-eps-secondary, .btn-eps-accent {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 48px;
    justify-content: center;
    min-width: 120px;
    text-transform: none;
    letter-spacing: 0.5px;
}

.btn-eps-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.btn-eps-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-eps-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.2);
}

.btn-eps-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

.btn-eps-accent {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

.btn-eps-accent:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.pdf-container {
    padding: 24px;
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
}

.pdf-viewer {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    min-height: 600px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

.pdf-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 600px;
    color: #6c757d;
    gap: 16px;
}

.pdf-loading i {
    font-size: 2rem;
}

.pdf-footer {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.pdf-info {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Mode lecture seule */
.read-only-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
}

.read-only-notice i {
    font-size: 1.1rem;
}

/* Responsive amélioré */
@media (min-width: 768px) {
    .pdf-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .pdf-title {
        order: 1;
    }
    
    .pdf-controls-main {
        order: 2;
        width: auto;
    }
    
    .pdf-controls-secondary {
        order: 3;
        width: auto;
    }
    
    .mobile-controls-toggle {
        display: none;
    }
    
    .pdf-controls-menu {
        position: static;
        display: flex;
        gap: 16px;
        background: transparent;
        box-shadow: none;
        padding: 0;
        min-width: auto;
        border: none;
    }
    
    .controls-group {
        margin-bottom: 0;
    }
    
    .controls-label {
        display: none;
    }
    
    .controls-buttons {
        display: flex;
        gap: 8px;
    }
    
    .controls-buttons .btn-eps-secondary {
        padding: 10px 16px;
        font-size: 0.85rem;
        min-height: 44px;
        min-width: 100px;
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }
}

@media (max-width: 768px) {
    .document-details {
        grid-template-columns: 1fr;
        padding: 12px;
        gap: 12px;
    }
    
    .detail-item {
        padding: 12px;
    }
    
    .pdf-header {
        padding: 12px 16px;
    }
    
    .pdf-title {
        font-size: 1rem;
    }
    
    .page-indicator {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        min-width: 50px;
    }
    
    .page-separator {
        font-size: 0.7rem;
    }
    
    .control-btn {
        min-height: 32px;
        min-width: 32px;
        font-size: 0.75rem;
    }
    
    .main-actions {
        gap: 6px;
    }
    
    .btn-eps-primary, .btn-eps-secondary, .btn-eps-accent {
        padding: 10px 16px;
        font-size: 0.85rem;
        min-height: 44px;
        min-width: 100px;
    }
    
    .pdf-container {
        padding: 16px;
    }
    
    .pdf-viewer {
        min-height: 400px;
    }
    
    .pdf-loading {
        height: 400px;
    }
}

@media (max-width: 640px) {
    .document-details {
        padding: 8px;
        gap: 8px;
    }
    
    .detail-item {
        padding: 10px;
    }
    
    .detail-item label {
        font-size: 0.8rem;
    }
    
    .detail-value {
        font-size: 0.9rem;
    }
    
    .pdf-header {
        padding: 10px 12px;
    }
    
    .main-actions {
        gap: 4px;
    }
    
    .btn-eps-primary, .btn-eps-secondary, .btn-eps-accent {
        padding: 8px 14px;
        font-size: 0.8rem;
        min-height: 40px;
        min-width: 90px;
    }
    
    .pdf-container {
        padding: 12px;
    }
    
    .pdf-viewer {
        min-height: 350px;
    }
    
    .pdf-loading {
        height: 350px;
    }
    
    .pdf-controls-menu {
        min-width: 260px;
        padding: 12px;
    }
    
    .controls-buttons {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .controls-buttons .btn-eps-secondary {
        padding: 10px 14px;
        font-size: 0.85rem;
        min-height: 44px;
        min-width: 90px;
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }
}

@media (max-width: 480px) {
    .pdf-header {
        padding: 8px 10px;
    }
    
    .pdf-title {
        font-size: 0.9rem;
    }
    
    .main-actions {
        flex-direction: column;
        gap: 6px;
    }
    
    .btn-eps-primary, .btn-eps-secondary, .btn-eps-accent {
        width: 100%;
        justify-content: center;
        padding: 12px 16px;
        min-height: 48px;
        font-size: 0.9rem;
    }
    
    .controls-buttons .btn-eps-secondary {
        width: 100%;
        justify-content: center;
        padding: 12px 16px;
        min-height: 48px;
        font-size: 0.9rem;
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }
    
    .pdf-container {
        padding: 8px;
    }
    
    .pdf-viewer {
        min-height: 300px;
    }
    
    .pdf-loading {
        height: 300px;
    }
    
    /* Optimisations pour la signature sur mobile */
    .pdf-viewer canvas {
        /* Mode signature : permettre le zoom et le défilement */
        touch-action: pan-x pan-y pinch-zoom !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        -webkit-touch-callout: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }
    
    /* Mode lecture : permettre le défilement et le zoom */
    .pdf-viewer-mobile:not(.signature-mode) canvas {
        touch-action: pan-x pan-y pinch-zoom !important;
        -webkit-overflow-scrolling: touch !important;
    }
    
    /* Amélioration de la précision tactile */
    .pdf-viewer canvas:active {
        cursor: crosshair;
    }
    
    /* Optimisations tactiles pour les boutons */
    .btn-eps-primary, .btn-eps-secondary, .btn-eps-accent {
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    .btn-eps-primary:active, .btn-eps-secondary:active, .btn-eps-accent:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }
}

/* Gestion des modes de défilement mobile */
.pdf-container-mobile.scroll-mode {
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: auto;
}

.pdf-container-mobile.signature-mode {
    overflow: auto;
    touch-action: pan-x pan-y pinch-zoom;
    -webkit-overflow-scrolling: touch;
}

.pdf-container-mobile.signature-mode .pdf-viewer-mobile canvas {
    touch-action: pan-x pan-y pinch-zoom !important;
    pointer-events: auto;
}

.pdf-container-mobile.scroll-mode .pdf-viewer-mobile canvas {
    touch-action: pan-x pan-y pinch-zoom !important;
    pointer-events: auto;
}

/* Amélioration du défilement pour les petits écrans */
@media (max-width: 480px) {
    .pdf-container-mobile {
        margin: 0;
        border-radius: 0;
        min-height: 100vh;
    }
    
    .pdf-viewer-mobile {
        margin: 0;
        border-radius: 0;
        min-height: 50vh;
        padding: 0.5rem;
    }
    
    /* Optimiser le défilement vertical */
    body {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
    
    /* Prévenir le zoom automatique sur les champs de saisie */
    input, textarea, select {
        font-size: 16px !important;
        transform: translateZ(0);
    }
}
</style>

<script>
// Configuration PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Prévenir les instances multiples d'Alpine.js
if (window.Alpine) {
    console.warn('⚠️ Alpine.js déjà chargé, évitement des conflits...');
    // Ne pas réinitialiser Alpine si déjà chargé
    window.Alpine = window.Alpine;
}

// Initialiser le module unifié simplifié
document.addEventListener('DOMContentLoaded', function() {
    const config = {
        pdfUrl: '{{ $displayPdfUrl ?? $pdfUrl }}',
        signatureUrl: '{{ $signatureUrl }}',
        parapheUrl: '{{ $parapheUrl }}',
        cachetUrl: '{{ $cachetUrl ?? "/signatures/user-cachet" }}',
        uploadUrl: '{{ $uploadUrl ?? "/documents/upload-signed-pdf" }}',
        redirectUrl: '{{ $redirectUrl ?? "/documents/" . $document->id . "/process/view" }}',
        documentId: {{ $document->id }},
        containerId: 'pdfViewer',
        processFormId: 'processForm',
        actionTypeInputId: 'action_type',
        signatureTypeInputId: 'signature_type',
        parapheTypeInputId: 'paraphe_type',
        cachetTypeInputId: 'cachet_type',
        liveSignatureDataInputId: 'live_signature_data',
        liveParapheDataInputId: 'live_paraphe_data',
        liveCachetDataInputId: 'live_cachet_data',
        signatureXInputId: 'signature_x',
        signatureYInputId: 'signature_y',
        parapheXInputId: 'paraphe_x',
        parapheYInputId: 'paraphe_y',
        cachetXInputId: 'cachet_x',
        cachetYInputId: 'cachet_y',
        addSignatureBtnId: 'addSignatureBtn',
        addParapheBtnId: 'addParapheBtn',
        addCachetBtnId: 'addCachetBtn',
        addSignAndParapheBtnId: 'addSignAndParapheBtn',
        clearAllBtnId: 'clearAllBtn',
        submitBtnId: 'submitBtn',
        zoomInBtnId: 'zoomInBtn',
        zoomOutBtnId: 'zoomOutBtn',
        resetZoomBtnId: 'resetZoomBtn',
        autoFitBtnId: 'autoFitBtn',
        a4FitBtnId: 'a4FitBtn',
        completeRenderBtnId: 'completeRenderBtn',
        prevPageBtnId: 'prevPageBtn',
        nextPageBtnId: 'nextPageBtn',
        pageInfoId: 'pageInfo',
        pdfContainerId: 'pdfViewer',
        qualitySelectId: 'qualitySelect',
        allowSignature: {{ $allowSignature ? 'true' : 'false' }},
        allowParaphe: {{ $allowParaphe ? 'true' : 'false' }},
        allowCachet: {{ $allowCachet ? 'true' : 'false' }},
        allowBoth: {{ $allowBoth ? 'true' : 'false' }},
        allowAll: {{ $allowAll ? 'true' : 'false' }},
        isReadOnly: {{ isset($isReadOnly) && $isReadOnly ? 'true' : 'false' }}
    };
    
    // Log de debug pour la configuration
    console.log('🔧 Configuration uploadUrl:', config.uploadUrl);
    console.log('🔧 Configuration redirectUrl:', config.redirectUrl);

    // Vérifier que la classe est disponible
    if (typeof PDFOverlayUnifiedModule === 'undefined') {
        console.error('❌ PDFOverlayUnifiedModule non trouvé. Vérifiez que le script est chargé.');
        return;
    }
    
    const unifiedModule = new PDFOverlayUnifiedModule(config);
    unifiedModule.init();
    
    // Gestion du menu mobile des contrôles
    const mobileControlsToggle = document.getElementById('mobileControlsToggle');
    const pdfControlsMenu = document.getElementById('pdfControlsMenu');
    
    if (mobileControlsToggle && pdfControlsMenu) {
        mobileControlsToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            pdfControlsMenu.classList.toggle('show');
            
            // Changer l'icône
            const icon = mobileControlsToggle.querySelector('i');
            if (pdfControlsMenu.classList.contains('show')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-ellipsis-v';
            }
        });
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (!pdfControlsMenu.contains(event.target) && !mobileControlsToggle.contains(event.target)) {
                pdfControlsMenu.classList.remove('show');
                const icon = mobileControlsToggle.querySelector('i');
                icon.className = 'fas fa-ellipsis-v';
            }
        });
        
        // Fermer le menu en cliquant sur un bouton
        const controlButtons = pdfControlsMenu.querySelectorAll('button');
        controlButtons.forEach(button => {
            button.addEventListener('click', function() {
                pdfControlsMenu.classList.remove('show');
                const icon = mobileControlsToggle.querySelector('i');
                icon.className = 'fas fa-ellipsis-v';
            });
        });
        
        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                pdfControlsMenu.classList.remove('show');
                const icon = mobileControlsToggle.querySelector('i');
                icon.className = 'fas fa-ellipsis-v';
            }
        });
    }
});

// Gestion de l'indicateur de zoom mobile
document.addEventListener('DOMContentLoaded', function() {
    const mobileZoomIndicator = document.getElementById('mobileZoomIndicator');
    const zoomLevel = document.getElementById('zoomLevel');
    
    if (mobileZoomIndicator && zoomLevel) {
        // Afficher l'indicateur de zoom temporairement lors des changements
        function showZoomIndicator(level) {
            zoomLevel.textContent = Math.round(level) + '%';
            mobileZoomIndicator.classList.remove('hidden');
            
            // Masquer après 2 secondes
            setTimeout(() => {
                mobileZoomIndicator.classList.add('hidden');
            }, 2000);
        }
        
        // Écouter les événements de zoom du module PDF
        document.addEventListener('zoomChanged', function(e) {
            showZoomIndicator(e.detail.zoomLevel);
        });
        
        // Masquer l'indicateur au début
        mobileZoomIndicator.classList.add('hidden');
    }
});

// Gestion de la navigation entre pages
document.addEventListener('DOMContentLoaded', function() {
    const currentPageNumber = document.getElementById('currentPageNumber');
    const totalPagesNumber = document.getElementById('totalPagesNumber');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const firstPageBtn = document.getElementById('firstPageBtn');
    const lastPageBtn = document.getElementById('lastPageBtn');
    
    // Variables pour la navigation
    let currentPage = 1;
    let totalPages = 1;
    
    // Fonction pour mettre à jour l'indicateur de page
    function updatePageIndicator() {
        if (currentPageNumber) {
            currentPageNumber.textContent = currentPage;
        }
        if (totalPagesNumber) {
            totalPagesNumber.textContent = totalPages;
        }
        
        // Mettre à jour l'état des boutons
        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage <= 1;
            prevPageBtn.classList.toggle('disabled', currentPage <= 1);
        }
        if (nextPageBtn) {
            nextPageBtn.disabled = currentPage >= totalPages;
            nextPageBtn.classList.toggle('disabled', currentPage >= totalPages);
        }
        if (firstPageBtn) {
            firstPageBtn.disabled = currentPage <= 1;
            firstPageBtn.classList.toggle('disabled', currentPage <= 1);
        }
        if (lastPageBtn) {
            lastPageBtn.disabled = currentPage >= totalPages;
            lastPageBtn.classList.toggle('disabled', currentPage >= totalPages);
        }
    }
    
    // Fonction pour aller à une page spécifique
    function goToPage(pageNumber) {
        if (pageNumber >= 1 && pageNumber <= totalPages) {
            currentPage = pageNumber;
            updatePageIndicator();
            
            // Déclencher l'événement de changement de page
            document.dispatchEvent(new CustomEvent('pageChanged', {
                detail: { currentPage, totalPages }
            }));
            
            // Notification pour les lecteurs d'écran
            if (window.NotificationSystem) {
                window.NotificationSystem.info(`Page ${currentPage} sur ${totalPages}`, {
                    autoClose: true,
                    duration: 2000
                });
            }
        }
    }
    
    // Événements des boutons de navigation
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', async () => {
            if (currentPage > 1) {
                // Utiliser le module PDF pour naviguer
                if (window.pdfOverlayModule) {
                    await window.pdfOverlayModule.goToPreviousPage();
                } else {
                    goToPage(currentPage - 1);
                }
            }
        });
    }
    
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', async () => {
            if (currentPage < totalPages) {
                // Utiliser le module PDF pour naviguer
                if (window.pdfOverlayModule) {
                    await window.pdfOverlayModule.goToNextPage();
                } else {
                    goToPage(currentPage + 1);
                }
            }
        });
    }
    
    if (firstPageBtn) {
        firstPageBtn.addEventListener('click', async () => {
            // Utiliser le module PDF pour naviguer
            if (window.pdfOverlayModule) {
                await window.pdfOverlayModule.goToFirstPage();
            } else {
                goToPage(1);
            }
        });
    }
    
    if (lastPageBtn) {
        lastPageBtn.addEventListener('click', async () => {
            // Utiliser le module PDF pour naviguer
            if (window.pdfOverlayModule) {
                await window.pdfOverlayModule.goToLastPage();
            } else {
                goToPage(totalPages);
            }
        });
    }
    
    // Navigation par clavier
    document.addEventListener('keydown', function(e) {
        // Éviter les conflits avec les champs de saisie
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                if (currentPage > 1) {
                    if (window.pdfOverlayModule) {
                        window.pdfOverlayModule.goToPreviousPage();
                    } else {
                        goToPage(currentPage - 1);
                    }
                }
                break;
            case 'ArrowRight':
                e.preventDefault();
                if (currentPage < totalPages) {
                    if (window.pdfOverlayModule) {
                        window.pdfOverlayModule.goToNextPage();
                    } else {
                        goToPage(currentPage + 1);
                    }
                }
                break;
            case 'Home':
                e.preventDefault();
                if (window.pdfOverlayModule) {
                    window.pdfOverlayModule.goToFirstPage();
                } else {
                    goToPage(1);
                }
                break;
            case 'End':
                e.preventDefault();
                if (window.pdfOverlayModule) {
                    window.pdfOverlayModule.goToLastPage();
                } else {
                    goToPage(totalPages);
                }
                break;
        }
    });
    
    // Écouter les événements du module PDF pour mettre à jour le nombre total de pages
    document.addEventListener('pdfLoaded', function(e) {
        if (e.detail && e.detail.totalPages) {
            totalPages = e.detail.totalPages;
            updatePageIndicator();
        }
    });
    
    // Écouter les changements de page du module PDF
    document.addEventListener('pageChanged', function(e) {
        if (e.detail && e.detail.currentPage) {
            currentPage = e.detail.currentPage;
            updatePageIndicator();
        }
    });
    
    // Initialiser l'indicateur
    updatePageIndicator();
});

// Gestion des modes de défilement mobile
document.addEventListener('DOMContentLoaded', function() {
    const pdfContainer = document.querySelector('.pdf-container-mobile');
    const pdfViewer = document.querySelector('.pdf-viewer-mobile');
    
    if (pdfContainer && pdfViewer) {
        // Mode par défaut : défilement
        pdfContainer.classList.add('scroll-mode');
        
        // Détecter les interactions de signature
        const signatureBtn = document.getElementById('addSignatureBtn');
        const parapheBtn = document.getElementById('addParapheBtn');
        const cachetBtn = document.getElementById('addCachetBtn');
        
        function enableSignatureMode() {
            pdfContainer.classList.remove('scroll-mode');
            pdfContainer.classList.add('signature-mode');
            // Ne PAS désactiver le défilement du body pour permettre le zoom
            // document.body.style.overflow = 'hidden'; // Commenté pour permettre le zoom
        }
        
        function enableScrollMode() {
            console.log('🔄 Réactivation du mode défilement...');
            pdfContainer.classList.remove('signature-mode');
            pdfContainer.classList.add('scroll-mode');
            // Réactiver le défilement du body
            document.body.style.overflow = '';
            // Forcer la réactivation des propriétés CSS pour permettre zoom et défilement
            pdfContainer.style.touchAction = 'pan-x pan-y pinch-zoom';
            pdfContainer.style.overflow = 'auto';
            // Réactiver le défilement et zoom sur le canvas
            const canvas = pdfContainer.querySelector('canvas');
            if (canvas) {
                canvas.style.touchAction = 'pan-x pan-y pinch-zoom';
                canvas.style.pointerEvents = 'auto';
            }
            console.log('✅ Mode défilement et zoom réactivé');
        }
        
        // Événements pour activer le mode signature
        if (signatureBtn) {
            signatureBtn.addEventListener('click', enableSignatureMode);
        }
        if (parapheBtn) {
            parapheBtn.addEventListener('click', enableSignatureMode);
        }
        if (cachetBtn) {
            cachetBtn.addEventListener('click', enableSignatureMode);
        }
        
        // Événements pour revenir au mode défilement
        const clearBtn = document.getElementById('clearAllBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', enableScrollMode);
        }
        
        // Détecter la fin de la signature via les événements du module PDF
        document.addEventListener('signatureCompleted', function(e) {
            console.log('🎉 Événement signatureCompleted reçu:', e.detail);
            enableScrollMode();
        });
        document.addEventListener('parapheCompleted', function(e) {
            console.log('🎉 Événement parapheCompleted reçu:', e.detail);
            enableScrollMode();
        });
        document.addEventListener('cachetCompleted', function(e) {
            console.log('🎉 Événement cachetCompleted reçu:', e.detail);
            enableScrollMode();
        });
        
        // Approche simplifiée : ne jamais bloquer complètement le défilement
        // Le mode signature ne bloque plus le défilement, il ajoute juste des fonctionnalités
        const originalEnableSignatureMode = enableSignatureMode;
        enableSignatureMode = function() {
            originalEnableSignatureMode();
            console.log('📱 Mode signature activé - défilement et zoom toujours disponibles');
        };
        
        // Fallback supplémentaire : détecter la création d'éléments
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Vérifier si un élément de signature/paraphe/cachet a été ajouté
                    const addedNodes = Array.from(mutation.addedNodes);
                    const hasSignatureElement = addedNodes.some(node => 
                        node.nodeType === 1 && (
                            node.classList.contains('signature-element') ||
                            node.classList.contains('paraphe-element') ||
                            node.classList.contains('cachet-element') ||
                            node.querySelector('.signature-element, .paraphe-element, .cachet-element')
                        )
                    );
                    
                    if (hasSignatureElement && pdfContainer.classList.contains('signature-mode')) {
                        console.log('🔍 Élément de signature détecté, réactivation du mode défilement');
                        setTimeout(() => enableScrollMode(), 1000);
                    }
                }
            });
        });
        
        // Observer les changements dans le conteneur PDF
        if (pdfContainer) {
            observer.observe(pdfContainer, { childList: true, subtree: true });
        }
        
        // Bouton de secours pour forcer la réactivation (visible uniquement en mode signature)
        const createEmergencyButton = () => {
            const emergencyBtn = document.createElement('button');
            emergencyBtn.id = 'emergency-scroll-btn';
            emergencyBtn.innerHTML = '🔄 Réactiver le défilement';
            emergencyBtn.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 9999;
                background: #dc3545;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 5px;
                font-size: 12px;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                display: none;
            `;
            emergencyBtn.addEventListener('click', () => {
                console.log('🚨 Bouton d\'urgence activé');
                enableScrollMode();
                emergencyBtn.style.display = 'none';
            });
            document.body.appendChild(emergencyBtn);
            return emergencyBtn;
        };
        
        const emergencyBtn = createEmergencyButton();
        
        // Afficher le bouton d'urgence en mode signature
        const originalEnableSignatureMode2 = enableSignatureMode;
        enableSignatureMode = function() {
            originalEnableSignatureMode2();
            emergencyBtn.style.display = 'block';
        };
        
        const originalEnableScrollMode = enableScrollMode;
        enableScrollMode = function() {
            originalEnableScrollMode();
            emergencyBtn.style.display = 'none';
        };
        
        // Gestion du redimensionnement
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                // Sur desktop, toujours en mode défilement
                enableScrollMode();
            }
        });
        
        // Gestion des gestes tactiles pour améliorer l'expérience
        let touchStartY = 0;
        let touchStartX = 0;
        
        pdfViewer.addEventListener('touchstart', function(e) {
            if (pdfContainer.classList.contains('signature-mode')) {
                return; // En mode signature, laisser le module gérer
            }
            
            touchStartY = e.touches[0].clientY;
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        
        pdfViewer.addEventListener('touchmove', function(e) {
            // Permettre le défilement et le zoom en permanence
            // Ne plus bloquer les gestes tactiles
            if (pdfContainer.classList.contains('signature-mode')) {
                // En mode signature, permettre quand même le défilement et le zoom
                // Ne pas empêcher les gestes naturels
                return;
            }
            
            // Permettre le défilement naturel
            // e.preventDefault(); // Commenté pour permettre le défilement
        }, { passive: true });
    }
});
</script>
@endsection
