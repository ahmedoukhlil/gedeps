<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Upload de Document</h2>

        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="upload">
            <!-- Zone de drag & drop -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fichier à uploader *
                </label>
                
                @if (!$file)
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors"
                         x-data="{ isDragging: false }"
                         x-on:dragover.prevent="isDragging = true"
                         x-on:dragleave.prevent="isDragging = false"
                         x-on:drop.prevent="isDragging = false; $wire.upload('file', $event.dataTransfer.files[0])"
                         :class="{ 'border-blue-400 bg-blue-50': isDragging }">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4">
                            <label for="file-upload" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    Glissez-déposez votre fichier ici ou
                                </span>
                                <span class="mt-1 block text-sm text-blue-600 hover:text-blue-500">
                                    cliquez pour sélectionner
                                </span>
                            </label>
                            <input id="file-upload" type="file" class="sr-only" wire:model="file" accept=".pdf,.png,.jpg,.jpeg">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            PDF, PNG, JPG, JPEG jusqu'à 10MB
                        </p>
                    </div>
                @else
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-8 w-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $file->getClientOriginalName() }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <button type="button" wire:click="removeFile" class="text-red-600 hover:text-red-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
                
                @error('file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type de document -->
            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    Type de document *
                </label>
                <select id="type" wire:model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Sélectionnez un type</option>
                    @foreach($this->types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Commentaire -->
            <div class="mb-6">
                <label for="comment_agent" class="block text-sm font-medium text-gray-700 mb-2">
                    Commentaire (optionnel)
                </label>
                <textarea id="comment_agent" wire:model="comment_agent" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Ajoutez un commentaire pour expliquer le contexte de ce document..."></textarea>
                @error('comment_agent')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Barre de progression -->
            @if($isUploading)
                <div class="mb-6">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $uploadProgress }}%"></div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Upload en cours... {{ $uploadProgress }}%</p>
                </div>
            @endif

            <!-- Bouton de soumission -->
            <div class="flex justify-end">
                <button type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="upload"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading wire:target="upload" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="upload">Uploader le document</span>
                    <span wire:loading wire:target="upload">Upload en cours...</span>
                </button>
            </div>
        </form>
    </div>
</div>
