import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/AppearanceTabs';
import Heading from '@/components/Heading';
import AppLayout from '@/components/layout/AppLayout';
import SettingsLayout from '@/components/layout/settings/Layout';
import { edit as editAppearance } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/lib/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: editAppearance().url,
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance Settings</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Appearance settings"
                        description="Update your account's appearance settings"
                    />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
