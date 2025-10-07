@extends('emails.layout')

@section('content')
    @if($isCompleted)
        <div class="info-box" style="background: #f0fdf4; border-color: #22c55e;">
            <h3>🎉 Document Entièrement Signé !</h3>
            <p>Toutes les signatures ont été complétées avec succès.</p>
        </div>
    @else
        <p>Bonjour <strong>{{ $signer->name }}</strong>,</p>
        
        <p>C'est votre tour de signer le document dans le processus séquentiel.</p>
    @endif
    
    <div class="info-box">
        <h3>📄 {{ $document->document_name }}</h3>
        <p><strong>Type :</strong> {{ $document->type }}</p>
        <p><strong>Description :</strong> {{ $document->description ?? 'Aucune description' }}</p>
        <p><strong>Uploadé par :</strong> {{ $document->uploader->name }}</p>
        <p><strong>Date de création :</strong> {{ $document->created_at->format('d/m/Y à H:i') }}</p>
    </div>

    @if(!$isCompleted)
        <div class="info-box">
            <h3>📊 Progression</h3>
            <p><strong>{{ number_format($progress, 1) }}% Complété</strong></p>
            <div style="background: #e5e7eb; height: 8px; border-radius: 4px; margin: 8px 0;">
                <div style="background: #3b82f6; height: 100%; width: {{ $progress }}%; border-radius: 4px;"></div>
            </div>
        </div>

        <div class="info-box">
            <h3>👥 Signataires</h3>
            @foreach($document->sequentialSignatures as $sequentialSignature)
                <p style="margin: 4px 0;">
                    @if($sequentialSignature->status === 'signed')
                        ✅ <strong>{{ $sequentialSignature->user->name }}</strong> (Signé)
                    @elseif($sequentialSignature->user_id === $signer->id)
                        🔄 <strong>{{ $sequentialSignature->user->name }}</strong> (Votre tour)
                    @else
                        ⏳ <strong>{{ $sequentialSignature->user->name }}</strong> (En attente)
                    @endif
                </p>
            @endforeach
        </div>

        @if($previousSigner)
            <div class="info-box" style="background: #f0f9ff; border-color: #0ea5e9;">
                <h3>ℹ️ Information</h3>
                <p>{{ $previousSigner->name }} a terminé sa signature. C'est maintenant votre tour.</p>
            </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ route('signatures.sequential.show', $document->id) }}" class="button">
                📝 Signer le Document
            </a>
        </div>
    @else
        <div style="text-align: center;">
            <a href="{{ url('/documents/history') }}" class="button">
                📋 Voir l'Historique
            </a>
        </div>
    @endif
@endsection
