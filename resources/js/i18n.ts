import en from '@/lang/en/translation.json';
import it from '@/lang/it/translation.json';
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

i18n.use(initReactI18next).init({
    fallbackLng: 'en',
    interpolation: {
        escapeValue: false,
    },
    resources: {
        en: { translation: en },
        it: { translation: it },
    },
});

export default i18n;
