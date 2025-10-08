@props([
    'document' => null,
    'user' => null,
    'isSequential' => false,
    'isAdmin' => false,
    'canSign' => false,
    'canParaphe' => false,
    'canComplete' => false,
    'signatureOrder' => 1,
    'totalSignatures' => 1
])

<div class="signature-actions">
    <div class="signature-actions-header">
        @if($isSequential)
            <h3 class="signature-actions-title">Signature Séquentielle</h3>
            <p class="signature-actions-subtitle">
                Étape {{ $signatureOrder }} sur {{ $totalSignatures }}
                @if($signatureOrder == 1)
                    - Premier signataire
                @else
                    - Signataire suivant
                @endif
            </p>
        @else
            <h3 class="signature-actions-title">Actions de Signature</h3>
            <p class="signature-actions-subtitle">
                Choisissez l'action appropriée pour ce document
            </p>
        @endif
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
        
        @if($isAdmin)
            <button type="button" 
                    class="signature-btn signature-btn-complete"
                    onclick="adminSignDocument()">
                <i class="fas fa-crown"></i>
                <span>Signer en tant qu'Admin</span>
            </button>
        @endif
    </div>
    
    @if($isSequential && $signatureOrder > 1)
        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-center gap-2 text-blue-800">
                <i class="fas fa-info-circle"></i>
                <span class="font-medium">Information</span>
            </div>
            <p class="text-blue-700 text-sm mt-2">
                Ce document a déjà été signé par {{ $signatureOrder - 1 }} signataire(s). 
                Votre signature sera ajoutée à la suite.
            </p>
        </div>
    @endif
    
    @if(!$canSign && !$canParaphe && !$canComplete && !$isAdmin)
        <div class="text-center py-8">
            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Aucune action de signature disponible pour ce document.</p>
        </div>
    @endif
</div>

<script>
function initiateSignature() {
    console.log('Initiation de la signature...');
    // Logique pour initier la signature
}

function initiateParaphe() {
    console.log('Initiation du paraphe...');
    // Logique pour initier le paraphe
}

function completeDocument() {
    console.log('Finalisation du document...');
    // Logique pour finaliser le document
}

function adminSignDocument() {
    if (confirm('Êtes-vous sûr de vouloir signer ce document en tant qu\'administrateur ?')) {
        console.log('Signature administrative...');
        // Logique pour la signature administrative
    }
}
</script>
