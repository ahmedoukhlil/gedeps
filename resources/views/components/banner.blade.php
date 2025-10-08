@props(['type' => 'info', 'title' => null, 'message' => null, 'dismissible' => false])

@php
    $bannerClasses = 'banner';
    $iconClass = '';
    
    switch($type) {
        case 'success':
            $bannerClasses .= ' banner-success';
            $iconClass = 'fas fa-check-circle';
            break;
        case 'warning':
            $bannerClasses .= ' banner-warning';
            $iconClass = 'fas fa-exclamation-triangle';
            break;
        case 'danger':
        case 'error':
            $bannerClasses .= ' banner-danger';
            $iconClass = 'fas fa-times-circle';
            break;
        case 'info':
        default:
            $bannerClasses .= ' banner-info';
            $iconClass = 'fas fa-info-circle';
            break;
    }
@endphp

<div class="{{ $bannerClasses }}" id="banner-{{ uniqid() }}">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <i class="{{ $iconClass }} text-xl"></i>
        </div>
        
        <div class="flex-1">
            @if($title)
                <h3 class="font-semibold text-lg mb-1">{{ $title }}</h3>
            @endif
            
            @if($message)
                <p class="text-sm opacity-90">{{ $message }}</p>
            @endif
            
            {{ $slot }}
        </div>
        
        @if($dismissible)
            <button type="button" 
                    class="flex-shrink-0 ml-2 text-current opacity-70 hover:opacity-100 transition-opacity"
                    onclick="dismissBanner('banner-{{ uniqid() }}')"
                    aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>

<script>
function dismissBanner(bannerId) {
    const banner = document.getElementById(bannerId);
    if (banner) {
        banner.style.transition = 'all 0.3s ease';
        banner.style.transform = 'translateX(-100%)';
        banner.style.opacity = '0';
        setTimeout(() => {
            banner.remove();
        }, 300);
    }
}
</script>
