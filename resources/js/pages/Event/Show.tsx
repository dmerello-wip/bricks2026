import { usePage } from '@inertiajs/react';
import Title from '@/components/editorial/atom/Title';
import PageLayout from '@/components/layout/PageLayout';
import Map from '@/components/Map';
import SeoHead from '@/components/seo/SeoHead';
import type { EventModel, SeoData, SharedData } from '@/lib/types';

type EventShowProps = SharedData & {
    event: EventModel;
    seo: SeoData;
};

export default function EventShow() {
    const { event, seo } = usePage<EventShowProps>().props;

    let lat = NaN;
    let lng = NaN;
    if (event.luogo) {
        try {
            const choords = JSON.parse(event.luogo) as { latlng?: string };
            const [latStr, lngStr] = (choords.latlng ?? '').split('|');
            lat = parseFloat(latStr ?? '');
            lng = parseFloat(lngStr ?? '');
        } catch {
            // luogo is not valid JSON — leave coords as NaN, Map will render nothing
        }
    }

    return (
        <PageLayout>
            <SeoHead seo={seo} />
            <article className="pt-16">
                <div className="container mx-auto flex max-w-4xl flex-col gap-4">
                    <Title
                        content={event.title ?? ''}
                        seoTag="h1"
                    />
                    {event.data && (
                        <p className="text-muted-foreground">
                            {new Date(event.data).toLocaleString()}
                        </p>
                    )}
                    <Map
                        lat={lat}
                        lng={lng}
                    />
                    {event.description && <p>{event.description}</p>}
                </div>
            </article>
        </PageLayout>
    );
}
