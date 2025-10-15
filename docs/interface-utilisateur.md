# Interface Utilisateur - GESEC Gestion de Contrat

Ce document décrit les principes d'interface utilisateur et les flux de navigation de l'application GESEC Gestion de Contrat.

## Principes généraux

L'interface de l'application est conçue selon les principes suivants :
- Design responsive adapté aux ordinateurs de bureau et tablettes
- Interface intuitive "à taille humaine", sans surcharge d'informations
- Navigation par onglets clairement identifiés
- Interfaces adaptées aux rôles des utilisateurs
- Support des opérations de type tableur pour la saisie de données

## Structure générale de l'interface

### En-tête
- Logo GESEC
- Titre de l'application
- Menu principal de navigation
- Nom de l'utilisateur connecté
- Bouton de déconnexion

### Barre de navigation principale
- Clients
- Sites
- Affaires
- Affectations
- Saisie quantités

### Zone de contenu principale
- Contenu dynamique selon la section sélectionnée
- Largeur adaptable selon le contenu (ex: formulaire vs. tableur)

### Pied de page
- Version de l'application
- Copyright
- Liens vers aide/support

## Interfaces par rôle

### Administrateur système

#### Tableau de bord
- Statistiques générales (nombre d'utilisateurs, clients, affaires)
- Activités récentes
- État du système

#### Gestion des utilisateurs
- Liste des utilisateurs avec filtres (rôle, statut, nom)
- Formulaire de création/modification d'utilisateur
- Boutons d'action (réinitialiser mot de passe, désactiver/activer)

### Utilisateur GESEC

#### Clients
- Liste des clients avec filtres et recherche
- Fiche client détaillée
- Formulaire de création/modification de client

#### Sites
- Liste des sites par client avec filtres
- Formulaire de création/modification de site
- Interface d'import CSV avec prévisualisation
- Carte géographique des sites (optionnel)

#### Affaires
- Liste des affaires avec filtres (client, statut, date)
- Fiche affaire avec ses caractéristiques
- Interface d'ajout et de gestion des BPU directement dans l'affaire
- Interface de valorisation des BPU (prix, fréquence)
- Options de changement de statut et duplication
- Accès à l'affaire de référence contenant les BPU standards

#### Affectations
- Interface de sélection d'affaire
- Tableau des sites de l'affaire
- Formulaire d'affectation d'adhérents aux sites
- Configuration du type de prise en charge (Multitech, ELEC, CVC)

### Adhérent

#### Mes affaires
- Liste des affaires où l'adhérent est affecté
- Filtres par statut et date
- Détails de l'affaire en lecture seule

#### Saisie des quantités
- Sélection d'une affaire et d'un site
- Interface de type tableur avec :
  - Lignes = BPU de l'affaire
  - Colonnes = informations (description, prix, quantité, total)
- Cellules éditables pour les quantités
- Support copier/coller depuis Excel
- Calcul automatique des montants

## Flux utilisateurs

### Premier accès
1. L'utilisateur reçoit ses identifiants par email
2. Il se connecte avec le mot de passe temporaire
3. Il est invité à changer son mot de passe
4. Il accède au tableau de bord correspondant à son rôle

### Flux GESEC - Création d'une affaire
1. Création/sélection du client
2. Création/import des sites du client
3. Création de l'affaire (informations générales)
4. Ajout des BPU directement dans l'affaire et valorisation (prix, fréquence)
   - Option d'importer des BPU depuis l'affaire de référence
5. Affectation des sites aux adhérents
6. Changement de statut de l'affaire (Offre → Contrat)

### Flux Adhérent - Saisie des quantités
1. Accès à la liste des affaires assignées
2. Sélection d'une affaire
3. Sélection d'un site à sa charge
4. Saisie des quantités par BPU (manuelle ou copier/coller)
5. Sauvegarde des données saisies

## Composants d'interface spécifiques

### Interface tableur pour BPU et quantités
- Grille de données interactive avec colonnes redimensionnables
- Support du tri et filtrage
- Cellules éditables avec validation
- Support du copier/coller multi-cellules
- Navigation au clavier (tabulation entre cellules)
- Calculs automatiques (ex: quantité × prix)

### Interface d'importation CSV
- Zone de glisser-déposer pour le fichier
- Tableau de prévisualisation des données
- Mapping des colonnes CSV aux champs de l'application
- Validation et affichage des erreurs
- Confirmation avant import final

### Interface d'affectation des sites
- Vue synthétique des sites et adhérents
- Sélection facile des adhérents (menu déroulant ou recherche)
- Choix du type de prise en charge par cases à cocher
- Possibilité d'affecter en masse plusieurs sites

## Considérations d'accessibilité et d'ergonomie

- Contrastes suffisants pour la lisibilité
- Taille de police adaptable
- Messages d'erreur clairs et explicites
- Confirmation avant actions destructives
- Sauvegarde automatique des saisies en cours
- Indicateurs de chargement pour les opérations longues