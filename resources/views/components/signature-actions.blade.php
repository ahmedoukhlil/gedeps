@props(['document', 'canSign' => false, 'canParaphe' => false, 'canComplete' => false])

<div class="signature-actions">
    <div class="signature-actions-header">
        <h3 class="signature-actions-title">Actions de Signature</h3>
        <p class="signature-actions-subtitle">
            Choisissez l'action appropriée pour ce document
        </p>
    </div>
    
    <div class="signature-buttons-grid">
        @if($canSign)
            <button type="button" 
                    class="signature-btn signature-btn-sign"
                    onclick="initiateSignature()">
                <i class="fas fa-pen-fancy"></i>
                <span>Signer le Document</span>
            </button>
        @endif
        
        @if($canParaphe)
            <button type="button" 
                    class="signature-btn signature-btn-paraphe"
                    onclick="initiateParaphe()">
                <i class="fas fa-stamp"></i>
                <span>Parapher le Document</span>
            </button>
        @endif
        
        @if($canComplete)
            <button type="button" 
                    class="signature-btn signature-btn-complete"
                    onclick="completeDocument()">
                <i class="fas fa-check-circle"></i>
                <span>Finaliser le Document</span>
            </button>
        @endif
    </div>
    
    @if(!$canSign && !$canParaphe && !$canComplete)
        <div class="text-center py-8">
            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Aucune action de signature disponible pour ce document.</p>
        </div>
    @endif
</div>

<script>
function initiateSignature() {
    // Logique pour initier la signature
    console.log('Initiation de la signature...');
    // Ici vous pouvez ajouter votre logique de signature
}

function initiateParaphe() {
    // Logique pour initier le paraphe
    console.log('Initiation du paraphe...');
    // Ici vous pouvez ajouter votre logique de paraphe
}

function completeDocument() {
    // Logique pour finaliser le document
    console.log('Finalisation du document...');
    // Ici vous pouvez ajouter votre logique de finalisation
}
</script>
