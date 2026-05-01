import { router } from '@inertiajs/react';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { I18nextProvider } from 'react-i18next';
import '../css/app.css';
import i18n from './i18n';
import { setUrlDefaults } from './wayfinder';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const locale =
            (props.initialPage.props as { locale?: string }).locale ?? 'it';
        setUrlDefaults({ locale });

        router.on('navigate', (event) => {
            const pageLocale =
                (event.detail.page.props as { locale?: string }).locale ?? 'it';
            setUrlDefaults({ locale: pageLocale });
        });

        const root = createRoot(el);

        root.render(
            <StrictMode>
                <I18nextProvider i18n={i18n}>
                    <App {...props} />
                </I18nextProvider>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
