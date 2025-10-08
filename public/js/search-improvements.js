/**
 * AMÉLIORATIONS DE LA RECHERCHE
 * Script pour améliorer l'expérience de recherche globale
 */

(function() {
    'use strict';

    // Configuration
    const SEARCH_CONFIG = {
        debounceDelay: 500, // Délai réduit pour une recherche plus rapide
        minSearchLength: 1, // Recherche dès le premier caractère
        maxSuggestions: 5,
        animationDuration: 300
    };

    /**
     * Initialise les améliorations de recherche
     */
    function initSearchImprovements() {
        console.log('🔍 Initialisation des améliorations de recherche...');
        
        // Améliorer le champ de recherche global
        enhanceGlobalSearch();
        
        // Gérer les filtres avancés
        handleAdvancedFilters();
        
        // Améliorer l'expérience utilisateur
        enhanceUserExperience();
        
        console.log('✅ Améliorations de recherche initialisées');
    }

    /**
     * Améliore le champ de recherche global
     */
    function enhanceGlobalSearch() {
        const searchInput = document.getElementById('search');
        if (!searchInput) return;

        // Ajouter les classes CSS pour le style
        searchInput.classList.add('search-global-input');
        
        // Gérer le focus et le blur
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });

        // Gérer la recherche automatique en temps réel
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            if (this.value.length >= SEARCH_CONFIG.minSearchLength) {
                // Afficher l'état de chargement
                this.classList.add('search-loading');
                
                // Afficher un indicateur de recherche en cours
                showSearchIndicator();
                
                searchTimeout = setTimeout(() => {
                    // Recherche automatique
                    this.form.submit();
                }, SEARCH_CONFIG.debounceDelay);
            } else if (this.value.length === 0) {
                // Recherche immédiate si le champ est vidé
                this.form.submit();
            }
        });

        // Gérer les raccourcis clavier
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
            
            if (e.key === 'Escape') {
                this.value = '';
                this.blur();
            }
        });

        // Auto-focus sur le champ de recherche
        if (searchInput.value === '') {
            searchInput.focus();
        }
    }

    /**
     * Gère l'affichage/masquage des filtres avancés
     */
    function handleAdvancedFilters() {
        const toggleButton = document.getElementById('toggleAdvancedFilters');
        const advancedFilters = document.getElementById('advancedFilters');
        const filterToggleIcon = document.getElementById('filterToggleIcon');
        
        if (!toggleButton || !advancedFilters || !filterToggleIcon) return;

        // Ajouter les classes CSS
        toggleButton.classList.add('advanced-filters-toggle');
        advancedFilters.classList.add('advanced-filters-container');
        
        // Gérer le clic sur le bouton toggle
        toggleButton.addEventListener('click', function() {
            const isHidden = advancedFilters.classList.contains('hidden');
            
            if (isHidden) {
                // Afficher les filtres
                advancedFilters.classList.remove('hidden');
                filterToggleIcon.classList.remove('fa-chevron-down');
                filterToggleIcon.classList.add('fa-chevron-up');
                toggleButton.classList.add('active');
                
                // Animation d'apparition
                advancedFilters.style.opacity = '0';
                advancedFilters.style.transform = 'translateY(-10px)';
                
                requestAnimationFrame(() => {
                    advancedFilters.style.transition = 'all 0.3s ease';
                    advancedFilters.style.opacity = '1';
                    advancedFilters.style.transform = 'translateY(0)';
                });
            } else {
                // Masquer les filtres
                advancedFilters.style.transition = 'all 0.3s ease';
                advancedFilters.style.opacity = '0';
                advancedFilters.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    advancedFilters.classList.add('hidden');
                    filterToggleIcon.classList.remove('fa-chevron-up');
                    filterToggleIcon.classList.add('fa-chevron-down');
                    toggleButton.classList.remove('active');
                }, SEARCH_CONFIG.animationDuration);
            }
        });

        // Afficher les filtres s'ils ont des valeurs
        const hasFilterValues = document.querySelector('#type').value || 
                               document.querySelector('#status').value || 
                               document.querySelector('#date_from').value;
        
        if (hasFilterValues) {
            advancedFilters.classList.remove('hidden');
            filterToggleIcon.classList.remove('fa-chevron-down');
            filterToggleIcon.classList.add('fa-chevron-up');
            toggleButton.classList.add('active');
        }

        // Recherche automatique pour les filtres avancés
        const filterInputs = advancedFilters.querySelectorAll('select, input[type="date"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Recherche automatique lors du changement de filtre
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            });
        });
    }

    /**
     * Affiche un indicateur de recherche en cours
     */
    function showSearchIndicator() {
        const searchContainer = document.querySelector('.bg-white.rounded-xl.shadow-md');
        if (!searchContainer) return;

        // Supprimer l'ancien indicateur s'il existe
        const existingIndicator = searchContainer.querySelector('.search-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        // Créer le nouvel indicateur
        const indicator = document.createElement('div');
        indicator.className = 'search-indicator';
        indicator.innerHTML = `
            <div class="flex items-center justify-center py-2 text-blue-600">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                <span class="text-sm font-medium">Recherche en cours...</span>
            </div>
        `;
        indicator.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 50;
            margin-top: 0.25rem;
        `;

        // Ajouter l'indicateur
        searchContainer.style.position = 'relative';
        searchContainer.appendChild(indicator);

        // Masquer l'indicateur après 2 secondes
        setTimeout(() => {
            if (indicator.parentElement) {
                indicator.remove();
            }
        }, 2000);
    }

    /**
     * Améliore l'expérience utilisateur
     */
    function enhanceUserExperience() {
        // Améliorer les boutons d'action
        enhanceActionButtons();
        
        // Ajouter des animations
        addAnimations();
        
        // Gérer les états de chargement
        handleLoadingStates();
    }

    /**
     * Améliore les boutons d'action
     */
    function enhanceActionButtons() {
        const searchButton = document.querySelector('button[type="submit"]');
        const clearButton = document.querySelector('a[href*="documents.history"]');
        
        if (searchButton) {
            searchButton.classList.add('search-button', 'search-button-primary');
        }
        
        if (clearButton) {
            clearButton.classList.add('search-button', 'search-button-secondary');
        }
    }

    /**
     * Ajoute des animations
     */
    function addAnimations() {
        // Animation d'apparition pour les éléments de recherche
        const searchContainer = document.querySelector('.bg-white.rounded-xl.shadow-md');
        if (searchContainer) {
            searchContainer.style.opacity = '0';
            searchContainer.style.transform = 'translateY(20px)';
            
            requestAnimationFrame(() => {
                searchContainer.style.transition = 'all 0.5s ease';
                searchContainer.style.opacity = '1';
                searchContainer.style.transform = 'translateY(0)';
            });
        }
    }

    /**
     * Gère les états de chargement
     */
    function handleLoadingStates() {
        const form = document.querySelector('form[method="GET"]');
        if (!form) return;

        form.addEventListener('submit', function() {
            const searchInput = this.querySelector('#search');
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (searchInput && submitButton) {
                // Ajouter l'état de chargement
                searchInput.classList.add('search-loading');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Recherche...';
            }
        });
    }

    /**
     * Ajoute des suggestions de recherche (optionnel)
     */
    function addSearchSuggestions() {
        const searchInput = document.getElementById('search');
        if (!searchInput) return;

        // Créer le conteneur de suggestions
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        suggestionsContainer.style.display = 'none';
        searchInput.parentElement.appendChild(suggestionsContainer);

        // Gérer l'affichage des suggestions
        let suggestionTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(suggestionTimeout);
            
            if (this.value.length >= SEARCH_CONFIG.minSearchLength) {
                suggestionTimeout = setTimeout(() => {
                    showSuggestions(this.value, suggestionsContainer);
                }, SEARCH_CONFIG.debounceDelay);
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });

        // Masquer les suggestions lors du clic ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    /**
     * Affiche les suggestions de recherche
     */
    function showSuggestions(query, container) {
        // Suggestions statiques (à personnaliser selon vos besoins)
        const suggestions = [
            { text: 'Contrat', type: 'Type', icon: 'fas fa-file-contract' },
            { text: 'Facture', type: 'Type', icon: 'fas fa-file-invoice' },
            { text: 'Rapport', type: 'Type', icon: 'fas fa-file-alt' },
            { text: 'En attente', type: 'Statut', icon: 'fas fa-clock' },
            { text: 'Signé', type: 'Statut', icon: 'fas fa-check-circle' }
        ];

        // Filtrer les suggestions
        const filteredSuggestions = suggestions.filter(suggestion => 
            suggestion.text.toLowerCase().includes(query.toLowerCase())
        ).slice(0, SEARCH_CONFIG.maxSuggestions);

        // Afficher les suggestions
        if (filteredSuggestions.length > 0) {
            container.innerHTML = filteredSuggestions.map(suggestion => `
                <div class="search-suggestion-item" data-text="${suggestion.text}">
                    <i class="${suggestion.icon} search-suggestion-icon"></i>
                    <span class="search-suggestion-text">${suggestion.text}</span>
                    <span class="search-suggestion-type">${suggestion.type}</span>
                </div>
            `).join('');

            // Gérer les clics sur les suggestions
            container.querySelectorAll('.search-suggestion-item').forEach(item => {
                item.addEventListener('click', function() {
                    const text = this.dataset.text;
                    searchInput.value = text;
                    container.style.display = 'none';
                    searchInput.focus();
                });
            });

            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    /**
     * Améliore l'accessibilité
     */
    function enhanceAccessibility() {
        const searchInput = document.getElementById('search');
        if (!searchInput) return;

        // Ajouter des attributs ARIA
        searchInput.setAttribute('aria-label', 'Recherche globale dans les documents');
        searchInput.setAttribute('aria-describedby', 'search-help');
        
        // Ajouter un texte d'aide
        const helpText = document.createElement('div');
        helpText.id = 'search-help';
        helpText.className = 'text-sm text-gray-500 mt-2';
        helpText.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Recherchez par nom du document, soumissionnaire, type, statut ou description';
        searchInput.parentElement.appendChild(helpText);
    }

    /**
     * Initialisation au chargement de la page
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initSearchImprovements();
            enhanceAccessibility();
            // addSearchSuggestions(); // Décommentez si vous voulez les suggestions
        });
    } else {
        initSearchImprovements();
        enhanceAccessibility();
        // addSearchSuggestions(); // Décommentez si vous voulez les suggestions
    }

    // Export pour utilisation externe
    window.SearchImprovements = {
        init: initSearchImprovements,
        enhanceGlobalSearch: enhanceGlobalSearch,
        handleAdvancedFilters: handleAdvancedFilters,
        enhanceUserExperience: enhanceUserExperience
    };

})();
