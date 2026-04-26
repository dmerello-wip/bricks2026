import { usePage } from '@inertiajs/react';
import { Footer } from '@/components/Footer';
import GoogleFonts from '@/components/GoogleFonts';
import { Header } from '@/components/Header/Header';
import SmoothScrolling from '@/components/SmoothScrolling';
import type { PageLayoutProps, SharedData } from '@/lib/types';

export default function PageLayout({ children }: PageLayoutProps) {
    const { auth, menu } = usePage<SharedData>().props;

    const primaryMenu = menu.primary || [];
    const footerMenu = menu.footer || [];
    const isLogged = !!auth.user;

    return (
        <SmoothScrolling>
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
