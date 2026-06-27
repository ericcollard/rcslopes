# RC Slopes — Interface d'administration

Interface CRUD pour la base de données `rcslopes` (tables `slopes`, `weather_forecast`, `wind_station`), en **PHP natif** + **Bootstrap 5.3** + **TinyMCE**.

## 1. Prérequis

- PHP 8.1+ avec extensions : `pdo_mysql`, `fileinfo`, `dom`, `libxml`
- MySQL / MariaDB
- Apache avec `mod_rewrite` et `.htaccess` autorisés (`AllowOverride All`)

## 2. Installation

### 2.1. Base de données

```bash
# Importer d'abord le dump original du site
mysql -u root -p rcslopes < rcslopes_2026-06-25.sql

# Puis le schéma additionnel (table administrators, login_attempts)
mysql -u root -p rcslopes < admin/sql/00_admin_schema.sql
```

### 2.2. Configuration

Définissez les variables d'environnement (ou modifiez directement `admin/config/config.php`) :

| Variable        | Description                  | Défaut          |
|------------------|-------------------------------|------------------|
| `RCS_DB_HOST`    | Hôte MySQL                    | `127.0.0.1`      |
| `RCS_DB_PORT`    | Port MySQL                    | `3306`           |
| `RCS_DB_NAME`    | Nom de la base                | `rcslopes`       |
| `RCS_DB_USER`    | Utilisateur MySQL              | `root`           |
| `RCS_DB_PASS`    | Mot de passe MySQL             | _(vide)_         |
| `RCS_DEBUG`      | Affiche les erreurs si `1`     | _(désactivé)_    |

### 2.3. Déploiement des fichiers

Placez le dossier `admin/` à la racine de votre site, de façon à ce qu'il soit accessible via `https://votre-domaine.tld/admin/`.

Assurez-vous que `admin/assets/images/` est accessible en écriture par le serveur web :

```bash
chmod 755 admin/assets/images
chown www-data:www-data admin/assets/images   # adapter à votre utilisateur serveur
```

### 2.4. Initialisation du compte administrateur

1. Ouvrez `https://votre-domaine.tld/admin/install.php`
2. Cliquez sur "Initialiser le mot de passe par défaut"
3. Connectez-vous avec :
   - Email : `admin@rcslopes.local`
   - Mot de passe : `ChangeMoi123!`
4. **Changez immédiatement ce mot de passe** depuis "Mon profil"
5. **Supprimez le fichier `install.php`** du serveur

## 3. Gestion des privilèges

Deux rôles sont disponibles pour chaque administrateur :

- **Éditeur** : peut créer, modifier et supprimer les données (sites, prévisions météo, stations de vent) et gérer la bibliothèque d'images.
- **Administrateur** : tout ce que fait l'éditeur, plus la gestion des comptes administrateurs (création, modification, suppression).

La gestion des comptes se fait depuis le menu **Administrateurs** (visible uniquement par les comptes ayant le rôle Administrateur).

## 4. Ajouter une nouvelle table au CRUD

Le CRUD est entièrement piloté par un registre déclaratif : `admin/includes/TableRegistry.php`.

Pour ajouter une nouvelle table, ajoutez une entrée dans le tableau retourné par `TableRegistry::all()`, en suivant le même format que les tables existantes (clé primaire, colonnes, types de champ, libellés). Aucune autre modification de code n'est nécessaire : la liste, le formulaire, la recherche et la pagination s'adaptent automatiquement.

Types de champ disponibles : `text`, `textarea`, `wysiwyg`, `number`, `decimal`, `checkbox`, `select`, `select_multiple`, `lookup`, `datetime`, `date`, `hidden`.

## 5. Sécurité — points clés

- Mots de passe hashés avec `bcrypt` (`password_hash`/`password_verify`)
- Protection CSRF sur tous les formulaires (jeton par session)
- Protection brute-force basique sur le login (5 tentatives / 15 min par email+IP)
- Tout le contenu HTML saisi via TinyMCE est nettoyé côté serveur (`HtmlSanitizer`) avant stockage : balises et attributs dangereux supprimés, gestionnaires d'événements `on*` bloqués, URLs `javascript:`/`data:` non-image rejetées
- Upload d'images : vérification du type MIME réel du fichier (pas de l'extension), validation que le contenu est une image décodable, noms de fichiers regénérés aléatoirement
- Les répertoires `config/`, `includes/`, `sql/` sont protégés par `.htaccess` (`Require all denied`)
- Le répertoire `assets/images/` interdit l'exécution de scripts, même déposés par erreur

⚠️ **Recommandation** : en production, remplacez `HtmlSanitizer` (implémentation maison) par [HTMLPurifier](http://htmlpurifier.org/) via Composer pour une couverture XSS plus exhaustive — voir la note en fin de fichier `includes/HtmlSanitizer.php`.

## 6. Structure des fichiers

```
admin/
├── api/
│   └── upload_image.php       # Endpoint JSON pour l'upload depuis TinyMCE
├── assets/
│   ├── css/admin.css
│   ├── js/admin.js
│   └── images/                 # Stockage des images uploadées
├── config/
│   ├── config.php               # Constantes (DB, sécurité, upload)
│   └── database.php             # Connexion PDO (singleton)
├── includes/
│   ├── Auth.php                 # Session, login, CSRF, privilèges
│   ├── AdminManager.php         # CRUD comptes administrateurs
│   ├── CrudEngine.php           # Moteur CRUD générique
│   ├── FormProcessor.php        # Validation/nettoyage des données de formulaire
│   ├── HtmlSanitizer.php        # Nettoyage HTML (anti-XSS) pour les champs WYSIWYG
│   ├── ImageManager.php         # Upload/listing/suppression d'images
│   ├── TableRegistry.php        # Registre déclaratif des tables administrables
│   ├── helpers.php
│   ├── bootstrap.php            # Point d'entrée commun (require + session)
│   └── views/                   # Fragments de vue (header, sidebar, formulaires...)
├── pages/
│   └── 403.php
├── sql/
│   └── 00_admin_schema.sql      # Tables administrators + login_attempts
├── admins.php                   # Gestion des comptes administrateurs
├── images.php                   # Bibliothèque d'images
├── index.php                    # Tableau de bord
├── install.php                  # Script d'installation (à supprimer après usage)
├── login.php
├── logout.php
├── profile.php
└── table.php                    # CRUD générique (liste + formulaire) pour toute table du registre
```
