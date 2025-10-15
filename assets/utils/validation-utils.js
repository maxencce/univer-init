/**
 * Utilitaires de validation
 */

/**
 * Vérifie si une valeur est vide (null, undefined, chaîne vide, tableau vide)
 * @param {*} value - La valeur à vérifier
 * @returns {Boolean} - True si la valeur est vide
 */
export function isEmpty(value) {
  if (value === null || value === undefined) return true;
  if (typeof value === 'string') return value.trim() === '';
  if (Array.isArray(value)) return value.length === 0;
  if (typeof value === 'object') return Object.keys(value).length === 0;
  return false;
}

/**
 * Vérifie si une valeur est un nombre valide
 * @param {*} value - La valeur à vérifier
 * @returns {Boolean} - True si la valeur est un nombre valide
 */
export function isValidNumber(value) {
  if (typeof value === 'number') return !isNaN(value) && isFinite(value);
  if (typeof value === 'string') {
    const n = Number(value.trim().replace(',', '.'));
    return !isNaN(n) && isFinite(n);
  }
  return false;
}

/**
 * Vérifie si une valeur est un entier positif
 * @param {*} value - La valeur à vérifier
 * @returns {Boolean} - True si la valeur est un entier positif
 */
export function isPositiveInteger(value) {
  if (typeof value === 'number') return Number.isInteger(value) && value >= 0;
  if (typeof value === 'string') {
    const n = parseInt(value.trim(), 10);
    return !isNaN(n) && n >= 0 && n.toString() === value.trim();
  }
  return false;
}

/**
 * Vérifie si une valeur est un email valide
 * @param {String} value - L'email à vérifier
 * @returns {Boolean} - True si l'email est valide
 */
export function isValidEmail(value) {
  if (typeof value !== 'string') return false;
  // Expression régulière simple pour vérifier les emails
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(value);
}

/**
 * Normalise une valeur pour qu'elle soit dans une plage spécifiée
 * @param {Number} value - La valeur à normaliser
 * @param {Number} min - La valeur minimale
 * @param {Number} max - La valeur maximale
 * @returns {Number} - La valeur normalisée
 */
export function clamp(value, min, max) {
  if (isNaN(value)) return min;
  return Math.min(Math.max(value, min), max);
}
