@extends('layouts.app')

@section('title', 'Paraphes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- En-tête moderne -->
            <div class="modern-card">
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <h1 class="card-title">
                                <i class="fas fa-pen-nib"></i>
                                Gestion des Paraphes
                            </h1>
                            <p class="card-subtitle">Parapher vos documents électroniquement</p>
                        </div>
                        <div class="header-badge">
                            <span class="status-modern status-info">
                                <i class="fas fa-pen-nib"></i>
                                Paraphes
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Statistiques rapides -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $documents->total() }}</h3>
                                <p>Documents</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $documents->where('status', 'pending')->count() }}</h3>
                                <p>En Attente</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-pen-nib"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $documents->where('status', 'paraphed')->count() }}</h3>
                                <p>Paraphés</p>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des documents -->
                    <div class="modern-panel">
                        <div class="panel-header">
                            <h3 class="panel-title">Documents à Parapher</h3>
                            <div class="panel-actions">
                                <a href="{{ route('documents.upload') }}" class="btn-modern btn-modern-primary">
                                    <i class="fas fa-upload"></i>
                                    <span>Nouveau Document</span>
                                </a>
                            </div>
                        </div>

                        @if($documents->count() > 0)
                            <div class="modern-table-container">
                                <table class="modern-table">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Type</th>
                                            <th>Statut</th>
                                            <th>Uploadé par</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documents as $document)
                                            <tr>
                                                <td>
                                                    <div class="document-info">
                                                        <div class="document-name">
                                                            <i class="fas fa-file-pdf"></i>
                                                            {{ $document->filename_original }}
                                                        </div>
                                                        <div class="document-description">
                                                            {{ Str::limit($document->description, 50) }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="type-badge">
                                                        {{ $document->type_name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-modern 
                                                        @if($document->status === 'pending') status-warning
                                                        @elseif($document->status === 'paraphed') status-success
                                                        @elseif($document->status === 'signed') status-info
                                                        @else status-danger @endif">
                                                        {{ $document->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="user-info">
                                                        <div class="user-name">{{ $document->uploader->name }}</div>
                                                        <div class="user-role">{{ $document->uploader->role->display_name ?? 'N/A' }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="date-info">
                                                        <div class="date">{{ $document->created_at->format('d/m/Y') }}</div>
                                                        <div class="time">{{ $document->created_at->format('H:i') }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        @if($document->status === 'pending' || $document->status === 'in_progress')
                                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'paraphe']) }}" 
                                                               class="btn-modern btn-modern-primary btn-sm">
                                                                <i class="fas fa-pen-nib"></i>
                                                                <span>Parapher</span>
                                                            </a>
                                                        @elseif($document->status === 'paraphed')
                                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                                                               class="btn-modern btn-modern-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                                <span>Voir</span>
                                                            </a>
                                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'download']) }}" 
                                                               class="btn-modern btn-modern-success btn-sm">
                                                                <i class="fas fa-download"></i>
                                                                <span>Télécharger</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                {{ $documents->links() }}
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h3>Aucun document trouvé</h3>
                                <p>Il n'y a actuellement aucun document à parapher.</p>
                                <a href="{{ route('documents.upload') }}" class="btn-modern btn-modern-primary">
                                    <i class="fas fa-upload"></i>
                                    <span>Uploader un Document</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux paraphes */
.document-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.document-name {
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.document-description {
    font-size: 0.85rem;
    color: #6c757d;
}

.type-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.user-role {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.date-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.time {
    font-size: 0.75rem;
    color: #6c757d;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.85rem;
}

.modern-table-container {
    overflow-x: auto;
}

.pagination-container {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 16px;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 24px 0;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-table {
        font-size: 0.85rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}
</style>
@endsection
