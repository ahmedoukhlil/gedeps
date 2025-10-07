@extends('emails.layout')

@section('content')
    <p>Bonjour <strong>{{ $agent->name }}</strong>,</p>
    
    <p>Le document a été paraphé avec succès.</p>
    
    <div class="info-box">
        <h3>✍️ Document Paraphé</h3>
        <p><strong>Nom :</strong> {{ basename($document->path_original) }}</p>
        <p><strong>Paraphé par :</strong> {{ $signer->name }}</p>
        <p><strong>Date de paraphe :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
    
    <p>Le document paraphé est maintenant disponible dans votre historique.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/documents/history') }}" class="button">📋 Voir l'Historique</a>
    </div>
@endsection
