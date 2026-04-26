import { AppContent } from '@/components/AppContent';
import { AppHeader } from '@/components/AppHeader';
import { AppShell } from '@/components/AppShell';
import type { AppLayoutProps } from '@/lib/types';

export default function AppHeaderLayout({
    children,
    breadcrumbs,
}: AppLayoutProps) {
    return (
        <AppShell>
            <AppHeader breadcrumbs={breadcrumbs} />
            <AppContent>{children}</AppContent>
        </AppShell>
    );
}
