import AuthLayoutTemplate from '@/components/layout/auth/AuthSimpleLayout';
import SmoothScrolling from '@/components/SmoothScrolling';

export default function AuthLayout({
    children,
    title,
    description,
    ...props
}: {
    children: React.ReactNode;
    title: string;
    description: string;
}) {
    return (
        <SmoothScrolling>
            <AuthLayoutTemplate
                title={title}
                description={description}
                {...props}
            >
                {children}
            </AuthLayoutTemplate>
        </SmoothScrolling>
    );
}
