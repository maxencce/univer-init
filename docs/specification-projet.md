# Spécifications du projet GESEC Gestion de Contrat

## Présentation générale

Le projet GESEC Gestion de Contrat est une application web destinée à simplifier la gestion des contrats de maintenance dans le secteur de l'électricité et du CVC (Chauffage, Ventilation, Climatisation). L'objectif est de fournir un outil "à taille humaine" permettant à l'association GESEC et ses adhérents de centraliser les données contractuelles, les prestations (Bordereaux de Prix Unitaires - BPU) et les répartitions de responsabilités entre les parties prenantes.

## Objectifs du projet

- Centraliser la gestion des contrats pour tous les clients du GESEC
- Permettre une vision globale des affaires pour les membres du GESEC
- Offrir un outil de travail simple mais puissant pour l'édition, le suivi et l'archivage des contrats
- Faciliter la gestion des affectations d'adhérents aux sites clients
- Simplifier la saisie des quantités BPU grâce à une interface type tableur

## Fonctionnalités clés

### Gestion des utilisateurs et rôles
- Création, modification et désactivation des comptes utilisateur
- Trois types de rôles : Administrateur système, Utilisateur GESEC, Adhérent
- Gestion des permissions selon les rôles

### Gestion des clients et sites
- Création et modification des fiches client (coordonnées, type local/national)
- Gestion des sites associés à chaque client
- Import des sites par fichier CSV

### Gestion des affaires avec BPU intégrés
- Modèle versionné : distinction `AffaireMaster` (code) / `AffaireVersion` (contenu de version)
- Création et modification d'affaires liées à un client (via `AffaireVersion`)
- Intégration directe des BPU dans les versions d'affaire (`AffaireBPU` attachés à `AffaireVersion`)
- Utilisation d'une affaire de référence comme catalogue de BPU standards
- Configuration des tarifs, fréquences et prix annuels
- Gestion du cycle de vie (Offre → Contrat) au niveau de `AffaireVersion`
- Duplication d'affaires et création de nouvelles versions, en conservant l'historique

### Affectation des sites
- Attribution des sites aux adhérents
- Configuration du type de prise en charge (Multitech, ELEC ou CVC)
- Option pour un second adhérent sous-traitant

### Saisie des quantités
- Interface de type tableur pour la saisie des quantités de BPU par site
- Support du copier/coller depuis Excel

## Contraintes techniques

- Application web développée avec Symfony
- Base de données PostgreSQL
- Interface utilisateur responsive et intuitive
- Performance adaptée à la gestion de plusieurs centaines d'affaires et milliers de lignes BPU intégrés
- Sécurité des accès basée sur les rôles

## Exclusions du périmètre

- Pas de génération automatique de documents PDF/Word
- Pas de facturation ou paiement en ligne intégré
- Pas de planification de maintenance ou interventions physiques

## Planning prévisionnel

Le projet est estimé à 1-2 mois de développement, réparti en quatre phases :

1. **Phase 1** (2 semaines) : Gestion des clients, sites, utilisateurs
2. **Phase 2** (2 semaines) : Gestion des affaires avec BPU intégrés, affectations
3. **Phase 3** (1 semaine) : Interface tableur et saisie des quantités
4. **Phase 4** (1 semaine) : Archivage, duplication d'affaires, validations

## Équipe projet

- 1 développeur 