# Structure de la base de données

## Introduction

Ce document décrit la structure de la base de données PostgreSQL utilisée pour l'application GESEC Gestion de Contrat. Il présente le Modèle Logique de Données (MLD) avec les tables, leurs champs et les relations entre elles.

## Entités principales

L'application s'articule autour des entités suivantes :
- Utilisateurs (avec différents rôles : Admin, GESEC, Adhérent)
- Clients
- Sites
- Affaires (incluant les BPU directement intégrés à l'affaire)
- Affectations (lien entre sites, adhérents et affaires)
- Quantités BPU (par site)

## Modèle Logique de Données (MLD)

```
Enum user_role {
  ADMIN
  GESEC
  ADHERENT
}

Table Utilisateur {
  user_id integer [pk, increment]
  nom varchar [not null]
  email varchar [not null, unique]
  mot_de_passe varchar [not null]
  role user_role [not null] // Administrateur, Gesec, Adherent
  created_at datetime
  updated_at datetime
}

// En réalité relation utilisateur adhérent = 1:1
// Grâce à la contrainte unique sur utilisateur_id

Table Adherent {
  adherent_id integer [pk, increment]
  nom varchar [not null]
  utilisateur_id integer [not null, unique, ref: > Utilisateur.user_id]
  created_at datetime
  updated_at datetime
}

Enum client_type {
  NATIONAL
  LOCAL
}

Table Client {
  client_id integer [pk, increment]
  nom varchar [not null]
  type client_type [not null] // national, local
  adresse text [not null]
  siren varchar [not null]
  contact_nom varchar [not null]
  contact_email varchar [not null]
  created_at datetime
  updated_at datetime
}

Enum site_statut {
  ACTIF
  INACTIF
  FERME
}

Table Site {
  site_id integer [pk, increment]
  code varchar [not null, unique]
  description text
  adresse text
  code_postal varchar
  contact_nom varchar
  contact_email varchar
  client_id integer [not null, ref: > Client.client_id]
  statut site_statut // actif, inactif, fermé
  created_at datetime
  updated_at datetime
}

Enum bpu_categorie {
  "Plomberie/Sanitaires"
  "Chauffage"
  "Climatisation"
  "Ventilation"
  "Courants forts"
  "Courants faibles"
  "GTB/GTC"
  "Désenfumage/Sécurité incendie"
}

Enum bpu_type {
  ELEC
  CVC
}

Enum affaire_statut {
  OFFRE
  CONTRAT
}

Table Affaire {
  affaire_id integer [pk, increment]
  code varchar [not null, unique]
  description text
  client_id integer [not null, ref: > Client.client_id]
  statut affaire_statut [not null, default: "OFFRE"] // Offre, Contrat
  date_debut date
  date_fin date
  version integer [not null]
  version_active boolean [not null]
  archive boolean [not null]
  created_at datetime
  updated_at datetime
}

Table Affaire_BPU {
  affaire_bpu_id integer [pk, increment]
  affaire_id integer [not null, ref: > Affaire.affaire_id]

  bpu_code varchar [not null]
  description_personnalisee text
  type bpu_type [not null]
  categorie bpu_categorie [not null]

  prix_maintenance_annuelle decimal(10,2)
  frequence_visite integer
  prix_visite_intermediaire decimal(10,2)
  prix_annuel decimal(10,2)

  created_at datetime
  updated_at datetime

  Indexes {
    (affaire_id, categorie, type)
  }
}

Table Affaire_Adherent {
  affaire_id integer [not null, ref: > Affaire.affaire_id]
  adherent_id integer [not null, ref: > Adherent.adherent_id]
  created_at datetime
  updated_at datetime

  Indexes {
    (affaire_id, adherent_id) [pk]
  }
}

Enum activite {
  MULTITECH
  ELEC
  CVC
}

// Si activite = MULTITECH : affaire, site unique
// Sinon : 2 affaire, site
Table Affaire_Site_Adherent {
  affaire_id integer [not null, ref: > Affaire.affaire_id]
  site_id integer [not null, ref: > Site.site_id]
  adherent_id integer [not null, ref: > Adherent.adherent_id]
  activite activite [default: 'MULTITECH']
  sous_traitant_id integer [ref: > Adherent.adherent_id] // optionnel
  created_at datetime
  updated_at datetime

  Indexes {
    (affaire_id, site_id, adherent_id) [pk]
  }
}

Table Contrat_Application {
  affaire_id integer [not null, ref: > Affaire.affaire_id]
  site_id integer [not null, ref: > Site.site_id]
  affaire_bpu_id integer [not null, ref: > Affaire_BPU.affaire_bpu_id]
  quantite integer [not null]
  prix_maintenance_site decimal(10,2)
  created_at datetime
  updated_at datetime

  Indexes {
    (affaire_id, site_id, affaire_bpu_id) [pk]
  }
}
```

### Entité Utilisateur
- Stockage des utilisateurs du système avec leurs rôles et permissions

### Entité Adherent
- Informations relatives aux adhérents avec une relation 1:1 avec Utilisateur
- Grâce à la contrainte unique sur utilisateur_id

### Entité Client
- Informations relatives aux clients du GESEC
- Distinction entre clients locaux et nationaux

### Entité Site
- Sites rattachés aux clients
- Informations de localisation et de contact

### Entité Affaire
- Contrats ou offres liés à un client
- Une affaire de référence peut contenir tous les BPU standards

### Entité Affaire_BPU
- Remplace l'ancien catalogue BPU indépendant
- Intègre directement les informations des BPU (code, type, catégorie)
- Permet la valorisation des prestations (prix, fréquence)
- Une affaire de référence sert de catalogue pour les autres affaires

### Entité Affectation
- Relation entre les sites, les affaires et les adhérents responsables
- Configuration du type de prise en charge (Multitech, ELEC, CVC)

### Entité Contrat_Application
- Quantités de chaque BPU appliquées à chaque site dans une affaire
- Référence maintenant affaire_bpu_id au lieu de bpu_code

## Contraintes et règles métier

- Un site est rattaché à un seul client
- Une affaire concerne un seul client mais peut inclure plusieurs sites
- Un site peut être affecté à un ou deux adhérents maximum (principal et sous-traitant)
- Les adhérents n'ont accès qu'aux affaires et sites auxquels ils sont affectés
- Une affaire peut passer du statut "Offre" à "Contrat"
- Une affaire spécifique sert de catalogue de référence pour les BPU standards

## Évolutions prévues

Cette structure pourra évoluer en fonction des besoins futurs de l'application, notamment pour :
- Ajouter des métadonnées supplémentaires aux entités
- Champs libres dans une table
- Étendre le modèle pour gérer des fonctionnalités additionnelles
- Optimiser les performances pour les requêtes fréquentes 