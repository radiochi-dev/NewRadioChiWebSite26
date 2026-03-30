import calendarEventsData from './data/calendarevents.json'

import esHome from './i18n/es/home.json'
import enHome from './i18n/en/home.json'
import caHome from './i18n/ca/home.json'
import frHome from './i18n/fr/home.json'
import itHome from './i18n/it/home.json'
import deHome from './i18n/de/home.json'

import esAbout from './i18n/es/about.json'
import enAbout from './i18n/en/about.json'
import caAbout from './i18n/ca/about.json'
import frAbout from './i18n/fr/about.json'
import itAbout from './i18n/it/about.json'
import deAbout from './i18n/de/about.json'

import esMusic from './i18n/es/music.json'
import enMusic from './i18n/en/music.json'
import caMusic from './i18n/ca/music.json'
import frMusic from './i18n/fr/music.json'
import itMusic from './i18n/it/music.json'
import deMusic from './i18n/de/music.json'

import esHeader from './i18n/es/header.json'
import enHeader from './i18n/en/header.json'
import caHeader from './i18n/ca/header.json'
import frHeader from './i18n/fr/header.json'
import itHeader from './i18n/it/header.json'
import deHeader from './i18n/de/header.json'

import esCalendar from './i18n/es/calendarEvents.json'
import enCalendar from './i18n/en/calendarEvents.json'
import caCalendar from './i18n/ca/calendarEvents.json'
import frCalendar from './i18n/fr/calendarEvents.json'
import itCalendar from './i18n/it/calendarEvents.json'
import deCalendar from './i18n/de/calendarEvents.json'

import esContact from './i18n/es/contact.json'
import enContact from './i18n/en/contact.json'
import caContact from './i18n/ca/contact.json'
import frContact from './i18n/fr/contact.json'
import itContact from './i18n/it/contact.json'
import deContact from './i18n/de/contact.json'

import esMedia from './i18n/es/media.json'
import enMedia from './i18n/en/media.json'
import caMedia from './i18n/ca/media.json'
import frMedia from './i18n/fr/media.json'
import itMedia from './i18n/it/media.json'
import deMedia from './i18n/de/media.json'

import esIntro from './i18n/es/introwebsite.json'
import enIntro from './i18n/en/introwebsite.json'
import caIntro from './i18n/ca/introwebsite.json'
import frIntro from './i18n/fr/introwebsite.json'
import itIntro from './i18n/it/introwebsite.json'
import deIntro from './i18n/de/introwebsite.json'

import esFooter from './i18n/es/footer.json'
import enFooter from './i18n/en/footer.json'
import caFooter from './i18n/ca/footer.json'
import frFooter from './i18n/fr/footer.json'
import itFooter from './i18n/it/footer.json'
import deFooter from './i18n/de/footer.json'

import esTermsPolicyCookies from './i18n/es/terms-policy-cookies.json'
import enTermsPolicyCookies from './i18n/en/terms-policy-cookies.json'
import caTermsPolicyCookies from './i18n/ca/terms-policy-cookies.json'
import frTermsPolicyCookies from './i18n/fr/terms-policy-cookies.json'
import itTermsPolicyCookies from './i18n/it/terms-policy-cookies.json'
import deTermsPolicyCookies from './i18n/de/terms-policy-cookies.json'

const byLocale = {
    es: { home: esHome, about: esAbout, music: esMusic, header: esHeader, calendar: esCalendar, contact: esContact, media: esMedia, intro: esIntro, footer: esFooter, termsPolicyCookies: esTermsPolicyCookies },
    en: { home: enHome, about: enAbout, music: enMusic, header: enHeader, calendar: enCalendar, contact: enContact, media: enMedia, intro: enIntro, footer: enFooter, termsPolicyCookies: enTermsPolicyCookies },
    ca: { home: caHome, about: caAbout, music: caMusic, header: caHeader, calendar: caCalendar, contact: caContact, media: caMedia, intro: caIntro, footer: caFooter, termsPolicyCookies: caTermsPolicyCookies },
    fr: { home: frHome, about: frAbout, music: frMusic, header: frHeader, calendar: frCalendar, contact: frContact, media: frMedia, intro: frIntro, footer: frFooter, termsPolicyCookies: frTermsPolicyCookies },
    it: { home: itHome, about: itAbout, music: itMusic, header: itHeader, calendar: itCalendar, contact: itContact, media: itMedia, intro: itIntro, footer: itFooter, termsPolicyCookies: itTermsPolicyCookies },
    de: { home: deHome, about: deAbout, music: deMusic, header: deHeader, calendar: deCalendar, contact: deContact, media: deMedia, intro: deIntro, footer: deFooter, termsPolicyCookies: deTermsPolicyCookies },
}

export function getLegacyContent(locale) {
    return byLocale[locale] ?? byLocale.es
}

export function getLegacyCalendarData(locale) {
    return {
        events: calendarEventsData.events ?? [],
        translations: calendarEventsData.translations?.[locale] ?? calendarEventsData.translations?.es ?? {},
    }
}

export function getLegacyMediaData() {
    return {
        photos: [
            { src: '/assets/img/media/Abraxas2.webp', alt: 'Abraxas', caption: 'Abraxas Club', date: '2023-10-15' },
            { src: '/assets/img/media/Decadance1.webp', alt: 'Decadance', caption: 'Decadance Party', date: '2023-09-20' },
            { src: '/assets/img/media/Decadance2_optimized.webp', alt: 'Decadance 2', caption: 'Decadance Night', date: '2023-08-30' },
            { src: '/assets/img/media/Decadance3.webp', alt: 'Decadance 3', caption: 'Decadance Festival', date: '2023-07-15' },
            { src: '/assets/img/media/PrideGranCanarias1.webp', alt: 'Pride Gran Canarias', caption: 'Pride Gran Canarias', date: '2023-06-10' },
            { src: '/assets/img/media/PrideGranCanarias2.webp', alt: 'Pride Gran Canarias 2', caption: 'Pride Gran Canarias Festival', date: '2023-05-20' },
            { src: '/assets/img/media/SitgesPride1.webp', alt: 'Sitges Pride', caption: 'Sitges Pride', date: '2023-04-15' },
            { src: '/assets/img/media/SitgesPride2_optimized.webp', alt: 'Sitges Pride 2', caption: 'Sitges Pride Festival', date: '2023-03-10' },
            { src: '/assets/img/media/SitgesPride4.webp', alt: 'Sitges Pride 4', caption: 'Sitges Pride Night', date: '2023-02-20' },
            { src: '/assets/img/media/sitgesPride3.webp', alt: 'Sitges Pride 3', caption: 'Sitges Pride Party', date: '2023-01-15' },
            { src: '/assets/img/media/Sixpaczone1.webp', alt: 'Sixpaczone', caption: 'Sixpaczone Event', date: '2022-12-10' },
            { src: '/assets/img/media/Sixpaczone2_optimized.webp', alt: 'Sixpaczone 2', caption: 'Sixpaczone Party', date: '2022-11-20' },
            { src: '/assets/img/media/Sixpaczone4.webp', alt: 'Sixpaczone 4', caption: 'Sixpaczone Night', date: '2022-10-15' },
            { src: '/assets/img/media/privilege1.webp', alt: 'Privilege', caption: 'Privilege Club', date: '2022-09-10' },
        ],
        videos: [
            { id: '3CJOX4v3hww', thumbnail: 'https://i.ytimg.com/vi/3CJOX4v3hww/hqdefault.jpg', title: 'sixpakzone2015videobajaryoutube com', date: '2020-04-06', duration: '1:35' },
            { id: 'L_NqmQPf1Mw', thumbnail: 'https://i.ytimg.com/vi/L_NqmQPf1Mw/hqdefault.jpg', title: 'CLUB MERCI CUMPLEAÑOS Dj JOBANI 2010', date: '2010-11-02', duration: '9:11' },
            { id: '0dSI9i1G3dc', thumbnail: 'https://i.ytimg.com/vi/0dSI9i1G3dc/hqdefault.jpg', title: 'UP THE MADS (MERCI)', date: '2012-04-18', duration: '6:11' },
            { id: 'kflSAKOMs70', thumbnail: 'https://i.ytimg.com/vi/kflSAKOMs70/hqdefault.jpg', title: 'CHIWAS @ L´ATLANTIDA AGOSTO 2009', date: '2009-09-11', duration: '5:40' },
            { id: 'pGlbwId_YI4', thumbnail: 'https://i.ytimg.com/vi/pGlbwId_YI4/hqdefault.jpg', title: "MR.CHIWAS @ L' ATLANTIDA", date: '2011-08-26', duration: '14:41' },
        ],
    }
}
