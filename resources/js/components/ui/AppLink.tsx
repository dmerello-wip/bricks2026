import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useContext } from 'react';
import { PreviewContext } from '@/lib/context/preview';

// Manages:
// - Internal vs external links (<a> vs inertia Link)
// - Inertia links in Twill iframe preview mode (where Inertia navigation brakes the preview)

// Omit HTML events that conflict with Inertia's own callback signatures
type SharedProps = Omit<React.HTMLAttributes<Element>, 'children' | 'onProgress' | 'onError'>;

export type AppLinkProps = SharedProps & {
    href: string;
    type?: 'internal' | 'external' | 'download';
    target?: '_self' | '_blank';
    download?: string;
    children?: React.ReactNode;
    preserveScroll?: InertiaLinkProps['preserveScroll'];
    preserveState?: InertiaLinkProps['preserveState'];
    replace?: InertiaLinkProps['replace'];
    only?: InertiaLinkProps['only'];
    except?: InertiaLinkProps['except'];
    headers?: InertiaLinkProps['headers'];
};

export default function AppLink({
    href,
    type = 'internal',
    target = '_self',
    download,
    children,
    preserveScroll,
    preserveState,
    replace,
    only,
    except,
    headers,
    ...anchorProps
}: AppLinkProps) {
    const isPreview = useContext(PreviewContext);
    const rel = target === '_blank' ? 'noopener noreferrer' : undefined;

    if (type === 'download') {
        return (
            <a href={href} download={download ?? true} {...anchorProps}>
                {children}
            </a>
        );
    }

    if (type === 'external') {
        return (
            <a href={href} target={target} rel={rel} {...anchorProps}>
                {children}
            </a>
        );
    }

    if (isPreview) {
        return (
            <a href={href} target={target} {...anchorProps}>
                {children}
            </a>
        );
    }

    return (
        <Link
            href={href}
            target={target}
            preserveScroll={preserveScroll}
            preserveState={preserveState}
            replace={replace}
            only={only}
            except={except}
            headers={headers}
            {...anchorProps}
        >
            {children}
        </Link>
    );
}
