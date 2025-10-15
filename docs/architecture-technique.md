# Architecture Technique - GESEC Gestion de Contrat

## Vue d'ensemble

L'application GESEC Gestion de Contrat est une application web développée avec le framework Symfony et une base de données PostgreSQL. Elle adopte une architecture MVC (Modèle-Vue-Contrôleur) pour faciliter la maintenance et l'évolution du code.

## Stack technologique

### Backend
- **Framework**: Symfony 7.3.x
- **Langage**: PHP 8.4
- **Base de données**: PostgreSQL
- **ORM**: Doctrine
- **Authentification**: Symfony Security Bundle
- **Gestion des formulaires**: Symfony Form
- **Validation**: Symfony Validator
- **Import/Export CSV**: League CSV

### Frontend
- **Framework CSS**: Bootstrap
- **JavaScript**: 
  - Stimulus.js pour les interactions légères
  - Handsontable pour l'interface type tableur
- **Templating**: Twig
- **Asset Management**: Webpack encore

### Infrastructure
- **Environnement de développement**: Docker (via docker-compose)
- **Serveur web**: Nginx ou Apache
- **Cache**: APCu ou Redis (selon besoins)

## Architecture applicative

```
GESEC Gestion de Contrat
|
├── Interface utilisateur (Twig + Stimulus + Bootstrap)
|
├── Contrôleurs (Controllers Symfony)
|   ├── SecurityController (gestion authentification)
|   ├── UserController (gestion des utilisateurs)
|   ├── ClientController (gestion des clients)
|   ├── SiteController (gestion des sites)
|   ├── AffaireController (gestion des affaires et des BPU intégrés)
|   └── AffectationController (gestion des affectations)
|
├── Services
|   ├── ImportService (import CSV)
|   ├── AffaireService (logique métier des affaires)
|   ├── AffectationService (logique métier des affectations)
|   └── BpuService (logique métier des BPU intégrés aux affaires)
|
├── Entités (Doctrine)
|   ├── User (utilisateurs et rôles)
|   ├── Adherent (informations sur les adhérents)
|   ├── Client
|   ├── Site
|   ├── AffaireMaster (identité métier d'une affaire - code)
|   ├── AffaireVersion (versions de l'affaire, contient les BPU/paramètres pour une version donnée)
|   ├── AffaireBpu (BPU attachés à une `AffaireVersion`)
|   ├── AffaireSiteAdherent (lien site-adhérent-affaire pour une version donnée)
|   └── ContratApplication (quantités par site et BPU référencées sur une `AffaireVersion`)
|
└── Repositories (Doctrine)
    ├── UserRepository
    ├── AdherentRepository
    ├── ClientRepository
    ├── SiteRepository
    ├── AffaireRepository
    ├── AffaireBpuRepository
    ├── AffaireSiteAdherentRepository
    └── ContratApplicationRepository
```

## Sécurité

L'application implémente une gestion des droits basée sur les rôles :
- **ROLE_ADMIN** : Administrateur système avec accès complet
- **ROLE_GESEC** : Utilisateur GESEC avec accès à toutes les données mais permissions limitées
- **ROLE_ADHERENT** : Adhérent avec accès uniquement à ses affaires assignées

La sécurité est gérée via le bundle Symfony Security, avec :
- Authentification par formulaire
- Protection CSRF
- Encodage sécurisé des mots de passe
- Filtrage des accès aux ressources selon les rôles
- Restriction d'accès aux données basée sur le contexte utilisateur (voter Symfony)

## Interfaces utilisateur spécifiques

### Interface de type tableur
- Utilisation d'une bibliothèque JavaScript performante (Handsontable)
- Support du copier-coller depuis Excel (fait par Handsontable)
- Cellules éditables avec validation en temps réel
- Gestion de grandes quantités de données avec pagination côté serveur

### Importation CSV
- Interface de téléchargement avec prévisualisation
- Mapping des colonnes
- Validation des données et rapport d'erreurs
- Confirmation avant import final

## Performance et scalabilité

L'application est conçue pour gérer :
- Plusieurs centaines d'affaires
- Milliers de lignes BPU intégrés aux affaires
- Dizaines d'utilisateurs simultanés

Optimisations prévues :
- Indexation stratégique des tables SQL
- Mise en cache des données fréquemment accédées
- Requêtes SQL optimisées via Doctrine
- Pagination pour les listes volumineuses
- Chargement asynchrone des données volumineuses

## Environnements

### Développement
- Docker avec services containerisés (PHP-FPM, PostgreSQL, Nginx)
- Configuration locale via .env.local
- Fixtures pour données de test

### Production
- Serveur dédié ou mutualisé
- Base de données PostgreSQL
- Variables d'environnement sécurisées
- Logs et monitoring

## Tests

- Tests unitaires avec PHPUnit
- Tests fonctionnels avec navigateur headless
- Tests d'intégration pour les fonctionnalités critiques

## Déploiement

- Déploiement via Git
- Gestion des migrations de base de données avec Doctrine Migrations
- Scripts de déploiement automatisés 