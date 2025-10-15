# PRD: GESEC Gestion de Contrat

## 1. Product overview
### 1.1 Document title and version
   - PRD: GESEC Gestion de Contrat
   - Version: v1.0
   - 
### 1.2 Product summary
   - Le GESEC souhaite mettre en place un outil visant à simplifier la gestion des contrats de maintenance dans le secteur de l'électricité et du CVC, tout en conservant une solution « à taille humaine ». Cette application permettra à l'association et à ses adhérents de centraliser les données contractuelles, les prestations (BPU), et les répartitions de responsabilités entre les parties prenantes.

## 2. Goals
### 2.1 Business goals
   - Centraliser la gestion des contrats pour tous les clients du GESEC.
   - Permettre une vision globale des affaires pour les membres du GESEC.
   - Offrir un outil de travail simple mais puissant pour l'édition, le suivi et l'archivage des contrats.
### 2.2 User goals
   - Les adhérents peuvent visualiser et saisir leurs contrats et BPU.
   - Les gestionnaires GESEC peuvent assigner les contrats et sites aux bons adhérents.
   - L'admin peut gérer l'ensemble des données et utilisateurs.
### 2.3 Non-goals
   - Pas de génération automatique de documents PDF/Word.
   - Pas de facturation ou paiement en ligne intégré.
   - Pas de planification de maintenance ou interventions physiques.
## 3. User personas
### 3.1 Key user types
   - Administrateur système
   - Utilisateur GESEC
   - Adhérent
### 3.2 Basic persona details
   - **Admin**: profil technique, responsable de la plateforme.
   - **GESEC**: gestionnaire contractuel, modifie tout, interface avec les adhérents.
   - **Adhérent**: PME terrain, souhaite un accès clair et rapide aux données qui le concernent.
### 3.3 Role-based access
   - **Admin**: accès complet à toutes les fonctionnalités.
   - **GESEC**: gestion complète des contrats, clients, sites, BPU et affectations.
   - **Adhérent**: accès en lecture/écriture sur les sites et contrats dont il est responsable.
## 4. Functional requirements
   - **Gestion des utilisateurs** (Priority: High)
     - Création, modification et désactivation des comptes utilisateur.
     - Attribution des rôles et permissions.
   - **Gestion des clients** (Priority: High)
     - Création et modification des fiches client avec coordonnées complètes.
     - Distinction entre clients locaux et nationaux.
   - **Gestion des sites** (Priority: High)
     - Création et modification des sites liés à un client.
     - Import de sites via CSV.
   - **Gestion des affaires et BPU intégrés** (Priority: High)
     - Création et modification d'affaires liées à un client.
     - Modèle versionné : chaque `Affaire` possède une `AffaireMaster` (code) et une ou plusieurs `AffaireVersion`.
     - Les BPU, affectations et données opérationnelles sont stockées par `AffaireVersion` afin de garder l'historique.
     - `AffaireMaster.current_version_id` pointe vers la version active utilisée par les autres entités.
     - Utilisation d'une affaire de référence comme catalogue de BPU standards.
     - Configuration des tarifs, fréquences et prix annuels.
     - Gestion du cycle de vie (Offre → Contrat) gérée au niveau de la `AffaireVersion`.
   - **Affectations des sites** (Priority: High)
     - Attribution des sites aux adhérents.
     - Configuration du type de prise en charge (Multitech, ELEC, CVC).
     - Option pour un second adhérent sous-traitant.
   - **Saisie des quantités** (Priority: High)
     - Interface tableur pour la saisie des quantités de BPU par site.
     - Support du copier/coller depuis Excel.
   - **Duplication d'affaires** (Priority: Medium)
     - Possibilité de copier une affaire pour une nouvelle version.
     - Possibilité de créer un nouveau contrat à partir d'un existant.
## 5. User experience
### 5.1. Entry points & first-time user flow
   - L'Admin crée les comptes utilisateurs pour les différents rôles.
   - L'utilisateur GESEC configure les clients, sites et une affaire de référence avec les BPU standards.
   - L'Adhérent reçoit un compte préconfiguré avec accès aux affaires et sites dont il est responsable.
### 5.2. Core experience
   - **Navigation structurée**: Les utilisateurs naviguent par onglets : Clients > Sites > Affaires > Affectations > Saisie.
     - L'interface présente clairement le chemin de navigation et le contexte actuel.
   - **Fiches adaptées**: Chaque type d'utilisateur accède à des fiches claires et ciblées selon son rôle.
     - Les fiches sont simples et ne présentent que les informations pertinentes.
   - **Saisie facilitée**: Les utilisateurs bénéficient de tableurs intégrés avec support copier/coller.
     - L'expérience de saisie est similaire à celle d'Excel pour minimiser la courbe d'apprentissage.
### 5.3. Advanced features & edge cases
   - Double gestion Adhérent principal / sous-traitant pour les sites complexes.
   - Prise en charge partielle des sites (mode Elec ou CVC uniquement).
   - Gestion de multiples versions d'une affaire avec système d'archivage.
### 5.4. UI/UX highlights
   - Interface de type tableur pour la saisie rapide des BPU et quantités.
   - Fonctionnalités de tri et filtrage dans toutes les vues pour faciliter la recherche.
   - Support complet du copier/coller vers et depuis Excel pour une intégration fluide avec les outils existants.
## 6. Narrative
Un gestionnaire GESEC crée un client, ses sites, puis une affaire. Il crée des BPU directement dans l'affaire ou les importe depuis l'affaire de référence, configure les prix et fréquences, puis affecte des adhérents à chaque site. L'affaire passe du statut 'Offre' à 'Contrat' une fois validée. Les adhérents responsables peuvent alors saisir les quantités de BPU sur leurs sites respectifs.
## 7. Success metrics
### 7.1. User-centric metrics
   - Taux d'usage de la saisie par les adhérents.
   - Temps moyen pour créer une affaire complète.
   - Taux de remplissage automatique via copie ou import CSV.
### 7.2. Business metrics
   - Nombre d'affaires actives par an.
   - Nombre d'adhérents connectés par mois.
   - Temps économisé dans la gestion manuelle des contrats.
### 7.3. Technical metrics
   - Temps de réponse de l'interface.
   - Temps de chargement d'une affaire avec de nombreux BPU.
   - Intégrité des données après import ou copie.
## 8. Technical considerations
### 8.1. Integration points
   - Import de fichiers CSV pour les sites.
   - Aucun système externe (facturation, intervention) pour le moment.
### 8.2. Data storage & privacy
   - Stockage structuré en PostgreSQL (affaires avec BPU intégrés, sites, affectations…).
   - Gestion des accès par rôle et par affaire.
   - Données sensibles : aucun paiement ni mot de passe stocké en clair.
### 8.3. Scalability & performance
   - Conçu pour évoluer jusqu'à plusieurs centaines d'affaires et milliers de lignes BPU.
   - Utilisation de composants performants pour l'interface type tableur.
### 8.4. Potential challenges
   - Complexité des affectations multi-rôle et multi-activité.
   - Saisie rapide avec tableur JS et Excel (gestion des formats, validation).
   - Gestion des versions d'affaire proprement.
## 9. Milestones & sequencing
### 9.1. Project estimate
   - Medium: 1 à 2 mois selon les ressources (incluant les tests et ajustements).
### 9.2. Team size & composition
   - Small Team: 1 développeur
### 9.3. Suggested phases
   - **Phase 1**: Gestion des clients, sites, utilisateurs (2 semaines)
     - Key deliverables: Authentification, gestion des utilisateurs, CRUD clients et sites, import CSV.
   - **Phase 2**: Gestion des affaires avec BPU intégrés, affectations (2 semaines)
     - Key deliverables: CRUD affaires avec BPU intégrés, valorisation des BPU, affectation des adhérents.
   - **Phase 3**: Interface tableur et saisie des quantités (1 semaine)
     - Key deliverables: Interface type tableur, copier/coller Excel, saisie des quantités.
   - **Phase 4**: Archivage, duplication d'affaires, validations (1 semaine)
     - Key deliverables: Cycle de vie des affaires, archivage, duplication, validation des saisies.
## 10. User stories
### 10.1. Création de comptes utilisateur
   - **ID**: US-001
   - **Description**: En tant qu'Administrateur système, je veux créer, modifier et désactiver des comptes utilisateur, afin d'assurer le bon accès aux différentes fonctionnalités selon les rôles.
   - **Acceptance criteria**:
     - Je peux créer un utilisateur avec un rôle
     - Je peux modifier les informations d'un utilisateur
     - Je peux désactiver un utilisateur sans le supprimer
     - 
### 10.2. Réinitialisation des mots de passe
   - **ID**: US-002
   - **Description**: En tant qu'Administrateur système, je veux réinitialiser les mots de passe des utilisateurs, afin de les assister en cas de perte d'accès.
   - **Acceptance criteria**:
     - Un bouton permet de réinitialiser le mot de passe
     - L'utilisateur reçoit un lien de réinitialisation par email
     - 
### 10.3. Création de fiche client
   - **ID**: US-003
   - **Description**: En tant qu'utilisateur GESEC, je veux créer une fiche client avec ses coordonnées, afin de l'utiliser pour générer des affaires.
   - **Acceptance criteria**:
     - Je peux enregistrer un client avec nom, adresse, siren, contact, email
     - Je peux indiquer s'il est local ou national
     - 
### 10.4. Gestion des sites clients
   - **ID**: US-004
   - **Description**: En tant qu'utilisateur GESEC, je veux gérer les sites associés à un client, afin de les affecter aux affaires de maintenance.
   - **Acceptance criteria**:
     - Je peux créer/modifier un site avec code, description, adresse, contact référent
     - Chaque site est relié à un client
     - 
### 10.5. Import CSV de sites
   - **ID**: US-005
   - **Description**: En tant qu'utilisateur GESEC, je veux importer les sites depuis un fichier CSV, afin de gagner du temps sur la saisie.
   - **Acceptance criteria**:
     - Je peux importer un fichier CSV
     - Le système crée ou met à jour les sites du client correspondant
     - 
### 10.6. Gestion des BPU dans l'affaire de référence
   - **ID**: US-006
   - **Description**: En tant qu'utilisateur GESEC, je veux gérer les BPU dans une affaire de référence, afin de les réutiliser dans les différentes affaires.
   - **Acceptance criteria**:
     - Je peux ajouter/modifier/supprimer un BPU dans l'affaire de référence
     - Chaque BPU peut être classé par type et catégorie
     - Je peux importer des BPU depuis l'affaire de référence vers d'autres affaires
     - 
### 10.7. Création d'affaire
   - **ID**: US-007
   - **Description**: En tant qu'utilisateur GESEC, je veux créer une affaire avec un client, une période, des BPU et des adhérents, afin de structurer le contrat.
   - **Acceptance criteria**:
     - Je peux créer une affaire avec code, description, dates, client
     - Je peux ajouter des lignes BPU directement dans l'affaire avec tarifs, fréquences et descriptions
     - Je peux lier des adhérents à l'affaire
     - 
### 10.8. Changement de statut d'affaire
   - **ID**: US-008
   - **Description**: En tant qu'utilisateur GESEC, je veux changer le statut d'une affaire de "Offre" à "Contrat", afin de la rendre active.
   - **Acceptance criteria**:
     - Je peux modifier le statut
     - Le système archive les anciennes versions si besoin
     - 
### 10.9. Duplication d'affaire
   - **ID**: US-009
   - **Description**: En tant qu'utilisateur GESEC, je veux dupliquer une affaire existante, afin de gagner du temps pour une nouvelle version ou un nouveau contrat.
   - **Acceptance criteria**:
     - Je peux cloner l'affaire avec ses BPU intégrés
     - Je peux choisir de cloner vers une nouvelle version ou une nouvelle affaire
     - 
### 10.10. Affectation des sites aux adhérents
   - **ID**: US-010
   - **Description**: En tant qu'utilisateur GESEC, je veux définir quels adhérents prennent en charge chaque site d'une affaire, afin d'organiser la répartition des interventions.
   - **Acceptance criteria**:
     - Je peux assigner un ou deux adhérents par site
     - Je peux définir la prise en charge : Multitech, ELEC ou CVC
     - 
### 10.11. Visualisation des affaires assignées
   - **ID**: US-011
   - **Description**: En tant qu'adhérent, je veux voir la liste des affaires sur lesquelles je suis enrôlé, afin de connaître mes responsabilités.
   - **Acceptance criteria**:
     - Je peux voir les affaires où je suis assigné
     - Je peux filtrer par statut ou date
     - 
### 10.12. Saisie des quantités BPU
   - **ID**: US-012
   - **Description**: En tant qu'adhérent, je veux saisir les quantités de BPU pour mes sites assignés, afin de compléter les contrats d'application.
   - **Acceptance criteria**:
     - Je peux entrer une quantité par BPU et par site
     - Le montant est automatiquement calculé
     - 
### 10.13. Copier-coller depuis Excel
   - **ID**: US-013
   - **Description**: En tant qu'adhérent, je veux pouvoir copier-coller des blocs de cellules dans un tableur intégré, afin de faciliter la saisie de données depuis Excel.
   - **Acceptance criteria**:
     - Je peux coller plusieurs cellules
     - Le système reconnaît les colonnes automatiquement
     - 