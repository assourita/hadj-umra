# 🕌 Dèmè Travel - Agence de Pèlerinage

Agence de voyage spécialisée dans l'organisation de pèlerinages Umra et Hadj.

## 📋 Description

Dèmè Travel est une application web moderne développée avec Symfony 7.3, dédiée à la gestion complète des pèlerinages vers les lieux saints de l'Islam. L'application offre une interface complète pour les administrateurs et les clients.

## ✨ Fonctionnalités Principales

### 🏢 Administration
- **📊 Dashboard complet** avec statistiques en temps réel
- **🕌 Gestion des packages** (création, modification, suppression)
- **✈️ Gestion des départs** avec quotas et disponibilités
- **💰 Gestion des tarifs** par type de chambre
- **📋 Gestion des réservations** avec workflow complet
- **👥 Gestion des utilisateurs** et pèlerins
- **📄 Gestion des documents** requis
- **💬 Gestion des messages** de contact
- **📊 Rapports** financiers et statistiques

### 👤 Espace Client
- **📊 Dashboard personnel** avec réservations
- **📋 Suivi des réservations** en temps réel
- **📄 Gestion des documents** requis
- **💬 Messagerie** avec l'équipe
- **👤 Profil utilisateur** personnalisable

### 🌐 Site Public
- **🏠 Page d'accueil** attractive
- **🕌 Catalogue des packages** avec recherche
- **📞 Formulaire de contact** intégré
- **🗺️ Carte interactive** avec localisation
- **❓ FAQ** complète

## 🛠️ Technologies Utilisées

- **Backend :** Symfony 7.3, PHP 8.2+
- **Base de données :** PostgreSQL / MySQL
- **Frontend :** Twig, JavaScript, CSS3
- **Cartes :** Leaflet.js (OpenStreetMap)
- **Validation :** Symfony Validator
- **Sécurité :** Symfony Security Bundle

## 📦 Installation

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- PostgreSQL ou MySQL
- Symfony CLI (optionnel)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/assourita/hadj-umra.git
cd hadj-umra
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Modifier .env.local avec vos paramètres de base de données
DATABASE_URL="postgresql://user:password@localhost:5432/omra"
```

4. **Créer la base de données et exécuter les migrations**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Créer un utilisateur administrateur**
```bash
php bin/console app:create-admin
```

6. **Ajouter des données de test (optionnel)**
```bash
php bin/console app:add-default-images-to-packages
php bin/console app:add-package-images
```

7. **Démarrer le serveur de développement**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public/
```

## 🔧 Configuration

### Variables d'environnement importantes
```env
# Base de données
DATABASE_URL="postgresql://user:password@localhost:5432/omra"

# Sécurité
APP_SECRET="votre-secret-ici"

# Uploads
UPLOAD_DIR="public/uploads/"
```

### Permissions des dossiers
```bash
chmod -R 755 var/
chmod -R 755 public/uploads/
```

## 📁 Structure du Projet

```
hadj-umra/
├── src/
│   ├── Controller/          # Contrôleurs
│   ├── Entity/             # Entités Doctrine
│   ├── Repository/         # Repositories
│   ├── Service/            # Services métier
│   └── Command/            # Commandes console
├── templates/              # Templates Twig
│   ├── admin/             # Interface admin
│   ├── client/            # Interface client
│   └── home/              # Pages publiques
├── public/                # Fichiers publics
│   ├── uploads/           # Uploads utilisateurs
│   └── assets/            # Assets statiques
├── config/                # Configuration
├── migrations/            # Migrations base de données
└── var/                   # Cache et logs
```

## 🚀 Déploiement

### Production
1. **Optimiser l'environnement**
```bash
APP_ENV=prod composer install --no-dev --optimize-autoloader
```

2. **Vider le cache**
```bash
php bin/console cache:clear --env=prod
```

3. **Configurer le serveur web** (Apache/Nginx)

### Docker (optionnel)
```bash
docker-compose up -d
```

## 📊 Fonctionnalités Avancées

### Workflow des Réservations
1. **Création** par le client
2. **Validation** par l'administrateur
3. **Documents** requis
4. **Paiement** et confirmation
5. **Suivi** jusqu'au départ

### Gestion des Images
- **Upload multiple** pour les packages
- **Redimensionnement** automatique
- **Organisation** par type
- **Prévisualisation** en temps réel

### Système de Messagerie
- **Messages clients** avec statuts
- **Réponses administrateurs** tracées
- **Notifications** en temps réel

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou support :
- 📧 Email : contact@demetravel.com
- 🌐 Site web : https://demetravel.com
- 💬 Issues GitHub : [Créer une issue](https://github.com/assourita/hadj-umra/issues)

## 🙏 Remerciements

- **Symfony** pour le framework exceptionnel
- **Doctrine** pour l'ORM puissant
- **Twig** pour le moteur de templates
- **OpenStreetMap** pour les cartes gratuites
- **Leaflet.js** pour l'interactivité des cartes

---

**Développé avec ❤️ pour la communauté musulmane** 