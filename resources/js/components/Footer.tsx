// import { LanguageSelector } from '@/components/LanguageSelector';
import BrandLogo from '@/components/BrandLogo';
import AppLink from '@/components/ui/AppLink';
import type { MenuItem } from '@/lib/types';

export function Footer({ menu }: { menu: MenuItem[] }) {
    return (
        <footer className="footer bg-foreground py-8">
            <div className="container flex justify-between">
                <div className="logo">
                    <BrandLogo
                        width={60}
                        className="transition-all duration-500"
                    />
                </div>

                {menu.length > 0 && (
                    <div className="menu flex grow justify-center gap-8">
                        {menu.map((item) => (
                            <AppLink
                                key={item.id}
                                href={item.url!}
                                type={item.type}
                                target={item.target}
                                className="text-muted-foreground hover:text-primary"
                            >
                                {item.title}
                            </AppLink>
                        ))}
                    </div>
                )}
                {/* <LanguageSelector /> */}
            </div>
        </footer>
    );
}
