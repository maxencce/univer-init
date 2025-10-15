/**
 * Common type definitions used across the application
 */

/**
 * Basic model with ID and timestamps
 */
export interface BaseModel {
    id: number;
    createdAt?: string;
    updatedAt?: string;
}

/**
 * User model
 */
export interface User extends BaseModel {
    email: string;
    firstName: string;
    lastName: string;
    roles: string[];
}

/**
 * Affaire (Project) model
 */
export interface Affaire extends BaseModel {
    reference: string;
    nom: string;
    description?: string;
    statut: string;
    dateDebut?: string;
    dateFin?: string;
    client?: Client;
    clientId?: number;
}

/**
 * Client model
 */
export interface Client extends BaseModel {
    nom: string;
    adresse?: string;
    telephone?: string;
    email?: string;
    reference?: string;
}

/**
 * BPU (Bordereau des Prix Unitaires) model
 */
export interface Bpu extends BaseModel {
    type: string;
    categorie: string;
    description: string;
    puissance: string;
    tempsMaintenanceAnnuelle: number | null;
    nbHeuresVisiteTechnique: number | null;
    nbHeuresVisiteBonFonctionnement: number | null;
    totalHeuresEquipement: number | null;
    prixMo: number | null;
    fraisKilometriqueUnitaire: number | null;
    nbKmMoyen: number | null;
    totalFraisKilometrique: number | null;
    diversConsommable: number | null;
    prixEquipement1VisiteTechnique: number | null;
    prixTotalEquipement: number | null;
    affaireId: number;
}

/**
 * Pagination metadata
 */
export interface PaginationMeta {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
}

/**
 * Paginated response
 */
export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

/**
 * API response structure
 */
export interface ApiResponse<T> {
    success: boolean;
    data?: T;
    message?: string;
    errors?: Record<string, string[]>;
}

/**
 * Select option for dropdowns
 */
export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

/**
 * Sort direction
 */
export enum SortDirection {
    ASC = 'asc',
    DESC = 'desc'
}

/**
 * Sort options
 */
export interface SortOptions {
    column: string;
    direction: SortDirection;
}

/**
 * Filter options
 */
export interface FilterOptions {
    [key: string]: string | number | boolean | null | undefined;
}

/**
 * Column definition for data tables
 */
export interface ColumnDefinition {
    key: string;
    label: string;
    sortable?: boolean;
    filterable?: boolean;
    type?: 'text' | 'number' | 'date' | 'boolean' | 'currency';
    formatter?: (value: any, row: any) => string;
    width?: string | number;
}
