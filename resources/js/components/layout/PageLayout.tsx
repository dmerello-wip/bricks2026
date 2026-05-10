import { router, usePage } from '@inertiajs/react';
import { useLenis } from 'lenis/react';
import { useEffect } from 'react';
import { Footer } from '@/components/Footer';
import GoogleFonts from '@/components/GoogleFonts';
import { Header } from '@/components/Header/Header';
import SmoothScrolling from '@/components/SmoothScrolling';
import type { PageLayoutProps, SharedData } from '@/lib/types';

function HashScroll() {
    const lenis = useLenis();

    useEffect(() => {
        if (!lenis) {
            return;
        }

        const scrollToHash = () => {
            const hash = window.location.hash.replace(/^#/, '');
            if (!hash) {
                return;
            }
            const target = document.getElementById(hash);
            if (!target) {
                return;
            }
            lenis.scrollTo(target, { offset: -40 });
        };

        const raf = requestAnimationFrame(scrollToHash);
        window.addEventListener('hashchange', scrollToHash);
        const removeNavigateListener = router.on('navigate', () => {
            requestAnimationFrame(scrollToHash);
        });

        return () => {
            cancelAnimationFrame(raf);
            window.removeEventListener('hashchange', scrollToHash);
            removeNavigateListener();
        };
    }, [lenis]);

    return null;
}

export default function PageLayout({ children }: PageLayoutProps) {
    const { auth, menu } = usePage<SharedData>().props;

    const primaryMenu = menu.primary || [];
    const footerMenu = menu.footer || [];
    const isLogged = !!auth.user;

    return (
        <SmoothScrolling>
            <HashScroll />
            <GoogleFonts />
            <div className="flex min-h-screen flex-col">
                <Header
                    isLogged={isLogged}
                    menu={primaryMenu}
                />
                <main className="grow">{children}</main>
                <Footer menu={footerMenu} />
            </div>
        </SmoothScrolling>
    );
}
