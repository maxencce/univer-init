# User Stories - GESEC Gestion de Contrat

Ce document recense l'ensemble des user stories du projet GESEC Gestion de Contrat, organisées par rôle et priorité.

## Administrateur système

### Priorité Haute

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-001 DONE | En tant qu'Administrateur système, je veux créer, modifier et désactiver des comptes utilisateur, afin d'assurer le bon accès aux différentes fonctionnalités selon les rôles. | <ul><li>Je peux créer un utilisateur avec un rôle</li><li>Je peux modifier les informations d'un utilisateur</li><li>Je peux désactiver un utilisateur sans le supprimer</li></ul> |

### Priorité Moyenne

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-002 TODO | En tant qu'Administrateur système, je veux réinitialiser les mots de passe des utilisateurs, afin de les assister en cas de perte d'accès. | <ul><li>Un bouton permet de réinitialiser le mot de passe</li><li>L'utilisateur reçoit un lien de réinitialisation par email</li></ul> |

## Utilisateur GESEC

### Priorité Haute

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-003 | En tant qu'utilisateur GESEC, je veux créer une fiche client avec ses coordonnées, afin de l'utiliser pour générer des affaires. | <ul><li>Je peux enregistrer un client avec nom, adresse, siren, contact, email</li><li>Je peux indiquer s'il est local ou national</li></ul> |
| US-004 | En tant qu'utilisateur GESEC, je veux gérer les sites associés à un client, afin de les affecter aux affaires de maintenance. | <ul><li>Je peux créer/modifier un site avec code, description, adresse, contact référent</li><li>Chaque site est relié à un client</li></ul> |
| US-006 | En tant qu'utilisateur GESEC, je veux gérer les BPU dans une affaire de référence, afin de les réutiliser dans les différentes affaires. | <ul><li>Je peux ajouter/modifier/supprimer un BPU dans l'affaire de référence</li><li>Chaque BPU peut être classé par type et catégorie</li><li>Je peux importer des BPU depuis l'affaire de référence vers d'autres affaires</li></ul> |
| US-007 | En tant qu'utilisateur GESEC, je veux créer une affaire avec un client, une période, des BPU intégrés et des adhérents, afin de structurer le contrat. | <ul><li>Je peux créer une affaire avec code, description, dates, client</li><li>Je peux ajouter des BPU directement dans l'affaire avec tarifs, fréquences et descriptions</li><li>Je peux lier des adhérents à l'affaire</li></ul> |
| US-010 | En tant qu'utilisateur GESEC, je veux définir quels adhérents prennent en charge chaque site d'une affaire, afin d'organiser la répartition des interventions. | <ul><li>Je peux assigner un ou deux adhérents par site</li><li>Je peux définir la prise en charge : Multitech, ELEC ou CVC</li></ul> |

### Priorité Moyenne

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-005 | En tant qu'utilisateur GESEC, je veux importer les sites depuis un fichier CSV, afin de gagner du temps sur la saisie. | <ul><li>Je peux importer un fichier CSV</li><li>Le système crée ou met à jour les sites du client correspondant</li></ul> |
| US-008 | En tant qu'utilisateur GESEC, je veux changer le statut d'une affaire de "Offre" à "Contrat", afin de la rendre active. | <ul><li>Je peux modifier le statut</li><li>Le système archive les anciennes versions si besoin</li></ul> |

### Priorité Basse

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-009 | En tant qu'utilisateur GESEC, je veux dupliquer une affaire existante, afin de gagner du temps pour une nouvelle version ou un nouveau contrat. | <ul><li>Je peux cloner l'affaire avec ses BPU intégrés</li><li>Je peux choisir de cloner vers une nouvelle version ou une nouvelle affaire</li></ul> |

## Adhérent

### Priorité Haute

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-011 | En tant qu'adhérent, je veux voir la liste des affaires sur lesquelles je suis enrôlé, afin de connaître mes responsabilités. | <ul><li>Je peux voir les affaires où je suis assigné</li><li>Je peux filtrer par statut ou date</li></ul> |
| US-012 | En tant qu'adhérent, je veux saisir les quantités de BPU pour mes sites assignés, afin de compléter les contrats d'application. | <ul><li>Je peux entrer une quantité par BPU et par site</li><li>Le montant est automatiquement calculé</li></ul> |

### Priorité Moyenne

| ID | Description | Critères d'acceptation |
|----|-------------|------------------------|
| US-013 | En tant qu'adhérent, je veux pouvoir copier-coller des blocs de cellules dans un tableur intégré, afin de faciliter la saisie de données depuis Excel. | <ul><li>Je peux coller plusieurs cellules</li><li>Le système reconnaît les colonnes automatiquement</li></ul> |

## Statut des User Stories

| ID | Statut | Date de mise à jour |
|----|--------|---------------------|
| US-001 | Fait | - |
| US-002 | Fait | - |
| US-003 | Fait | - |
| US-004 | Fait | - |
| US-005 | À faire | - |
| US-006 | À faire | - |
| US-007 | À faire | - |
| US-008 | À faire | - |
| US-009 | À faire | - |
| US-010 | À faire | - |
| US-011 | À faire | - |
| US-012 | À faire | - |
| US-013 | À faire | - |

## Synthèse

- Total des user stories : 13
- User stories priorité Haute : 8
- User stories priorité Moyenne : 4
- User stories priorité Basse : 1
- User stories par rôle :
  - Administrateur système : 2
  - Utilisateur GESEC : 8
  - Adhérent : 3 