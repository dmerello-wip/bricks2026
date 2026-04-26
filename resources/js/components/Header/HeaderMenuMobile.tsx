import { Menu } from 'lucide-react';
import AppLink from '@/components/ui/AppLink';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/Sheet';
import type { MenuItem } from '@/lib/types';

export function HeaderMenuMobile({
    menu,
    children,
}: {
    menu: MenuItem[];
    children?: React.ReactNode;
}) {
    return (
        <Sheet>
            <SheetTrigger asChild>
                <Menu className="h-6 w-6" />
            </SheetTrigger>
            <SheetContent side="left">
                {/* sheet header: just for accessibility */}
                <SheetHeader>
                    <SheetTitle className="sr-only">Navigazione</SheetTitle>
                    <SheetDescription className="sr-only">
                        Navigazione principale
                    </SheetDescription>
                </SheetHeader>

                {/* sheet content */}
                <div className="flex h-full flex-col justify-center overflow-y-auto p-4">
                    {menu.map((item) => (
                        <HeaderMenuMobileItem
                            item={item}
                            key={item.id}
                        />
                    ))}
                </div>

                {/* sheet footer */}
                <SheetFooter className="p-4">{children}</SheetFooter>
            </SheetContent>
        </Sheet>
    );
}

function HeaderMenuMobileItem({ item }: { item: MenuItem }) {
    if (!item.url) return null;
    const hasChildren = item.children && item.children.length > 0;

    return (
        <div className="p-4">
            <AppLink
                className="text-lg font-semibold"
                href={item.url!}
                type={item.type}
                target={item.target}
            >
                {item.title}
            </AppLink>
            {hasChildren && (
                <div className="flex flex-col gap-4 pt-4">
                    {item.children!.map((child) => (
                        <Level2MobileItem
                            key={child.id}
                            item={child}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

function Level2MobileItem({ item }: { item: MenuItem }) {
    if (!item.url) return null;
    const hasChildren = item.children && item.children.length > 0;

    return (
        <div>
            <AppLink
                href={item.url!}
                type={item.type}
                target={item.target}
            >
                {item.title}
            </AppLink>
            {hasChildren && (
                <div className="mt-2 ml-2 flex flex-col gap-2 border-l pl-2">
                    {item.children!.map((grandchild) => (
                        <Level3MobileItem
                            key={grandchild.id}
                            item={grandchild}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

function Level3MobileItem({ item }: { item: MenuItem }) {
    if (!item.url) return null;

    return (
        <AppLink
            className="text-sm text-muted-foreground"
            href={item.url!}
            type={item.type}
            target={item.target}
        >
            {item.title}
        </AppLink>
    );
}
