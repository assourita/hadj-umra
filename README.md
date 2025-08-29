# DƐMƐ Travel - Agence de Voyage Hajj et Umra

## 📋 Description du Projet

DƐMƐ Travel est une agence de voyage spécialisée dans l'organisation de pèlerinages Hajj et Umra. Le projet est développé avec Symfony 6 et propose une plateforme web moderne pour la gestion des packages de voyage spirituel.

## 🏗️ Architecture Actuelle

### Technologies Utilisées
- **Backend**: Symfony 6 (PHP 8+)
- **Frontend**: Twig Templates, Bootstrap 4, jQuery
- **Base de données**: MySQL/PostgreSQL avec Doctrine ORM
- **Authentification**: Lexik JWT Authentication Bundle

### Structure du Projet
```
src/
├── Controller/          # Contrôleurs de l'application
├── Entity/             # Entités Doctrine (Package, News, Contact)
├── Form/               # Formulaires Symfony
├── Repository/         # Repositories Doctrine
└── Command/            # Commandes console personnalisées

templates/
├── base.html.twig      # Template de base
├── travel/             # Templates frontend
└── dashboard/          # Templates admin

public/
├── css/               # Styles CSS
├── js/                # JavaScript
└── images/            # Images et assets
```

## ✅ Fonctionnalités Actuellement Implémentées

### Frontend (Site Public)
- ✅ Page d'accueil avec slider dynamique
- ✅ Section de recherche de packages
- ✅ Affichage des packages Hajj et Umra
- ✅ Section témoignages
- ✅ Formulaire de contact
- ✅ Section actualités
- ✅ Design responsive avec thème sombre
- ✅ Navigation complète

### Backend (Admin)
- ✅ Dashboard administrateur
- ✅ Gestion des packages (CRUD)
- ✅ Gestion des actualités
- ✅ Gestion des contacts
- ✅ Interface d'administration sécurisée

### Entités Principales
- ✅ **Package**: Gestion des packages de voyage
- ✅ **News**: Gestion des actualités
- ✅ **Contact**: Gestion des demandes de contact

## 🚀 Fonctionnalités à Implémenter

### 1. Modification de l'En-tête
- [ ] Remplacer les icônes bus/avion par une icône "véhicule" unique
- [ ] Simplifier la navigation pour se concentrer sur les vols

### 2. Système de Gestion des Packages (Admin)
- [ ] Interface complète CRUD pour les packages
- [ ] Système de publication d'annonces mensuelles
- [ ] Gestion des images et médias
- [ ] Système de catégorisation (Hajj, Umra, VIP)
- [ ] Gestion des prix et promotions

### 3. Système de Réservation
- [ ] Bouton "Détails" sur chaque package
- [ ] Modal/Popup avec informations détaillées
- [ ] Formulaire de réservation avec :
  - Nom, prénom
  - Nombre de personnes
  - Sexe
  - Informations de contact
  - Adresse de livraison

### 4. Système de Paiement
- [ ] Intégration Orange Money
- [ ] Intégration cartes bancaires
- [ ] API de paiement sécurisée
- [ ] Confirmation de paiement
- [ ] Notifications admin

### 5. Système de Feedback
- [ ] Confirmation automatique après paiement
- [ ] Email de confirmation
- [ ] Instructions de livraison
- [ ] Suivi de commande

## 🛠️ Prochaines Étapes de Développement

### Phase 1: Interface Admin Améliorée
1. Créer les contrôleurs pour la gestion complète des packages
2. Implémenter les formulaires de création/modification
3. Ajouter la gestion des images et médias
4. Créer le système d'annonces mensuelles

### Phase 2: Système de Réservation
1. Créer l'entité Reservation
2. Implémenter les modals de détails
3. Créer les formulaires de réservation
4. Ajouter la validation des données

### Phase 3: Intégration Paiement
1. Étudier les APIs Orange Money et cartes bancaires
2. Implémenter les contrôleurs de paiement
3. Créer le système de confirmation
4. Ajouter les notifications

### Phase 4: Système de Feedback
1. Implémenter les emails automatiques
2. Créer le système de suivi
3. Ajouter les notifications admin
4. Tester l'ensemble du workflow

## 📁 Structure des Fichiers à Créer

```
src/
├── Entity/
│   ├── Reservation.php
│   ├── Payment.php
│   └── Announcement.php
├── Controller/
│   ├── ReservationController.php
│   ├── PaymentController.php
│   └── AdminController.php (amélioré)
├── Form/
│   ├── ReservationType.php
│   └── PaymentType.php
└── Service/
    ├── PaymentService.php
    ├── EmailService.php
    └── NotificationService.php

templates/
├── reservation/
│   ├── details.html.twig
│   ├── form.html.twig
│   └── confirmation.html.twig
└── payment/
    ├── form.html.twig
    └── success.html.twig
```

## 🔧 Configuration Requise

### Prérequis
- PHP 8.0+
- Symfony 6.0+
- MySQL 5.7+ 
- Composer
- Node.js (pour les assets)

### Installation
```bash
# Cloner le projet
git clone https://github.com/assourita/hadj-umra.git

# Installer les dépendances
composer install

# Configurer la base de données
# Copier .env.local et configurer DATABASE_URL 

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de test (optionnel)
php bin/console app:create-test-data

# Démarrer le serveur
symfony server:start
```

## 🎨 Thème et Design

Le projet utilise un thème sombre (noir) comme demandé par l'utilisateur, avec :
- Interface moderne et responsive
- Couleurs sombres pour une meilleure expérience utilisateur
- Design adapté aux pèlerinages religieux
- Icônes et images appropriées

## 📞 Contact

Pour toute question ou support technique, contactez l'équipe de développement a ladresse younousskoly01@gmail.com ou au +223 70 81 87 90.

---

**Note**: Ce projet est en développement actif. Les fonctionnalités sont ajoutées progressivement selon les besoins de l'agence de voyage.
