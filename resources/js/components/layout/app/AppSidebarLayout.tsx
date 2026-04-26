import { AppContent } from '@/components/AppContent';
import { AppShell } from '@/components/AppShell';
import { AppSidebar } from '@/components/AppSidebar';
import { AppSidebarHeader } from '@/components/AppSidebarHeader';
import type { AppLayoutProps } from '@/lib/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent
                variant="sidebar"
                className="overflow-x-hidden"
            >
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
