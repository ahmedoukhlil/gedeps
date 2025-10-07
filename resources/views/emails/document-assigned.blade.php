@extends('emails.layout')

@section('content')
    <p>Bonjour <strong>{{ $signer->name }}</strong>,</p>
    
    <p>Un nouveau document vous a été assigné pour signature.</p>
    
    <div class="info-box">
        <h3>📄 Informations du Document</h3>
        <p><strong>Nom :</strong> {{ basename($document->path_original) }}</p>
        <p><strong>Assigné par :</strong> {{ $agent->name }}</p>
        <p><strong>Date d'assignation :</strong> {{ $document->created_at->format('d/m/Y à H:i') }}</p>
    </div>
    
    <p>Connectez-vous à votre espace pour signer le document.</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/signatures') }}" class="button">📝 Signer le Document</a>
    </div>
@endsection