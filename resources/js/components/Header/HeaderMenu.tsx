import AppLink from '@/components/ui/AppLink';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/components/ui/NavigationMenu';
import type { MenuItem } from '@/lib/types';

const menuLinkClass =
    'block rounded-sm px-2 py-1.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground';

export function HeaderMenu({ menu }: { menu: MenuItem[] }) {
    return (
        <NavigationMenu
            viewport={false}
            className="mx-auto flex grow justify-center"
        >
            <NavigationMenuList className="gap-6">
                {menu.map((item) => (
                    <Level1Item
                        item={item}
                        key={item.id}
                    />
                ))}
            </NavigationMenuList>
        </NavigationMenu>
    );
}

function Level1Item({ item }: { item: MenuItem }) {
    if (!item.url) return null;
    const hasChildren = item.children && item.children.length > 0;

    return (
        <NavigationMenuItem>
            {hasChildren ? (
                <>
                    <NavigationMenuTrigger>{item.title}</NavigationMenuTrigger>
                    <NavigationMenuContent>
                        <ul className="min-w-48 p-2">
                            {item.children!.map((child) => (
                                <Level2Item
                                    key={child.id}
                                    item={child}
                                />
                            ))}
                        </ul>
                    </NavigationMenuContent>
                </>
            ) : (
                <NavigationMenuLink
                    asChild
                    className={navigationMenuTriggerStyle()}
                >
                    <AppLink
                        href={item.url!}
                        type={item.type}
                        target={item.target}
                    >
                        {item.title}
                    </AppLink>
                </NavigationMenuLink>
            )}
        </NavigationMenuItem>
    );
}

function Level2Item({ item }: { item: MenuItem }) {
    if (!item.url) return null;
    const hasChildren = item.children && item.children.length > 0;

    if (hasChildren) {
        return (
            <li className="mb-1">
                <div className="px-2 py-1.5 text-sm font-medium">
                    <AppLink
                        href={item.url!}
                        type={item.type}
                        target={item.target}
                        className={menuLinkClass}
                    >
                        {item.title}
                    </AppLink>
                </div>
                <ul className="ml-2 border-l pl-2">
                    {item.children!.map((grandchild) => (
                        <Level3Item
                            key={grandchild.id}
                            item={grandchild}
                        />
                    ))}
                </ul>
            </li>
        );
    }

    return (
        <li>
            <NavigationMenuLink asChild>
                <AppLink
                    href={item.url!}
                    type={item.type}
                    target={item.target}
                    className={menuLinkClass}
                >
                    {item.title}
                </AppLink>
            </NavigationMenuLink>
        </li>
    );
}

function Level3Item({ item }: { item: MenuItem }) {
    if (!item.url) return null;

    return (
        <li>
            <NavigationMenuLink asChild>
                <AppLink
                    href={item.url!}
                    type={item.type}
                    target={item.target}
                    className={menuLinkClass}
                >
                    {item.title}
                </AppLink>
            </NavigationMenuLink>
        </li>
    );
}
