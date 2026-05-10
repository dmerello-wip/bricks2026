import { usePage } from '@inertiajs/react';
import Title from '@/components/editorial/atom/Title';
import PageLayout from '@/components/layout/PageLayout';
import Map from '@/components/Map';
import SeoHead from '@/components/seo/SeoHead';
import type { EventModel, SeoData, SharedData } from '@/lib/types';
import Hero from '@/components/editorial/Hero';

type EventShowProps = SharedData & {
    event: EventModel;
    seo: SeoData;
};

export default function EventShow() {
    const { event, seo } = usePage<EventShowProps>().props;

    const data = new Date(event.data)
        .toLocaleString('it-IT', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        })
        .replace(',', ', ore');

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
            <article className="event-layout">
                <Hero
                    block={{
                        id: 1,
                        type: 'hero',
                        content: {
                            title: event.title ?? '',
                            subtitle: data,
                            text_alignment: 'text-center',
                            bg_color: 'black',
                        },
                    }}
                ></Hero>
                <Map
                    lat={lat}
                    lng={lng}
                />
                {event.description && <p>{event.description}</p>}
            </article>
        </PageLayout>
    );
}
