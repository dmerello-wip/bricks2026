import { Head } from '@inertiajs/react';
import PageLayout from '@/components/layout/PageLayout';

export default function Page() {
    return (
        <PageLayout>
            <Head title={'welcome'} />
            <div className="container m-auto py-16">TODO</div>
        </PageLayout>
    );
}
