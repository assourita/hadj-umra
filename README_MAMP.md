# Configuration Omra Himra avec MAMP

Ce guide vous explique comment configurer le projet Omra Himra pour utiliser MAMP avec MySQL.

## Prérequis

1. **MAMP** installé et configuré
2. **PHP 8.2+** (inclus avec MAMP)
3. **Composer** installé
4. **Symfony CLI** installé (optionnel, pour le serveur de développement)

## Configuration de la base de données

### 1. Démarrer MAMP
- Lancez MAMP
- Démarrez les services Apache et MySQL
- Ouvrez phpMyAdmin à l'adresse : http://localhost/phpMyAdmin

### 2. Créer la base de données
- Dans phpMyAdmin, créez une nouvelle base de données nommée `omra`
- Ou utilisez la base existante si elle existe déjà

### 3. Configuration du projet

Le fichier `.env.local` a été créé avec la configuration suivante :

```env
DATABASE_URL="mysql://root:root@127.0.0.1:3306/omra?serverVersion=8.0.32&charset=utf8mb4"
APP_ENV=dev
APP_SECRET=votre_secret_ici
STRIPE_SECRET_KEY=sk_test_votre_cle_stripe_ici
```

**Note :** Si votre MAMP utilise un mot de passe différent pour l'utilisateur `root`, modifiez la ligne `DATABASE_URL` en conséquence.

## Installation et configuration

### 1. Installer les dépendances
```bash
composer install
```

### 2. Créer la base de données (si elle n'existe pas)
```bash
php bin/console doctrine:database:create
```

### 3. Exécuter les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 4. Créer un utilisateur administrateur
```bash
php bin/console app:create-admin
```

### 5. Vider le cache
```bash
php bin/console cache:clear
```

## Démarrer l'application

### Option 1 : Serveur Symfony (recommandé)
```bash
symfony server:start
```
L'application sera accessible sur : http://127.0.0.1:8000

### Option 2 : Serveur MAMP
- Placez le projet dans le dossier `htdocs` de MAMP
- Accédez à : http://localhost/omra-himra-site/public/

## Accès à l'application

- **URL principale** : http://127.0.0.1:8000
- **Administration** : http://127.0.0.1:8000/admin
- **Connexion** : http://127.0.0.1:8000/login

### Compte administrateur créé
- **Email** : admin@omra.com
- **Mot de passe** : password123
- **Rôle** : ROLE_SUPER_ADMIN

## Structure de la base de données

Le projet utilise les tables suivantes :
- `user` - Utilisateurs du système
- `package` - Packages de voyage
- `depart` - Départs programmés
- `tarif` - Tarifs par type de chambre
- `reservation` - Réservations
- `pelerin` - Informations des pèlerins
- `paiement` - Paiements
- `document` - Documents des pèlerins
- `visa` - Visas
- `billet` - Billets d'avion

## Configuration Stripe

Pour les paiements, vous devez configurer une clé Stripe valide dans le fichier `.env.local` :

```env
STRIPE_SECRET_KEY=sk_test_votre_cle_stripe_ici
```

Remplacez `sk_test_votre_cle_stripe_ici` par votre vraie clé de test Stripe.

## Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MAMP est démarré
- Vérifiez les identifiants dans `DATABASE_URL`
- Vérifiez que la base de données `omra` existe

### Erreur de permissions
- Assurez-vous que le dossier `var/` est accessible en écriture

### Erreur de cache
```bash
php bin/console cache:clear
```

## Commandes utiles

```bash
# Vérifier la configuration de la base de données
php bin/console doctrine:schema:validate

# Créer un nouvel administrateur
php bin/console app:create-admin

# Voir les routes disponibles
php bin/console debug:router

# Vider le cache
php bin/console cache:clear
``` 