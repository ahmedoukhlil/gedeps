@props(['document', 'isAdmin' => false])

@if($isAdmin)
    <div class="signature-actions">
        <div class="signature-actions-header">
            <h3 class="signature-actions-title">Actions d'Administration</h3>
            <p class="signature-actions-subtitle">
                Gestion avancée du document
            </p>
        </div>
        
        <div class="signature-buttons-grid">
            <button type="button" 
                    class="signature-btn signature-btn-sign"
                    onclick="adminSignDocument()">
                <i class="fas fa-pen-fancy"></i>
                <span>Signer en tant qu'Admin</span>
            </button>
            
            <button type="button" 
                    class="signature-btn signature-btn-complete"
                    onclick="forceCompleteDocument()">
                <i class="fas fa-check-double"></i>
                <span>Forcer la Finalisation</span>
            </button>
            
            <button type="button" 
                    class="signature-btn signature-btn-paraphe"
                    onclick="resetDocument()">
                <i class="fas fa-undo"></i>
                <span>Réinitialiser le Document</span>
            </button>
        </div>
        
        <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
            <div class="flex items-center gap-2 text-yellow-800">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="font-medium">Attention</span>
            </div>
            <p class="text-yellow-700 text-sm mt-2">
                Les actions d'administration ont un impact direct sur le statut du document. 
                Utilisez-les avec précaution.
            </p>
        </div>
    </div>
@endif

<script>
function adminSignDocument() {
    if (confirm('Êtes-vous sûr de vouloir signer ce document en tant qu\'administrateur ?')) {
        console.log('Signature administrative...');
        // Logique pour la signature administrative
    }
}

function forceCompleteDocument() {
    if (confirm('Êtes-vous sûr de vouloir forcer la finalisation de ce document ?')) {
        console.log('Finalisation forcée...');
        // Logique pour forcer la finalisation
    }
}

function resetDocument() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser ce document ? Cette action est irréversible.')) {
        console.log('Réinitialisation du document...');
        // Logique pour réinitialiser le document
    }
}
</script>
