# Guide de la Palette de Couleurs d'Entreprise

## 🎨 Palette de Couleurs Principale

### Couleurs Bleues (Primary)
- **blue-900** (`#1e3a8a`) : Texte principal, titres
- **blue-800** (`#1e40af`) : Texte secondaire, sous-titres
- **blue-700** (`#1d4ed8`) : Hover states, états actifs
- **blue-600** (`#2563eb`) : Couleur principale, boutons, liens
- **blue-500** (`#3b82f6`) : Couleur d'accent, icônes
- **blue-400** (`#60a5fa`) : Couleur d'accent claire
- **blue-300** (`#93c5fd`) : Bordures, séparateurs
- **blue-200** (`#bfdbfe`) : Bordures claires
- **blue-100** (`#dbeafe`) : Arrière-plans d'accent
- **blue-50** (`#eff6ff`) : Arrière-plans très clairs

### Couleurs Neutres (Support)
- **white** (`#ffffff`) : Arrière-plans principaux
- **slate-50** (`#f8fafc`) : Arrière-plans secondaires
- **slate-100** (`#f1f5f9`) : Arrière-plans tertiaires
- **slate-200** (`#e2e8f0`) : Bordures principales
- **slate-300** (`#cbd5e1`) : Bordures secondaires

## 🎯 Utilisation des Couleurs

### Textes
- **Titres principaux** : `text-blue-900`
- **Sous-titres** : `text-blue-800`
- **Texte de contenu** : `text-blue-700`
- **Texte secondaire** : `text-blue-600`
- **Texte d'accent** : `text-blue-500`

### Arrière-plans
- **Arrière-plans principaux** : `bg-white`
- **Arrière-plans secondaires** : `bg-blue-50`
- **Arrière-plans d'accent** : `bg-blue-100`
- **Arrière-plans de boutons** : `bg-blue-600`

### Bordures
- **Bordures principales** : `border-blue-200`
- **Bordures d'accent** : `border-blue-300`
- **Bordures de focus** : `border-blue-500`

### Boutons
- **Boutons principaux** : `bg-blue-600 hover:bg-blue-700`
- **Boutons secondaires** : `bg-blue-100 hover:bg-blue-200 text-blue-900`
- **Boutons d'accent** : `bg-blue-500 hover:bg-blue-600`

### États
- **Hover** : `hover:bg-blue-700`, `hover:text-blue-900`
- **Focus** : `focus:ring-blue-500`, `focus:border-blue-500`
- **Active** : `bg-blue-100 text-blue-900`

## 🎨 Classes CSS Personnalisées

### Classes Utilitaires
```css
.corporate-primary     /* Fond bleu principal */
.corporate-secondary   /* Fond bleu secondaire */
.corporate-accent     /* Fond bleu d'accent */
.corporate-bg-primary /* Fond blanc */
.corporate-bg-secondary /* Fond gris très clair */
```

### Classes de Texte
```css
.corporate-primary-text    /* Texte bleu principal */
.corporate-secondary-text  /* Texte bleu secondaire */
.corporate-accent-text     /* Texte bleu d'accent */
```

### Classes de Boutons
```css
.corporate-btn            /* Bouton principal */
.corporate-btn-secondary  /* Bouton secondaire */
```

### Classes de Cards
```css
.corporate-card           /* Card standard */
.corporate-card-hover     /* Card avec hover */
```

## 📱 Responsive Design

### Mobile (< 768px)
- Réduction des espacements
- Boutons plus grands pour le touch
- Texte légèrement plus grand

### Tablet (768px - 1024px)
- Espacements moyens
- Navigation adaptée
- Cards en grille

### Desktop (> 1024px)
- Espacements complets
- Navigation complète
- Layout optimisé

## 🎯 Bonnes Pratiques

### Contraste
- Toujours utiliser un contraste suffisant
- Texte sombre sur fond clair
- Texte clair sur fond sombre

### Cohérence
- Utiliser la même palette partout
- Éviter les couleurs non définies
- Respecter la hiérarchie des couleurs

### Accessibilité
- Respecter les standards WCAG
- Tester avec des outils d'accessibilité
- Assurer la lisibilité

## 🔧 Implémentation

### Tailwind CSS
```html
<!-- Bouton principal -->
<button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
  Action
</button>

<!-- Card -->
<div class="bg-white border border-blue-200 rounded-lg p-6 shadow-sm">
  Contenu
</div>

<!-- Texte -->
<h1 class="text-blue-900 text-2xl font-bold">Titre</h1>
<p class="text-blue-700">Contenu</p>
```

### Classes CSS Personnalisées
```html
<!-- Bouton d'entreprise -->
<button class="corporate-btn">Action</button>

<!-- Card d'entreprise -->
<div class="corporate-card">Contenu</div>

<!-- Navigation d'entreprise -->
<nav class="corporate-nav">
  <a href="#" class="corporate-nav-link">Lien</a>
</nav>
```

## 📊 Exemples d'Utilisation

### Header
- Fond : `bg-white`
- Bordure : `border-blue-200`
- Logo : `text-blue-900`
- Navigation : `text-blue-700 hover:text-blue-900`

### Cards
- Fond : `bg-white`
- Bordure : `border-blue-200`
- Ombre : `shadow-sm`
- Hover : `hover:shadow-md`

### Boutons
- Principal : `bg-blue-600 hover:bg-blue-700 text-white`
- Secondaire : `bg-blue-100 hover:bg-blue-200 text-blue-900`
- Danger : `bg-red-600 hover:bg-red-700 text-white`

### Formulaires
- Input : `border-blue-200 focus:border-blue-500`
- Label : `text-blue-900`
- Erreur : `text-red-600 border-red-300`

Cette palette de couleurs assure une cohérence visuelle parfaite dans toute l'application ! 🎨
