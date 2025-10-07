@extends('emails.layout')

@section('content')
    <p>Bonjour <strong>{{ $agent->name }}</strong>,</p>
    
    <p>Le document a été signé avec succès.</p>
    
    <div class="info-box">
        <h3>✅ Document Signé</h3>
        <p><strong>Nom :</strong> {{ basename($document->path_original) }}</p>
        <p><strong>Signé par :</strong> {{ $signer->name }}</p>
        <p><strong>Date de signature :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
    
    <p>Le document signé est maintenant disponible dans votre historique.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/documents/history') }}" class="button">📋 Voir l'Historique</a>
    </div>
@endsection
