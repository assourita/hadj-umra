# Omra Himra - Site de Réservation

Site web professionnel de vente et gestion de voyages Omra/Himra développé avec Symfony 7.3.

## 📋 Fonctionnalités

### MVP (Version 1.0)
- ✅ **Authentification & Sécurité** : Inscription, connexion, gestion des rôles
- ✅ **Catalogue & Packages** : CRUD packages, gestion des départs, tarifs par chambre
- ✅ **Réservation** : Wizard de réservation en 3 étapes, génération code dossier
- ✅ **Espace Client** : Dashboard, upload documents, suivi réservations
- ✅ **Paiement** : Intégration Stripe, gestion acompte/solde
- ✅ **Back-office** : Dashboard admin, gestion dossiers, exports
- ✅ **API REST** : Endpoints pour intégrations futures

### Fonctionnalités Avancées (V2)
- 🔄 **Mobile Money** : Orange Money, MTN, Moov
- 🔄 **Multi-devises** : XOF, EUR, USD, SAR
- 🔄 **PWA** : Application web progressive
- 🔄 **WhatsApp API** : Notifications automatisées

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- Docker & Docker Compose
- Node.js (pour les assets)

### Installation avec Docker (Recommandé)

1. **Cloner le projet**
```bash
git clone https://github.com/votre-repo/omra-himra-site.git
cd omra-himra-site
```

2. **Configurer l'environnement**
```bash
cp .env .env.local
# Éditer .env.local avec vos configurations
```

3. **Démarrer les services**
```bash
docker-compose up -d
```

4. **Installer les dépendances**
```bash
docker-compose exec app composer install
```

5. **Créer la base de données**
```bash
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
```

6. **Charger les données de test**
```bash
docker-compose exec app php bin/console doctrine:fixtures:load
```

### Installation Manuelle

1. **Installer les dépendances**
```bash
composer install
npm install
```

2. **Configurer la base de données**
```bash
# Créer la base PostgreSQL
createdb omra_himra_db

# Configurer .env.local
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/omra_himra_db"
```

3. **Migrations et fixtures**
```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

4. **Démarrer le serveur**
```bash
symfony server:start
```

## 🏗️ Architecture

### Structure du Projet
```
src/
├── Controller/          # Contrôleurs web et API
│   ├── Api/            # Contrôleurs API REST
│   ├── Admin/          # Back-office administration
│   └── ...
├── Entity/             # Entités Doctrine
├── Repository/         # Repositories personnalisés
├── Service/            # Services métier
└── ...

templates/
├── base.html.twig      # Template de base
├── home/               # Pages publiques
├── package/            # Catalogue packages
├── reservation/        # Wizard réservation
├── client/             # Espace client
├── admin/              # Back-office
└── security/           # Authentification

config/
├── packages/           # Configuration Symfony
├── routes/             # Routes
└── ...
```

### Modèle de Données

```mermaid
erDiagram
    User ||--o{ Reservation : "fait"
    Package ||--o{ Depart : "a"
    Depart ||--o{ Tarif : "a"
    Depart ||--o{ Reservation : "pour"
    Reservation ||--o{ Pelerin : "contient"
    Reservation ||--o{ Paiement : "a"
    Pelerin ||--o{ Document : "a"
    Pelerin ||--o| Visa : "a"
    Pelerin ||--o| Billet : "a"
```

## 🔧 Configuration

### Variables d'Environnement

```bash
# Base de données
DATABASE_URL="postgresql://user:pass@host:5432/db"

# Stripe
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email
MAILER_DSN=smtp://localhost:1025

# Upload
UPLOAD_PATH=%kernel.project_dir%/public/uploads
```

### Rôles Utilisateurs

- **ROLE_USER** : Client/Pèlerin
- **ROLE_GUIDE** : Guide/Accompagnateur
- **ROLE_COMPTABLE** : Comptable
- **ROLE_ADMIN** : Responsable Agence
- **ROLE_SUPER_ADMIN** : Super Administrateur

## 📡 API Endpoints

### Packages
- `GET /api/packages` - Liste des packages avec filtres
- `GET /api/packages/{slug}` - Détail d'un package
- `GET /api/packages/search?q=term` - Recherche packages

### Départs
- `GET /api/departs/{id}` - Détail d'un départ

### Réservations
- `POST /api/reservations` - Créer une réservation
- `GET /api/reservations/{id}` - Détail réservation
- `POST /api/reservations/calculate` - Calculer prix

### Paiements
- `POST /api/payments/intent` - Créer Payment Intent Stripe
- `POST /api/payments/webhook` - Webhook Stripe

### Documents
- `POST /api/documents` - Upload document

## 🧪 Tests

### Tests Unitaires
```bash
php bin/phpunit tests/Unit/
```

### Tests Fonctionnels
```bash
php bin/phpunit tests/Functional/
```

### Tests d'Intégration
```bash
php bin/phpunit tests/Integration/
```

## 🚀 Déploiement

### Environnement de Staging
```bash
# Build et déploiement
docker-compose -f docker-compose.staging.yml up -d

# Migrations
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Cache
docker-compose exec app php bin/console cache:clear --env=prod
```

### Environnement de Production
```bash
# Variables d'environnement production
APP_ENV=prod
APP_DEBUG=false
DATABASE_URL="postgresql://prod_user:prod_pass@db:5432/omra_himra_prod"

# Optimisations
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

## 📊 Monitoring

### Logs
- Application : `var/log/`
- Nginx : `/var/log/nginx/`
- PostgreSQL : Logs Docker

### Métriques
- Dashboard admin : `/admin/dashboard`
- Symfony Profiler : `/_profiler` (dev uniquement)

## 🔒 Sécurité

### Mesures Implémentées
- HTTPS obligatoire (production)
- Protection CSRF
- Validation serveur pour uploads
- Headers de sécurité
- Audit logs

### Sauvegarde
```bash
# Base de données
pg_dump omra_himra_db > backup_$(date +%Y%m%d).sql

# Fichiers uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/uploads/
```

## 📱 Accès aux Services

### Développement
- **Application** : http://localhost:8080
- **API** : http://localhost:8080/api
- **Adminer** : http://localhost:8081
- **MailHog** : http://localhost:8025
- **MinIO** : http://localhost:9001

### Comptes de Test
- **Admin** : admin@omra-himra.com / admin123
- **Client** : client@omra-himra.com / client123
- **Comptable** : comptable@omra-himra.com / comptable123

## 🛠️ Maintenance

### Commandes Utiles
```bash
# Nettoyer le cache
php bin/console cache:clear

# Mise à jour du schéma
php bin/console doctrine:schema:update --force

# Créer un utilisateur admin
php bin/console app:create-admin

# Export des réservations
php bin/console app:export:reservations --format=csv

# Envoi des rappels de paiement
php bin/console app:send:payment-reminders
```

## 📞 Support

### Contact Projet
- **Nom** : Sitan Youssouf Sanogo
- **Email** : sitansanogoy@gmail.com
- **Téléphone** : +223 91672992

### Garantie
- Correction des bugs bloquants : 2 semaines après livraison
- Support technique : 2-4 heures/mois (option maintenance)

## 📄 Licence

Propriétaire - Tous droits réservés Omra Himra © 2025 