import { Link } from '@inertiajs/react';
import BrandLogo from '@/components/BrandLogo';
import { HeaderMenu } from '@/components/Header/HeaderMenu';
import { HeaderMenuMobile } from '@/components/Header/HeaderMenuMobile';
import { LanguageSelector } from '@/components/LanguageSelector';
import { Button } from '@/components/ui/Button';
import type { MenuItem } from '@/lib/types';
import { dashboard, login } from '@/routes';

export function Header({
    isLogged,
    menu,
}: {
    isLogged: boolean;
    menu: MenuItem[];
}) {
    return (
        <header className="site-header sticky top-0 z-50 border-b py-2">
            <div className="container flex items-center justify-between gap-6">
                {/* Logo */}
                <div className="logo">
                    <BrandLogo width={80} />
                </div>

                {/* Menu Primary Desktop*/}
                <div className="site-header__actions hidden grow justify-between lg:flex">
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
                <div className="site-header__trigger lg:hidden">
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
