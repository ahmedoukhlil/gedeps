@extends('layouts.app')

@section('title', 'Documents en Attente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation sophistiquée -->
    <nav class="sophisticated-breadcrumb mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li>
                <a href="{{ route('home') }}" class="sophisticated-breadcrumb-link">
                    <i class="fas fa-home"></i>
                    <span class="hidden sm:inline ml-1">Accueil</span>
                </a>
            </li>
            <li class="sophisticated-breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li class="sophisticated-breadcrumb-current">
                <i class="fas fa-clock"></i>
                <span class="hidden sm:inline ml-1">Documents en Attente</span>
            </li>
        </ol>
    </nav>
    <!-- En-tête moderne avec navigation -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                @if(auth()->user()->isSignataire())
                    <h1 class="text-3xl font-bold sophisticated-heading flex items-center gap-3">
                        <i class="fas fa-pen-fancy text-primary"></i>
                        Documents à Signer
                    </h1>
                    <p class="sophisticated-body mt-2">Voici les documents qui vous ont été assignés pour signature</p>
                @elseif(auth()->user()->isAdmin())
                    <h1 class="text-3xl font-bold sophisticated-heading flex items-center gap-3">
                        <i class="fas fa-clock text-primary"></i>
                        Documents en Attente de Signature
                    </h1>
                    <p class="sophisticated-body mt-2">Vue d'ensemble de tous les documents en attente de signature</p>
                @else
                    <h1 class="text-3xl font-bold sophisticated-heading flex items-center gap-3">
                        <i class="fas fa-upload text-primary"></i>
                        Mes Documents Soumis
                    </h1>
                    <p class="sophisticated-body mt-2">Documents que vous avez soumis et qui sont en attente de signature</p>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('documents.history') }}" class="btn btn-secondary">
                    <i class="fas fa-history"></i>
                    Historique
                </a>
                @if(auth()->user()->isAgent())
                    <a href="{{ route('documents.upload') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Nouveau Document
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Statistiques rapides -->
        @if($documents->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="stat-card stat-total">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ $documents->count() }}</h3>
                    <p class="stat-label">Documents en attente</p>
                </div>
            </div>
            
            <div class="stat-card stat-pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ $documents->where('status', 'pending')->count() }}</h3>
                    <p class="stat-label">En attente de signature</p>
                </div>
            </div>
            
            <div class="stat-card stat-urgent">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ $documents->where('created_at', '<', now()->subDays(7))->count() }}</h3>
                    <p class="stat-label">Documents urgents</p>
                </div>
            </div>
        </div>
        @endif
    </div>
        
    @if($documents->count() > 0)
        <!-- Tableau des documents avec vue moderne -->
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="sophisticated-card-header-title flex items-center gap-2">
                        <i class="fas fa-list text-primary"></i>
                        @if(auth()->user()->isSignataire())
                            Documents à signer ({{ $documents->count() }})
                        @elseif(auth()->user()->isAdmin())
                            Tous les documents en attente ({{ $documents->count() }})
                        @else
                            Mes documents soumis ({{ $documents->count() }})
                        @endif
                    </h2>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm sophisticated-body">Trier par:</span>
                            <select id="sort_by" class="form-control form-control-sm">
                                <option value="created_at_desc">Date (récent)</option>
                                <option value="created_at_asc">Date (ancien)</option>
                                <option value="filename_asc">Nom (A-Z)</option>
                                <option value="filename_desc">Nom (Z-A)</option>
                                <option value="file_size_desc">Taille (grand)</option>
                                <option value="file_size_asc">Taille (petit)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Version desktop -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Type</th>
                            <th>
                                @if(auth()->user()->isSignataire())
                                    Soumis par
                                @elseif(auth()->user()->isAdmin())
                                    Soumis par / Assigné à
                                @else
                                    Assigné à
                                @endif
                            </th>
                            <th>Date de soumission</th>
                            <th>Priorité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            @php
                                $isUrgent = $document->created_at < now()->subDays(7);
                                $daysSinceCreated = $document->created_at->diffInDays(now());
                            @endphp
                            <tr class="document-row {{ $isUrgent ? 'urgent-row' : '' }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-pdf text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium sophisticated-heading">{{ $document->document_name ?? $document->filename_original }}</div>
                                            <div class="text-sm sophisticated-caption">{{ number_format($document->file_size / 1024, 1) }} KB</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status status-pending">
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if(auth()->user()->isSignataire())
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user sophisticated-body text-sm"></i>
                                            </div>
                                            <span class="sophisticated-body">{{ $document->uploader->name }}</span>
                                        </div>
                                    @elseif(auth()->user()->isAdmin())
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-upload text-white text-xs"></i>
                                                </div>
                                                <span class="text-sm sophisticated-body">{{ $document->uploader->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-user-check text-white text-xs"></i>
                                                </div>
                                                <span class="text-xs sophisticated-caption">→ {{ $document->signer->name }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user sophisticated-body text-sm"></i>
                                            </div>
                                            <span class="sophisticated-body">{{ $document->signer->name }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="sophisticated-caption">
                                    <div class="text-sm">{{ $document->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $document->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    @if($isUrgent)
                                        <span class="status status-urgent">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Urgent ({{ $daysSinceCreated }}j)
                                        </span>
                                    @elseif($daysSinceCreated > 3)
                                        <span class="status status-warning">
                                            <i class="fas fa-clock"></i>
                                            En attente ({{ $daysSinceCreated }}j)
                                        </span>
                                    @else
                                        <span class="status status-info">
                                            <i class="fas fa-clock"></i>
                                            Récent
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i>
                                            <span>Voir</span>
                                        </a>
                                        
                                        @if(auth()->user()->isSignataire())
                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-pen-fancy"></i>
                                                <span>Signer</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Version mobile/tablette -->
            <div class="lg:hidden space-y-4">
                @foreach($documents as $document)
                    @php
                        $isUrgent = $document->created_at < now()->subDays(7);
                        $daysSinceCreated = $document->created_at->diffInDays(now());
                    @endphp
                    <div class="mobile-document-card {{ $isUrgent ? 'urgent-card' : '' }}">
                        <div class="mobile-card-header">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-pdf text-white text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium sophisticated-heading truncate">{{ $document->document_name ?? $document->filename_original }}</h3>
                                    <p class="text-sm sophisticated-caption">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="status status-pending">
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mobile-card-body">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                <div class="mobile-info-item">
                                    <div class="mobile-info-label">
                                        @if(auth()->user()->isSignataire())
                                            Soumis par
                                        @elseif(auth()->user()->isAdmin())
                                            Soumis par
                                        @else
                                            Assigné à
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user sophisticated-body text-xs"></i>
                                        </div>
                                        <span class="text-sm sophisticated-body">{{ $document->uploader->name }}</span>
                                    </div>
                                </div>
                                
                                <div class="mobile-info-item">
                                    <div class="mobile-info-label">Date</div>
                                    <div class="text-sm sophisticated-caption">
                                        {{ $document->created_at->format('d/m/Y') }} à {{ $document->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                            
                            @if(auth()->user()->isAdmin())
                                <div class="mobile-info-item mb-4">
                                    <div class="mobile-info-label">Assigné à</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user-check text-white text-xs"></i>
                                        </div>
                                        <span class="text-sm sophisticated-body">{{ $document->signer->name }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <div class="mobile-priority">
                                    @if($isUrgent)
                                        <span class="status status-urgent">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Urgent ({{ $daysSinceCreated }}j)
                                        </span>
                                    @elseif($daysSinceCreated > 3)
                                        <span class="status status-warning">
                                            <i class="fas fa-clock"></i>
                                            En attente ({{ $daysSinceCreated }}j)
                                        </span>
                                    @else
                                        <span class="status status-info">
                                            <i class="fas fa-clock"></i>
                                            Récent
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mobile-actions">
                                    <div class="flex gap-2">
                                        <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i>
                                            <span class="hidden sm:inline">Voir</span>
                                        </a>
                                        
                                        @if(auth()->user()->isSignataire())
                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-pen-fancy"></i>
                                                <span class="hidden sm:inline">Signer</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Message si aucun document -->
        <div class="empty-state">
            <div class="empty-icon">
                @if(auth()->user()->isSignataire())
                    <i class="fas fa-pen-fancy"></i>
                @elseif(auth()->user()->isAdmin())
                    <i class="fas fa-clock"></i>
                @else
                    <i class="fas fa-upload"></i>
                @endif
            </div>
            <h3>
                @if(auth()->user()->isSignataire())
                    Aucun document à signer
                @elseif(auth()->user()->isAdmin())
                    Aucun document en attente
                @else
                    Aucun document soumis
                @endif
            </h3>
            <p>
                @if(auth()->user()->isSignataire())
                    Vous n'avez actuellement aucun document assigné pour signature.
                @elseif(auth()->user()->isAdmin())
                    Tous les documents ont été traités.
                @else
                    Vous n'avez pas encore soumis de documents à l'approbation.
                @endif
            </p>
            @if(auth()->user()->isAgent())
                <div class="mt-6">
                    <a href="{{ route('documents.upload') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Soumettre un document
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

<style>
/* Styles pour la vue Documents à Signer */
.stat-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 80px;
    border: 1px solid #f1f3f4;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    border-color: #e3f2fd;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.stat-total .stat-icon { 
    background: linear-gradient(135deg, #007bff, #0056b3);
}
.stat-pending .stat-icon { 
    background: linear-gradient(135deg, #ffc107, #ff8f00);
}
.stat-urgent .stat-icon { 
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.stat-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0;
    line-height: 1;
}

.stat-label {
    color: #64748b;
    margin: 4px 0 0 0;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Styles pour les lignes urgentes */
.urgent-row {
    background: linear-gradient(90deg, #fef2f2, #ffffff);
    border-left: 4px solid #dc3545;
}

.urgent-row:hover {
    background: linear-gradient(90deg, #fee2e2, #f9fafb);
}

/* Statuts de priorité */
.status-urgent {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Animation des lignes du tableau */
.document-row {
    transition: all 0.3s ease;
}

.document-row:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Cartes mobiles pour les documents */
.mobile-document-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.mobile-document-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.mobile-document-card.urgent-card {
    border-left: 4px solid #dc3545;
    background: linear-gradient(90deg, #fef2f2, #ffffff);
}

.mobile-card-header {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.mobile-card-body {
    padding: 1rem;
}

.mobile-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.mobile-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mobile-priority {
    display: flex;
    align-items: center;
}

.mobile-actions .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    min-height: 36px;
}

.mobile-actions .btn i {
    font-size: 0.9rem;
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .stat-card {
        padding: 16px 18px;
        height: 70px;
        gap: 16px;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .mobile-document-card {
        margin-bottom: 1rem;
    }
    
    .mobile-card-header,
    .mobile-card-body {
        padding: 0.875rem;
    }
}

@media (max-width: 640px) {
    .stat-card {
        padding: 14px 16px;
        height: 65px;
        gap: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .mobile-document-card {
        border-radius: 8px;
    }
    
    .mobile-card-header,
    .mobile-card-body {
        padding: 0.75rem;
    }
    
    .mobile-actions .btn {
        padding: 0.5rem;
        font-size: 0.75rem;
        min-height: 32px;
    }
    
    .mobile-actions .btn span {
        display: none;
    }
    
    .mobile-actions .btn i {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .mobile-card-header {
        padding: 0.75rem 0.5rem;
    }
    
    .mobile-card-body {
        padding: 0.75rem 0.5rem;
    }
    
    .mobile-info-item {
        margin-bottom: 0.5rem;
    }
    
    .mobile-actions {
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    
    .mobile-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animation des lignes du tableau
    const tableRows = document.querySelectorAll('.document-row');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 50);
    });
});
</script>
@endsection