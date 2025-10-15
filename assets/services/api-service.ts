/**
 * API Service
 * 
 * Provides utilities for making HTTP requests to the server with proper typing
 * and handling of CSRF tokens.
 */

/**
 * Options for fetch requests
 */
interface FetchOptions extends RequestInit {
    headers?: Record<string, string>;
}

/**
 * Generic error thrown by the API service
 */
export class ApiError extends Error {
    status: number;
    
    constructor(message: string, status: number) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
    }
}

/**
 * Fetches data from the API with proper error handling
 * 
 * @param url - The URL to fetch data from
 * @param options - Optional fetch configuration
 * @returns Promise with the parsed response data
 */
export async function fetchData<T = any>(url: string, options: FetchOptions = {}): Promise<T> {
    try {
        const defaultOptions: FetchOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            credentials: 'same-origin',
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new ApiError(`HTTP error ${response.status}: ${response.statusText}`, response.status);
        }
        
        // Check if response is empty
        const text = await response.text();
        if (!text) return {} as T;
        
        // Parse JSON response
        return JSON.parse(text) as T;
    } catch (error) {
        if (error instanceof ApiError) {
            throw error;
        }
        throw new Error(`Failed to fetch data: ${error instanceof Error ? error.message : String(error)}`);
    }
}

/**
 * Posts data to the API with proper error handling
 * 
 * @param url - The URL to post data to
 * @param data - The data to send
 * @param csrfToken - Optional CSRF token for protection
 * @param options - Optional fetch configuration
 * @returns Promise with the parsed response data
 */
export async function postData<T = any, D = unknown>(
    url: string, 
    data: D, 
    csrfToken?: string, 
    options: FetchOptions = {}
): Promise<T> {
    try {
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        };
        
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        
        const defaultOptions: FetchOptions = {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify(data),
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new ApiError(`HTTP error ${response.status}: ${response.statusText}`, response.status);
        }
        
        // Check if response is empty
        const text = await response.text();
        if (!text) return {} as T;
        
        // Parse JSON response
        return JSON.parse(text) as T;
    } catch (error) {
        if (error instanceof ApiError) {
            throw error;
        }
        throw new Error(`Failed to post data: ${error instanceof Error ? error.message : String(error)}`);
    }
}

/**
 * Gets a CSRF token from a meta tag or input element
 * 
 * @param inputId - Optional ID of an input element containing the CSRF token
 * @returns The CSRF token or null if not found
 */
export function getCsrfToken(inputId?: string): string | null {
    // Try to get token from input if ID provided
    if (inputId) {
        const input = document.getElementById(inputId) as HTMLInputElement | null;
        if (input && input.value) {
            return input.value;
        }
    }

    // Try to get token from meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken && metaToken.getAttribute('content')) {
        return metaToken.getAttribute('content');
    }

    return null;
}

/**
 * API service for handling requests
 */
export const apiService = {
    fetchData,
    postData,
    getCsrfToken
};

export default apiService;
