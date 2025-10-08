@props(['document', 'cardType' => 'default'])

@php
    $cardClasses = 'card';
    $statusClasses = 'document-status';
    
    switch($cardType) {
        case 'primary':
            $cardClasses .= ' card-primary';
            break;
        case 'success':
            $cardClasses .= ' card-success';
            break;
        case 'warning':
            $cardClasses .= ' card-warning';
            break;
        case 'danger':
            $cardClasses .= ' card-danger';
            break;
        case 'info':
            $cardClasses .= ' card-info';
            break;
    }
    
    switch($document->status ?? 'pending') {
        case 'signed':
            $statusClasses .= ' document-status-signed';
            break;
        case 'in_progress':
            $statusClasses .= ' document-status-in-progress';
            break;
        case 'completed':
            $statusClasses .= ' document-status-completed';
            break;
        case 'rejected':
            $statusClasses .= ' document-status-rejected';
            break;
        default:
            $statusClasses .= ' document-status-pending';
    }
@endphp

<div class="{{ $cardClasses }}">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $document->document_name ?? 'Document' }}
            </h3>
            <span class="{{ $statusClasses }}">
                {{ ucfirst($document->status ?? 'En attente') }}
            </span>
        </div>
    </div>
    
    <div class="card-body">
        <div class="space-y-3">
            @if($document->description)
                <p class="text-gray-600 text-sm">
                    {{ Str::limit($document->description, 100) }}
                </p>
            @endif
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-500">Type:</span>
                    <span class="text-gray-900">{{ $document->type ?? 'Non spécifié' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500">Créé le:</span>
                    <span class="text-gray-900">{{ $document->created_at ? $document->created_at->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </div>
            
            @if($document->uploader)
                <div class="flex items-center gap-2">
                    <i class="fas fa-user text-gray-400"></i>
                    <span class="text-sm text-gray-600">
                        Uploadé par {{ $document->uploader->name }}
                    </span>
                </div>
            @endif
        </div>
    </div>
    
    <div class="card-footer">
        <x-quick-actions 
            :document="$document"
            :actions="[
                [
                    'type' => 'view',
                    'url' => route('documents.show', $document->id),
                    'label' => 'Voir'
                ],
                [
                    'type' => 'download',
                    'url' => route('documents.download', $document->id),
                    'label' => 'Télécharger'
                ],
                [
                    'type' => 'sign',
                    'url' => route('signatures.show', $document->id),
                    'label' => 'Signer'
                ]
            ]"
        />
    </div>
</div>
