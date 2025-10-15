/**
 * Number utilities
 * 
 * Functions for formatting and parsing numbers in various locales
 */

/**
 * Format options for number display
 */
export interface NumberFormatOptions {
    /**
     * Number of decimal places
     * @default 2
     */
    decimals?: number;
    
    /**
     * Whether to use grouping separators (thousands)
     * @default true
     */
    useGrouping?: boolean;
    
    /**
     * Locale for formatting
     * @default 'fr-FR'
     */
    locale?: string;
}

/**
 * Parses a string into a number, handling various number formats
 * 
 * @param value - String value or any value to parse
 * @param defaultValue - Value to return if parsing fails
 * @returns The parsed number or defaultValue if parsing fails
 */
export function parseLocalizedNumber(value: unknown, defaultValue: number = 0): number {
    // Handle null or undefined
    if (value === null || value === undefined) {
        return defaultValue;
    }
    
    // Handle numbers
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : defaultValue;
    }
    
    // Convert to string if not already
    const str = String(value).trim();
    if (str === '') {
        return defaultValue;
    }
    
    // Try to parse as standard number first
    const normalizedStr = str
        // Replace non-breaking spaces with normal spaces
        .replace(/\u00A0/g, ' ')
        // Replace various decimal points
        .replace(/[.,،٫]/g, '.')
        // Remove all spaces
        .replace(/\s/g, '')
        // Replace multiple decimal points (keep only the first)
        .replace(/(\..*?)\.+/g, '$1');
    
    const parsed = Number(normalizedStr);
    
    // Return the parsed value if valid, otherwise default
    return Number.isFinite(parsed) ? parsed : defaultValue;
}

/**
 * Formats a number according to the given locale and options
 * 
 * @param value - Number to format
 * @param options - Formatting options
 * @returns Formatted number string
 */
export function formatNumber(value: number, options: NumberFormatOptions = {}): string {
    const {
        decimals = 2,
        useGrouping = true,
        locale = 'fr-FR'
    } = options;
    
    try {
        return new Intl.NumberFormat(locale, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
            useGrouping
        }).format(value);
    } catch (error) {
        console.warn('Error formatting number:', error);
        return value.toFixed(decimals);
    }
}

/**
 * Formats a number as currency
 * 
 * @param value - Number to format
 * @param currency - Currency code (ISO 4217)
 * @param locale - Locale to use for formatting
 * @returns Formatted currency string
 */
export function formatCurrency(value: number, currency: string = 'EUR', locale: string = 'fr-FR'): string {
    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency
        }).format(value);
    } catch (error) {
        console.warn('Error formatting currency:', error);
        return `${formatNumber(value)} ${currency}`;
    }
}

/**
 * Rounds a number to the specified number of decimal places
 * 
 * @param value - Number to round
 * @param decimals - Number of decimal places
 * @returns Rounded number
 */
export function round(value: number, decimals: number = 2): number {
    const factor = Math.pow(10, decimals);
    return Math.round((value + Number.EPSILON) * factor) / factor;
}

/**
 * Clamps a number between min and max values
 * 
 * @param value - Number to clamp
 * @param min - Minimum value
 * @param max - Maximum value
 * @returns Clamped number
 */
export function clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
}

/**
 * Checks if a value is a finite number
 * 
 * @param value - Value to check
 * @returns True if the value is a finite number
 */
export function isNumber(value: unknown): boolean {
    return typeof value === 'number' && Number.isFinite(value);
}
