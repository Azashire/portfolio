# Portfolio — Zacharie Cicorella-Revol

Portfolio personnel développé en PHP/MySQL, hébergé sur [alwaysdata](https://zacharie-cicorella.alwaysdata.net/index.php).

## Fonctionnalités

- Présentation personnelle et parcours
- Page projets avec recherche et filtrage par tags
- Modale détaillée par projet (description longue en HTML, image, date, équipe, durée)
- Projets phares mis en avant sur la page d'accueil
- Interface d'administration (connexion requise) : ajouter, modifier, supprimer des projets et des tags

## Stack technique

- **PHP 8** — logique serveur
- **MySQL** — base de données
- **PDO** — accès BDD avec prepared statements
- **HTMLPurifier** — nettoyage du HTML dans les descriptions longues
- **Bootstrap Icons** — icônes
- **CSS / JS vanilla** — pas de framework front

## Structure des fichiers

```
portfolio/
├── index.php                   ← page d'accueil
├── db.php                      ← connexion BDD (à configurer, non commité)
├── db.example.php              ← modèle de db.php
├── auth.php                    ← garde de session pour les pages admin
├── composer.json
├── vendor/                     ← dépendances (non commité)
│
│
├── projects.php                ← pages
├── cv.php
├── contact.php
├── login.php
├── logout.php
│
├── ajouter_projet.php          ← pages admin
├── modifier_projet.php
├── gerer_tags.php
│
├── head.php                    ← includes
├── header.php                  
├── navbar.php
├── footer.php
│
├── assets/
│   ├── css/css.css
│   ├── js/projects.js
│   └── img/
│
├── uploads/                    ← images des projets (non commité)
│
└── setup.sql                   ← création des tables

```

## Installation en local

### Prérequis
- XAMPP (Apache + MySQL + PHP 8+)
- Composer

### Étapes

**1. Cloner le projet**
```bash
git clone https://github.com/Azashire/portfolio.git
cd portfolio
```

**2. Installer les dépendances**
```bash
composer install
```

**3. Configurer la base de données**

Copie `db.example.php` en `db.php` et remplir les valeurs :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**4. Créer la base de données**

Dans PHPMyAdmin (`http://localhost/phpmyadmin`) :
- Créer une base nommée `portfolio`
- Onglet SQL → coller le contenu de `sql/setup.sql` → Exécuter

**5. Créer le compte admin**

Créer un fichier temporaire `gen_hash.php` à la racine :
```php
<?php echo password_hash('ton_mot_de_passe', PASSWORD_DEFAULT);
```
Ouvrir `http://localhost/portfolio/gen_hash.php`, copier le hash affiché, puis dans PHPMyAdmin exécuter :
```sql
UPDATE admin SET mot_de_passe = 'LE_HASH_COPIE' WHERE identifiant = 'admin';
```
Supprimer `gen_hash.php` ensuite.

**6. Créer le dossier uploads**
```bash
mkdir uploads
```

**7. Lancer**

Ouvre `http://localhost/portfolio/index.php`.

## Déploiement sur alwaysdata

**1.** Dans le panel alwaysdata → Bases de données → MySQL : crée une base et un utilisateur.

**2.** Importer `sql/setup.sql` via phpMyAdmin alwaysdata.

**3.** Dans `db.php`, remplacer les valeurs par celles d'alwaysdata :
```php
define('DB_HOST', 'mysql-toncompte.alwaysdata.net');
define('DB_NAME', 'toncompte_portfolio');
define('DB_USER', 'toncompte_user');
define('DB_PASS', 'ton_mot_de_passe');
```

**4.** Upload les fichiers dans `www/` via FTP (FileZilla ou WinSCP).

## Fichiers non commités

| Fichier / Dossier | Raison |
|---|---|
| `db.php` | Contient les identifiants BDD |
| `config.ini` | Identifiants BDD (alternative) |
| `vendor/` | Dépendances Composer, trop lourdes |
| `uploads/` | Images uploadées, données personnelles |
| `gen_hash.php` | Fichier dangereux, à ne jamais laisser en ligne |
