import { Link } from '@inertiajs/react';
import BrandLogo from '@/components/BrandLogo';
import { HeaderMenu } from '@/components/Header/HeaderMenu';
import { HeaderMenuMobile } from '@/components/Header/HeaderMenuMobile';
import { LanguageSelector } from '@/components/LanguageSelector';
import { Button } from '@/components/ui/Button';
import type { MenuItem } from '@/lib/types';
import { dashboard, login } from '@/routes';
import { useEffect, useState, useRef } from 'react';
import { useLenis } from 'lenis/react';
import { cn } from '@/lib/utils';

export function Header({ menu }: { isLogged: boolean; menu: MenuItem[] }) {
    const [headerHeight, setHeaderHeight] = useState(0);
    const [hasScrolled, setHasScrolled] = useState(false);
    const headerRef = useRef(null);

    const headerClasses = cn(
        'header fixed top-0 z-50 w-full',
        hasScrolled ? 'bg-foreground/80 p-3 backdrop-blur-md' : 'p-6',
    );

    const logoSize = hasScrolled ? 60 : 180;

    useLenis((lenis) => {
        if (lenis.targetScroll > headerHeight) {
            setHasScrolled(true);
        } else {
            setHasScrolled(false);
        }
    });

    useEffect(() => {
        if (headerRef.current) {
            setHeaderHeight(headerRef.current.offsetHeight);
            window.addEventListener('resize', () => {
                setHeaderHeight(headerRef.current.offsetHeight);
            });
        }
    }, []);

    return (
        <header
            ref={headerRef}
            className={headerClasses}
        >
            <div className="header__inner flex items-center justify-between gap-6">
                {/* Logo */}
                <div className="logo">
                    <BrandLogo
                        width={logoSize}
                        className="transition-all duration-500"
                    />
                </div>

                {/* Menu Primary Desktop*/}
                <div className="header__actions hidden grow justify-start lg:flex">
                    <HeaderMenu menu={menu} />

                    {/* Auth & Language */}

                    {/*
                    <div className="flex items-center gap-2">
                        <AuthTrigger isLogged={isLogged} />
                        <LanguageSelector />
                    </div>
                    */}
                </div>

                {/* Menu Primary Mobile*/}
                <div className="header__trigger lg:hidden">
                    <HeaderMenuMobile menu={menu}>
                        {/*
                        <AuthTrigger isLogged={isLogged} />
                        <LanguageSelector />
                        */}
                    </HeaderMenuMobile>
                </div>
            </div>
        </header>
    );
}

function AuthTrigger({ isLogged }: { isLogged: boolean }) {
    return isLogged ? (
        <Button
            asChild
            variant="outline"
        >
            <Link href={dashboard()}>Dashboard</Link>
        </Button>
    ) : (
        <Button
            asChild
            variant="outline"
        >
            <Link href={login()}>Log in</Link>
        </Button>
    );
}
