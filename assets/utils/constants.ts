/**
 * Application constants
 * 
 * Defines constant values used across the application
 */

/**
 * Column types for data tables
 */
export enum COLUMN_TYPES {
    TEXT = 'text',
    NUMBER = 'number',
    DATE = 'date',
    DATETIME = 'datetime',
    BOOLEAN = 'boolean',
    SELECT = 'select',
    CHECKBOX = 'checkbox',
    CURRENCY = 'currency',
    PERCENTAGE = 'percentage',
    EMAIL = 'email',
    PHONE = 'phone',
    URL = 'url',
    IMAGE = 'image',
    FILE = 'file',
    CUSTOM = 'custom'
}

/**
 * Status options for projects/affaires
 */
export enum AFFAIRE_STATUSES {
    DRAFT = 'draft',
    IN_PROGRESS = 'in_progress',
    COMPLETED = 'completed',
    CANCELLED = 'cancelled'
}

/**
 * Default pagination options
 */
export const DEFAULT_PAGINATION = {
    PER_PAGE: 15,
    CURRENT_PAGE: 1
};

/**
 * Default date format
 */
export const DEFAULT_DATE_FORMAT = 'DD/MM/YYYY';

/**
 * Default datetime format
 */
export const DEFAULT_DATETIME_FORMAT = 'DD/MM/YYYY HH:mm';

/**
 * Routes constants
 */
export const ROUTES = {
    DASHBOARD: '/dashboard',
    AFFAIRES: {
        INDEX: '/affaires',
        SHOW: (id: number | string) => `/affaires/${id}`,
        CREATE: '/affaires/new',
        EDIT: (id: number | string) => `/affaires/${id}/edit`,
        BPU: (id: number | string) => `/affaires/${id}/bpu`
    },
    CLIENTS: {
        INDEX: '/clients',
        SHOW: (id: number | string) => `/clients/${id}`,
        CREATE: '/clients/new',
        EDIT: (id: number | string) => `/clients/${id}/edit`
    },
    USERS: {
        INDEX: '/users',
        SHOW: (id: number | string) => `/users/${id}`,
        CREATE: '/users/new',
        EDIT: (id: number | string) => `/users/${id}/edit`
    }
};

/**
 * API endpoints
 */
export const API_ENDPOINTS = {
    AFFAIRES: '/api/affaires',
    CLIENTS: '/api/clients',
    USERS: '/api/users',
    BPU: (affaireId: number | string) => `/api/affaires/${affaireId}/bpus`
};

/**
 * Default animation durations in milliseconds
 */
export const ANIMATION_DURATION = {
    FAST: 150,
    NORMAL: 300,
    SLOW: 500
};

/**
 * Locales supported by the application
 */
export const SUPPORTED_LOCALES = ['fr-FR', 'en-US'];

/**
 * Default locale
 */
export const DEFAULT_LOCALE = 'fr-FR';

/**
 * Maximum file upload size in bytes (10 MB)
 */
export const MAX_UPLOAD_SIZE = 10 * 1024 * 1024;

/**
 * Allowed MIME types for file uploads
 */
export const ALLOWED_FILE_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
