# 📋 Guide d'Installation - Dèmè Travel

## 🚀 Installation Rapide

### 1. Cloner le Repository
```bash
git clone https://github.com/assourita/hadj-umra.git
cd hadj-umra
```

### 2. Installer les Dépendances
```bash
composer install
```

### 3. Configurer l'Environnement
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Éditer .env.local avec vos paramètres
# DATABASE_URL="postgresql://user:password@localhost:5432/omra"
```

### 4. Créer la Base de Données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Créer un Administrateur
```bash
php bin/console app:create-admin
```

### 6. Démarrer le Serveur
```bash
symfony server:start
# ou
php -S localhost:8000 -t public/
```

## 🔧 Configuration Détaillée

### Variables d'Environnement (.env.local)
```env
# Base de données
DATABASE_URL="postgresql://user:password@localhost:5432/omra"

# Sécurité
APP_SECRET="votre-secret-ici"

# Uploads
UPLOAD_DIR="public/uploads/"

# Mailer (optionnel)
MAILER_DSN=smtp://localhost
```

### Permissions des Dossiers
```bash
# Linux/Mac
chmod -R 755 var/
chmod -R 755 public/uploads/

# Windows
# Les permissions sont généralement correctes par défaut
```

## 📊 Données de Test

### Ajouter des Images par Défaut
```bash
php bin/console app:add-default-images-to-packages
```

### Ajouter des Images aux Packages
```bash
php bin/console app:add-package-images
```

### Corriger les Images Existantes
```bash
php bin/console app:fix-package-images
```

## 🌐 Accès à l'Application

### URLs Principales
- **Site public :** http://localhost:8000
- **Administration :** http://localhost:8000/admin
- **Espace client :** http://localhost:8000/client

### Comptes par Défaut
- **Admin :** Utilisez les identifiants créés avec la commande `app:create-admin`
- **Client :** Créez un compte via l'interface d'inscription

## 🐛 Dépannage

### Problèmes Courants

#### 1. Erreur de Base de Données
```bash
# Vérifier la connexion
php bin/console doctrine:database:create --if-not-exists

# Réinitialiser les migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

#### 2. Erreur de Cache
```bash
# Vider le cache
php bin/console cache:clear

# Vider le cache en production
php bin/console cache:clear --env=prod
```

#### 3. Erreur de Permissions
```bash
# Vérifier les permissions
ls -la var/
ls -la public/uploads/

# Corriger si nécessaire
chmod -R 755 var/
chmod -R 755 public/uploads/
```

#### 4. Erreur de Dépendances
```bash
# Réinstaller les dépendances
composer install --no-dev --optimize-autoloader

# Mettre à jour Composer
composer self-update
```

## 🔒 Sécurité

### Production
1. **Changer APP_SECRET**
2. **Configurer HTTPS**
3. **Restreindre les permissions**
4. **Configurer un firewall**
5. **Sauvegarder régulièrement**

### Variables Sensibles
```env
# Ne jamais commiter ces valeurs
APP_SECRET="change-this-in-production"
DATABASE_URL="postgresql://user:password@localhost:5432/omra"
MAILER_DSN="smtp://user:password@smtp.example.com:587"
```

## 📝 Logs

### Vérifier les Logs
```bash
# Logs de développement
tail -f var/log/dev.log

# Logs de production
tail -f var/log/prod.log
```

### Niveau de Log
```env
# Dans .env.local
APP_ENV=dev
APP_DEBUG=true
```

## 🚀 Déploiement

### Préparation Production
```bash
# Optimiser l'environnement
APP_ENV=prod composer install --no-dev --optimize-autoloader

# Vider le cache
php bin/console cache:clear --env=prod

# Vérifier la configuration
php bin/console debug:config
```

### Serveur Web
- **Apache :** Configurer le DocumentRoot vers `public/`
- **Nginx :** Configurer le root vers `public/`
- **Docker :** Utiliser le docker-compose.yml fourni

## 📞 Support

Pour toute question :
- 📧 Email : contact@demetravel.com
- 🌐 Site : https://demetravel.com
- 💬 Issues : https://github.com/assourita/hadj-umra/issues

---

**Installation réussie ! 🎉**
