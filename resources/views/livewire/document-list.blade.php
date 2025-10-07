<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                @if($showAllDocuments)
                    Historique des Documents
                @else
                    Mes Documents
                @endif
            </h2>
            <div class="text-sm text-gray-500">
                {{ $documents->total() }} document(s)
            </div>
        </div>

        <!-- Filtres -->
        <div class="mb-6 bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Recherche -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        Recherche
                    </label>
                    <input type="text" id="search" wire:model.debounce.300ms="search" 
                           placeholder="Nom du fichier, commentaire..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Filtre par statut -->
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        Statut
                    </label>
                    <select id="statusFilter" wire:model="statusFilter" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($this->statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtre par type -->
                <div>
                    <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                        Type
                    </label>
                    <select id="typeFilter" wire:model="typeFilter" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($this->typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Bouton de réinitialisation -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        @if($documents->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            @if($showAllDocuments)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Uploadé par
                                </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documents as $document)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ Str::limit($document->filename_original, 30) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $document->id }}
                                            </div>
                                            @if($document->comment_agent)
                                                <div class="text-xs text-gray-400 mt-1">
                                                    {{ Str::limit($document->comment_agent, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $document->type_name }}
                                    </span>
                                </td>
                                @if($showAllDocuments)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $document->uploader->name }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $document->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_badge_class }}">
                                        {{ $document->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @can('download', $document)
                                            <a href="{{ route('documents.download', $document) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                Télécharger
                                            </a>
                                        @endcan
                                        
                                        @if($document->isSigned())
                                            <a href="{{ route('documents.view', $document) }}" target="_blank"
                                               class="text-green-600 hover:text-green-900">
                                                PDF Signé
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $documents->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun document trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($search || $statusFilter || $typeFilter)
                        Aucun document ne correspond à vos critères de recherche.
                    @else
                        Aucun document n'a encore été uploadé.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
