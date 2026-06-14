import { Link } from '@inertiajs/react';
import { useLenis } from 'lenis/react';
import { useEffect, useState, useRef } from 'react';
import BrandLogo from '@/components/BrandLogo';
import { HeaderMenu } from '@/components/Header/HeaderMenu';
import { HeaderMenuMobile } from '@/components/Header/HeaderMenuMobile';
// import { LanguageSelector } from '@/components/LanguageSelector';
import type { MenuItem } from '@/lib/types';
import { cn } from '@/lib/utils';

export function Header({
    menu,
    isHome,
}: {
    isLogged: boolean;
    isHome: boolean;
    menu: MenuItem[];
}) {
    const [headerHeight, setHeaderHeight] = useState(0);
    const [hasScrolled, setHasScrolled] = useState(false);
    const headerRef = useRef(null);

    const isCompact = hasScrolled || !isHome;

    const headerClasses = cn(
        'header fixed top-0 z-50 w-full',
        isCompact ? 'bg-foreground/60 p-3 backdrop-blur-md' : 'p-3',
    );

    const logoSize = isCompact ? 70 : 130;

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
                    <Link href={'/'}>
                        <BrandLogo
                            width={logoSize}
                            className="transition-all duration-500"
                        />
                    </Link>
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

// function AuthTrigger({ isLogged }: { isLogged: boolean }) {
//     return isLogged ? (
//         <Button
//             asChild
//             variant="outline"
//         >
//             <Link href={dashboard()}>Dashboard</Link>
//         </Button>
//     ) : (
//         <Button
//             asChild
//             variant="outline"
//         >
//             <Link href={login()}>Log in</Link>
//         </Button>
//     );
// }
