@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- En-tête simplifié -->
            <div class="sophisticated-dashboard-header">
                <div class="welcome-section">
                    <h1 class="sophisticated-welcome-title">
                        <i class="fas fa-home"></i>
                        Bienvenue, {{ auth()->user()->name }} !
                    </h1>
                    <p class="sophisticated-welcome-subtitle">
                        {{ auth()->user()->role ? auth()->user()->role->display_name : 'Utilisateur' }}
                    </p>
                </div>
                
                <!-- Action principale selon le rôle -->
                <div class="main-action">
                    @if(auth()->user()->isAgent())
                        <a href="{{ route('documents.upload') }}" class="sophisticated-main-action-btn">
                            <i class="fas fa-upload"></i>
                            <span>Nouveau Document</span>
                        </a>
                    @elseif(auth()->user()->isSignataire())
                        <a href="{{ route('documents.upload') }}" class="sophisticated-main-action-btn">
                            <i class="fas fa-upload"></i>
                            <span>Soumettre un document</span>
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="sophisticated-main-action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Administration</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Statistiques essentielles -->
            <div class="stats-grid">
                @if(auth()->user()->isAdmin())
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                            <p>Utilisateurs</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['total_documents'] ?? 0 }}</h3>
                            <p>Documents</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending_documents'] ?? 0 }}</h3>
                            <p>En Attente</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p>Signés</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['sequential_documents'] ?? 0 }}</h3>
                            <p>Séquentielles</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['sequential_pending'] ?? 0 }}</h3>
                            <p>En Cours</p>
                        </div>
                    </div>

                @elseif(auth()->user()->isAgent())
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['my_documents'] ?? 0 }}</h3>
                            <p>Mes Documents</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending_approval'] ?? 0 }}</h3>
                            <p>En Attente</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p>Signés</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['sequential_created'] ?? 0 }}</h3>
                            <p>Séquentielles Créées</p>
                        </div>
                    </div>

                @elseif(auth()->user()->isSignataire())
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending_documents'] ?? 0 }}</h3>
                            <p>À Signer (Simples)</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['sequential_pending'] ?? 0 }}</h3>
                            <p>Séquentielles</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p>Signés (Simples)</p>
                        </div>
                    </div>
                    
                    <div class="sophisticated-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['sequential_signed'] ?? 0 }}</h3>
                            <p>Signés (Séquentielles)</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions rapides simplifiées -->
            <div class="quick-actions">
                <h3>Actions Rapides</h3>
                <div class="actions-grid-modern">
                    @if(auth()->user()->isAgent())
                        <a href="{{ route('documents.upload') }}" class="action-card-modern primary">
                            <div class="action-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="action-content">
                                <h4>Soumettre</h4>
                                <p>Nouveau document</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <a href="{{ route('documents.history') }}" class="action-card-modern info">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="action-content">
                                <h4>Mes Documents</h4>
                                <p>Historique complet</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    @endif

                    @if(auth()->user()->isSignataire())
                        <a href="{{ route('signatures.index') }}" class="action-card-modern secondary">
                            <div class="action-icon">
                                <i class="fas fa-pen-fancy"></i>
                            </div>
                            <div class="action-content">
                                <h4>Signatures Simples</h4>
                                <p>Documents en attente</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('signatures.simple.index') }}" class="action-card-modern info">
                            <div class="action-icon">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="action-content">
                                <h4>Signatures Séquentielles</h4>
                                <p>Workflow ordonné</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="action-card-modern warning">
                            <div class="action-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="action-content">
                                <h4>Admin</h4>
                                <p>Administration</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    @endif

                    <a href="{{ route('documents.pending') }}" class="action-card-modern success">
                        <div class="action-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="action-content">
                            <h4>En Attente</h4>
                            <p>Documents en cours</p>
                        </div>
                        <div class="action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('documents.history') }}" class="action-card-modern info">
                        <div class="action-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="action-content">
                            <h4>Historique</h4>
                            <p>Tous les documents</p>
                        </div>
                        <div class="action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard simplifié */
.sophisticated-dashboard-header {
    background: var(--gradient-primary);
    color: var(--text-white);
    padding: 32px;
    border-radius: 16px;
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--soph-accent-700);
}

.sophisticated-welcome-title {
    margin: 0 0 8px 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-white);
}

.sophisticated-welcome-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
    color: var(--text-white);
}

.sophisticated-main-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 32px;
    background: rgba(255, 255, 255, 0.2);
    color: var(--text-white);
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.sophisticated-main-action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    color: var(--text-white);
    text-decoration: none;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.sophisticated-main-action-btn i {
    font-size: 1.3rem;
}

/* Grille de statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.sophisticated-stat-card {
    background: var(--bg-card);
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}

.sophisticated-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border-color: var(--soph-accent-500);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.sophisticated-stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.sophisticated-stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #28a745, #218838);
}

.sophisticated-stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.sophisticated-stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.stat-content h3 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
    font-weight: 500;
}

/* Actions rapides */
.quick-actions {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.quick-actions h3 {
    margin: 0 0 24px 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
}

.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 24px 16px;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.action-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    border-color: #667eea;
    color: #2c3e50;
    text-decoration: none;
}

.action-item i {
    font-size: 2rem;
    color: #667eea;
}

.action-item span {
    font-weight: 600;
    font-size: 0.9rem;
}

/* Responsive amélioré */
@media (max-width: 1024px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
        padding: 24px;
        gap: 20px;
    }
    
    .main-action-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 20px;
        margin-bottom: 24px;
    }
    
    .welcome-section h1 {
        font-size: 1.5rem;
        margin-bottom: 6px;
    }
    
    .welcome-subtitle {
        font-size: 1rem;
    }
    
    .main-action-btn {
        padding: 14px 24px;
        font-size: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .sophisticated-stat-card {
        padding: 20px;
        height: auto;
        min-height: 80px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .action-item {
        padding: 20px 16px;
    }
    
    .action-item i {
        font-size: 1.8rem;
    }
}

@media (max-width: 640px) {
    .dashboard-header {
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .welcome-section h1 {
        font-size: 1.25rem;
    }
    
    .welcome-subtitle {
        font-size: 0.9rem;
    }
    
    .main-action-btn {
        padding: 12px 20px;
        font-size: 0.95rem;
    }
    
    .stats-grid {
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .sophisticated-stat-card {
        padding: 16px;
        height: auto;
        min-height: 70px;
        gap: 12px;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .action-item {
        padding: 18px 14px;
    }
    
    .action-item i {
        font-size: 1.6rem;
    }
    
    .action-item span {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .dashboard-header {
        padding: 12px;
        margin-bottom: 16px;
    }
    
    .welcome-section h1 {
        font-size: 1.1rem;
        margin-bottom: 4px;
    }
    
    .welcome-subtitle {
        font-size: 0.85rem;
    }
    
    .main-action-btn {
        padding: 10px 16px;
        font-size: 0.9rem;
    }
    
    .stats-grid {
        gap: 10px;
        margin-bottom: 16px;
    }
    
    .sophisticated-stat-card {
        padding: 14px;
        min-height: 65px;
        gap: 10px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .stat-number {
        font-size: 1.6rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .action-item {
        padding: 16px 12px;
    }
    
    .action-item i {
        font-size: 1.4rem;
    }
    
    .action-item span {
        font-size: 0.8rem;
    }
}
</style>
@endsection