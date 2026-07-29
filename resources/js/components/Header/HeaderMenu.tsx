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

export function HeaderMenu({ menu }: { menu: MenuItem[] }) {
    return (
        <NavigationMenu viewport={false}>
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
    const children = item.children ?? [];

    if (children.length > 0) {
        return (
            <NavigationMenuItem>
                <NavigationMenuTrigger>{item.title}</NavigationMenuTrigger>
                <NavigationMenuContent>
                    <ul className="min-w-48">
                        {children.map((child) => (
                            <SubMenuItem
                                key={child.id}
                                item={child}
                            />
                        ))}
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        );
    }

    if (!item.url) return null;

    return (
        <NavigationMenuItem>
            <MenuLink
                item={item}
                className={navigationMenuTriggerStyle()}
            />
        </NavigationMenuItem>
    );
}

function SubMenuItem({ item }: { item: MenuItem }) {
    const children = item.children ?? [];

    if (children.length === 0) {
        return (
            <li>
                <MenuLink item={item} />
            </li>
        );
    }

    return (
        <li className="mb-1">
            <MenuLink
                item={item}
                className="font-medium"
            />
            <ul className="ml-2 border-l pl-2">
                {children.map((child) => (
                    <SubMenuItem
                        key={child.id}
                        item={child}
                    />
                ))}
            </ul>
        </li>
    );
}

/**
 * Styling lives on NavigationMenuLink (merged through cn) rather than on the
 * AppLink child: Radix's asChild concatenates both className strings without
 * tailwind-merge, so conflicting utilities would be resolved by CSS order.
 */
function MenuLink({ item, className }: { item: MenuItem; className?: string }) {
    if (!item.url) return null;

    return (
        <NavigationMenuLink
            asChild
            className={className}
        >
            <AppLink
                href={item.url}
                type={item.type}
                target={item.target}
            >
                {item.title}
            </AppLink>
        </NavigationMenuLink>
    );
}
