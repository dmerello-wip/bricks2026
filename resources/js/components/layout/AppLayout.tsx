import AppLayoutTemplate from '@/components/layout/app/AppSidebarLayout';
import type { AppLayoutProps } from '@/lib/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        {...props}
    >
        {children}
    </AppLayoutTemplate>
);
