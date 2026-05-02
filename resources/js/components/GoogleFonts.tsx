import { Head } from '@inertiajs/react';

export default function GoogleFonts() {
    return (
        <Head>
            <link
                rel="preconnect"
                href="https://fonts.googleapis.com"
            />
            <link
                rel="preconnect"
                href="https://fonts.gstatic.com"
                crossOrigin="anonymous"
            />
            <link
                href="https://fonts.googleapis.com/css2?family=Unbounded:wght@200..900&display=swap"
                rel="stylesheet"
            />
        </Head>
    );
}
