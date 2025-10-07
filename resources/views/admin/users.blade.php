@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="container mx-auto p-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Gestion des Utilisateurs</h1>
        
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulaire de création d'utilisateur -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Créer un nouvel utilisateur</h2>
            <form action="{{ route('admin.users.create') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                    <input type="text" name="name" id="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                    <select name="role_id" id="role_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Sélectionner un rôle</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4">
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Liste des utilisateurs</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Utilisateur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rôle
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date de création
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($user->role->name === 'admin') bg-red-100 text-red-800
                                            @elseif($user->role->name === 'agent') bg-blue-100 text-blue-800
                                            @elseif($user->role->name === 'signataire') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $user->role->display_name }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Aucun rôle
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->role_id ?? 'null' }})"
                                            class="text-indigo-600 hover:text-indigo-900">Modifier</button>
                                    
                                    @if($user->isSignataire())
                                        @if($user->hasSignature())
                                            <button onclick="viewSignature({{ $user->id }}, '{{ $user->name }}', '{{ $user->getSignatureUrl() }}')"
                                                    class="text-green-600 hover:text-green-900">Voir signature</button>
                                            <button onclick="deleteSignature({{ $user->id }}, '{{ $user->name }}')"
                                                    class="text-orange-600 hover:text-orange-900">Supprimer signature</button>
                                        @else
                                            <button onclick="uploadSignature({{ $user->id }}, '{{ $user->name }}')"
                                                    class="text-blue-600 hover:text-blue-900">Ajouter signature</button>
                                        @endif
                                        
                                        @if($user->hasParaphe())
                                            <button onclick="viewParaphe({{ $user->id }}, '{{ $user->name }}', '{{ $user->getParapheUrl() }}')"
                                                    class="text-green-600 hover:text-green-900">Voir paraphe</button>
                                            <button onclick="deleteParaphe({{ $user->id }}, '{{ $user->name }}')"
                                                    class="text-orange-600 hover:text-orange-900">Supprimer paraphe</button>
                                        @else
                                            <button onclick="uploadParaphe({{ $user->id }}, '{{ $user->name }}')"
                                                    class="text-blue-600 hover:text-blue-900">Ajouter paraphe</button>
                                        @endif
                                    @endif
                                    
                                    @if(!$user->isAdmin() || $user->id !== auth()->id())
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                                class="text-red-600 hover:text-red-900">Supprimer</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de modification -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Modifier l'utilisateur</h3>
            </div>
            <form id="editForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit_role_id" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                        <select name="role_id" id="edit_role_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Sélectionner un rôle</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'upload de signature -->
<div id="signatureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ajouter une signature</h3>
            </div>
            <form id="signatureForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="signature_file" class="block text-sm font-medium text-gray-700 mb-2">Fichier de signature (PNG uniquement)</label>
                        <input type="file" name="signature" id="signature_file" accept=".png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format PNG uniquement, taille max: 2MB</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeSignatureModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation de signature -->
<div id="viewSignatureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Signature de <span id="signatureUserName"></span></h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <img id="signatureImage" src="" alt="Signature" class="max-w-full h-auto border border-gray-200 rounded">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeViewSignatureModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upload de paraphe -->
<div id="parapheModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ajouter un paraphe</h3>
            </div>
            <form id="parapheForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="paraphe_file" class="block text-sm font-medium text-gray-700 mb-2">Fichier de paraphe (PNG uniquement)</label>
                        <input type="file" name="paraphe" id="paraphe_file" accept=".png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format PNG uniquement, taille max: 2MB</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeParapheModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation de paraphe -->
<div id="viewParapheModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Paraphe de <span id="parapheUserName"></span></h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <img id="parapheImage" src="" alt="Paraphe" class="max-w-full h-auto border border-gray-200 rounded">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeViewParapheModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(id, name, email, roleId) {
    document.getElementById('editForm').action = `/admin/users/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role_id').value = roleId || '';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function deleteUser(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function uploadSignature(id, name) {
    document.getElementById('signatureForm').action = `/admin/users/${id}/signature`;
    document.getElementById('signatureModal').classList.remove('hidden');
}

function closeSignatureModal() {
    document.getElementById('signatureModal').classList.add('hidden');
    document.getElementById('signatureForm').reset();
}

function viewSignature(id, name, signatureUrl) {
    document.getElementById('signatureUserName').textContent = name;
    document.getElementById('signatureImage').src = signatureUrl;
    document.getElementById('viewSignatureModal').classList.remove('hidden');
}

function closeViewSignatureModal() {
    document.getElementById('viewSignatureModal').classList.add('hidden');
}

function deleteSignature(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la signature de "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}/signature`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function uploadParaphe(id, name) {
    document.getElementById('parapheForm').action = `/admin/users/${id}/paraphe`;
    document.getElementById('parapheModal').classList.remove('hidden');
}

function closeParapheModal() {
    document.getElementById('parapheModal').classList.add('hidden');
    document.getElementById('parapheForm').reset();
}

function viewParaphe(id, name, parapheUrl) {
    document.getElementById('parapheUserName').textContent = name;
    document.getElementById('parapheImage').src = parapheUrl;
    document.getElementById('viewParapheModal').classList.remove('hidden');
}

function closeViewParapheModal() {
    document.getElementById('viewParapheModal').classList.add('hidden');
}

function deleteParaphe(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le paraphe de "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}/paraphe`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
