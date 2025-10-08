@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'onclick' => null
])

@php
    $buttonClasses = 'action-btn';
    $iconClasses = '';
    
    // Variantes de couleur
    switch($variant) {
        case 'success':
            $buttonClasses .= ' action-btn-success';
            break;
        case 'warning':
            $buttonClasses .= ' action-btn-warning';
            break;
        case 'danger':
            $buttonClasses .= ' action-btn-danger';
            break;
        case 'info':
            $buttonClasses .= ' action-btn-info';
            break;
        case 'secondary':
            $buttonClasses .= ' action-btn-secondary';
            break;
        case 'primary':
        default:
            $buttonClasses .= ' action-btn-primary';
            break;
    }
    
    // Tailles
    switch($size) {
        case 'sm':
            $buttonClasses .= ' action-btn-sm';
            break;
        case 'lg':
            $buttonClasses .= ' action-btn-lg';
            break;
        case 'md':
        default:
            $buttonClasses .= ' action-btn-md';
            break;
    }
    
    if ($loading || $disabled) {
        $buttonClasses .= ' opacity-50 cursor-not-allowed';
    }
@endphp

@if($href)
    <a href="{{ $href }}" 
       class="{{ $buttonClasses }}"
       @if($disabled) onclick="return false;" @endif>
        @if($icon)
            <i class="{{ $icon }} {{ $loading ? 'fa-spin' : '' }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" 
            class="{{ $buttonClasses }}"
            @if($onclick) onclick="{{ $onclick }}" @endif
            @if($disabled) disabled @endif>
        @if($icon)
            <i class="{{ $icon }} {{ $loading ? 'fa-spin' : '' }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif

<style>
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.3);
}

.action-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(59, 130, 246, 0.4);
}

.action-btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.3);
}

.action-btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(16, 185, 129, 0.4);
}

.action-btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(245, 158, 11, 0.3);
}

.action-btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(245, 158, 11, 0.4);
}

.action-btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.3);
}

.action-btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(239, 68, 68, 0.4);
}

.action-btn-info {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(6, 182, 212, 0.3);
}

.action-btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(6, 182, 212, 0.4);
}

.action-btn-secondary {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(107, 114, 128, 0.3);
}

.action-btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(107, 114, 128, 0.4);
}

.action-btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
}

.action-btn-md {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
}

.action-btn-lg {
    padding: 1rem 2rem;
    font-size: 1rem;
}
</style>
