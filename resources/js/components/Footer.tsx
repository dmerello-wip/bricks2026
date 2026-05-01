import { LanguageSelector } from '@/components/LanguageSelector';
import type { MenuItem } from '@/lib/types';
import AppLink from '@/components/ui/AppLink';

export function Footer({ menu }: { menu: MenuItem[] }) {
    return (
        <footer className="footer bg-muted py-8">
            <div className="container flex justify-between">
                <p className="text-muted-foreground">
                    &copy; {new Date().getFullYear()}. All rights reserved.
                </p>

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
