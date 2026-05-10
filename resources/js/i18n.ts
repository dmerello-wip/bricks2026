import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import it from '@/lang/it/translation.json';

i18n.use(initReactI18next).init({
    fallbackLng: 'it',
    interpolation: {
        escapeValue: false,
    },
    resources: {
        // en: { translation: en },
        it: { translation: it },
    },
});

export default i18n;
