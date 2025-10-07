@props(['items' => []])

<nav class="breadcrumbs" aria-label="Fil d'Ariane">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}" class="breadcrumb-link">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
        </li>
        @foreach($items as $item)
            <li class="breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                @if($loop->last)
                    <span class="breadcrumb-current">
                        <i class="fas fa-{{ $item['icon'] ?? 'file' }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </span>
                @else
                    <a href="{{ $item['url'] }}" class="breadcrumb-link">
                        <i class="fas fa-{{ $item['icon'] ?? 'file' }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<style>
.breadcrumbs {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 0;
    margin-bottom: 24px;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0 16px;
    max-width: 1200px;
    margin: 0 auto;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-link {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    color: #64748b;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.breadcrumb-link:hover {
    background: #e2e8f0;
    color: #334155;
    transform: translateY(-1px);
}

.breadcrumb-current {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #3b82f6;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
}

.breadcrumb-separator {
    color: #94a3b8;
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .breadcrumbs {
        padding: 8px 0;
        margin-bottom: 16px;
    }
    
    .breadcrumb-list {
        padding: 0 12px;
        gap: 4px;
    }
    
    .breadcrumb-link,
    .breadcrumb-current {
        padding: 4px 8px;
        font-size: 0.8rem;
    }
    
    .breadcrumb-link span,
    .breadcrumb-current span {
        display: none;
    }
    
    .breadcrumb-link i,
    .breadcrumb-current i {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .breadcrumb-list {
        padding: 0 8px;
    }
    
    .breadcrumb-separator {
        display: none;
    }
}
</style>
