@props(['document', 'currentUser', 'signatureOrder', 'totalSignatures'])

<div class="signature-actions">
    <div class="signature-actions-header">
        <h3 class="signature-actions-title">Signature Séquentielle</h3>
        <p class="signature-actions-subtitle">
            Étape {{ $signatureOrder }} sur {{ $totalSignatures }} - 
            @if($signatureOrder == 1)
                Premier signataire
            @else
                Signataire suivant
            @endif
        </p>
    </div>
    
    <div class="signature-buttons-grid">
        <button type="button" 
                class="signature-btn signature-btn-sign"
                onclick="initiateSequentialSignature()">
            <i class="fas fa-pen-fancy"></i>
            <span>Signer le Document</span>
        </button>
        
        @if($signatureOrder > 1)
            <button type="button" 
                    class="signature-btn signature-btn-complete"
                    onclick="viewPreviousSignatures()">
                <i class="fas fa-history"></i>
                <span>Voir les Signatures Précédentes</span>
            </button>
        @endif
    </div>
    
    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
        <div class="flex items-center gap-2 text-blue-800">
            <i class="fas fa-info-circle"></i>
            <span class="font-medium">Information</span>
        </div>
        <p class="text-blue-700 text-sm mt-2">
            @if($signatureOrder == 1)
                Vous êtes le premier signataire. Votre signature sera visible pour les signataires suivants.
            @else
                Ce document a déjà été signé par {{ $signatureOrder - 1 }} signataire(s). 
                Votre signature sera ajoutée à la suite.
            @endif
        </p>
    </div>
</div>

<script>
function initiateSequentialSignature() {
    console.log('Initiation de la signature séquentielle...');
    // Logique pour la signature séquentielle
}

function viewPreviousSignatures() {
    console.log('Affichage des signatures précédentes...');
    // Logique pour afficher les signatures précédentes
}
</script>
