import { ui } from './ui';

export const defaultLang = 'es';
export const showLang = false;
export const languages = {
  es: 'Español',
  en: 'English',
  ca: 'Català',
  fr: 'Français',
  it: 'Italiano',
  de: 'Deutsch',
};

export const locales = Object.keys(languages);

export function getLangFromUrl(url) {
  const [, lang] = url.pathname.split('/');
  if (lang in ui) return lang;
  return defaultLang;
}

export function useTranslations(lang) {
  return function t(key) {
    return ui[lang]?.[key] || ui[defaultLang]?.[key] || key;
  }
}

// Funciones de gestión de cookies para currentLang
export function detectBrowserLanguage() {
  if (typeof window === 'undefined') return 'en';
  
  const browserLang = navigator.language.split('-')[0];
  return locales.includes(browserLang) ? browserLang : 'en';
}

export function getCurrentLangFromCookie() {
  if (typeof window === 'undefined') return 'en';
  
  const cookies = document.cookie.split(';');
  const currentLangCookie = cookies.find(cookie => cookie.trim().startsWith('currentLang='));
  
  if (currentLangCookie) {
    return currentLangCookie.split('=')[1];
  }
  
  return null;
}

export function setCurrentLangCookie(lang) {
  if (typeof window === 'undefined') return;
  
  // Verificar que el idioma esté en la lista de idiomas soportados
  const validLang = locales.includes(lang) ? lang : 'en';
  
  // Establecer cookie que expire en 1 año
  const expirationDate = new Date();
  expirationDate.setFullYear(expirationDate.getFullYear() + 1);
  
  // Dentro de setCurrentLangCookie(lang)
  document.cookie = `currentLang=${validLang}; expires=${expirationDate.toUTCString()}; path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
}

export function initializeLanguage() {
  if (typeof window === 'undefined') return 'en';
  
  let currentLang = getCurrentLangFromCookie();
  
  if (!currentLang) {
    // Primera visita - detectar idioma del navegador
    currentLang = detectBrowserLanguage();
    setCurrentLangCookie(currentLang);
  }
  
  return currentLang;
}