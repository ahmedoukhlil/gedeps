@props(['document' => null, 'actions' => []])

<div class="flex flex-wrap gap-2 mt-4">
    @foreach($actions as $action)
        @switch($action['type'])
            @case('view')
                <a href="{{ $action['url'] }}" 
                   class="quick-action-btn quick-action-btn-view"
                   title="Voir le document">
                    <i class="fas fa-eye"></i>
                    <span>{{ $action['label'] ?? 'Voir' }}</span>
                </a>
                @break
                
            @case('edit')
                <a href="{{ $action['url'] }}" 
                   class="quick-action-btn quick-action-btn-edit"
                   title="Modifier le document">
                    <i class="fas fa-edit"></i>
                    <span>{{ $action['label'] ?? 'Modifier' }}</span>
                </a>
                @break
                
            @case('delete')
                <button type="button" 
                        class="quick-action-btn quick-action-btn-delete"
                        onclick="confirmDelete('{{ $action['url'] }}')"
                        title="Supprimer le document">
                    <i class="fas fa-trash"></i>
                    <span>{{ $action['label'] ?? 'Supprimer' }}</span>
                </button>
                @break
                
            @case('download')
                <a href="{{ $action['url'] }}" 
                   class="quick-action-btn quick-action-btn-download"
                   title="Télécharger le document">
                    <i class="fas fa-download"></i>
                    <span>{{ $action['label'] ?? 'Télécharger' }}</span>
                </a>
                @break
                
            @case('share')
                <button type="button" 
                        class="quick-action-btn quick-action-btn-share"
                        onclick="shareDocument('{{ $action['url'] }}')"
                        title="Partager le document">
                    <i class="fas fa-share"></i>
                    <span>{{ $action['label'] ?? 'Partager' }}</span>
                </button>
                @break
                
            @case('sign')
                <a href="{{ $action['url'] }}" 
                   class="signature-btn signature-btn-sign"
                   title="Signer le document">
                    <i class="fas fa-pen-fancy"></i>
                    <span>{{ $action['label'] ?? 'Signer' }}</span>
                </a>
                @break
                
            @case('complete')
                <button type="button" 
                        class="signature-btn signature-btn-complete"
                        onclick="completeDocument('{{ $action['url'] }}')"
                        title="Finaliser le document">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ $action['label'] ?? 'Finaliser' }}</span>
                </button>
                @break
        @endswitch
    @endforeach
</div>

<script>
function confirmDelete(url) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function shareDocument(url) {
    if (navigator.share) {
        navigator.share({
            title: 'Document GEDEPS',
            url: url
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Share
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}

function completeDocument(url) {
    if (confirm('Êtes-vous sûr de vouloir finaliser ce document ?')) {
        window.location.href = url;
    }
}
</script>
