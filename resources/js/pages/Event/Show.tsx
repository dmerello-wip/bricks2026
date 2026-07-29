import { usePage } from '@inertiajs/react';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import Paragraph from '@/components/editorial/Paragraph';
import PageLayout from '@/components/layout/PageLayout';
import Map from '@/components/Map';
import SeoHead from '@/components/seo/SeoHead';
import type { Block, EventModel, SeoData, SharedData } from '@/lib/types';

type EventShowProps = SharedData & {
    event: EventModel;
    blocks: Block[];
    seo: SeoData;
};

function parseLuogo(luogo: unknown): { latlng?: string } | null {
    if (!luogo) {
        return null;
    }
    if (typeof luogo === 'string') {
        try {
            return JSON.parse(luogo) as { latlng?: string };
        } catch {
            return null;
        }
    }
    return luogo as { latlng?: string };
}

export default function EventShow() {
    const { event, seo, blocks } = usePage<EventShowProps>().props;

    const blockList = Array.isArray(blocks) ? blocks : [];

    const descriptionBlock: Block | null = event.description
        ? {
              id: 0,
              type: 'paragraph',
              content: {
                  text: event.description,
                  columns: 'cols-1',
                  text_alignment: 'text-left',
                  text_color: 'block-text-dark',
              },
          }
        : null;

    let lat = NaN;
    let lng = NaN;
    const coords = parseLuogo(event.luogo);
    if (coords?.latlng) {
        const [latStr, lngStr] = coords.latlng.split('|');
        lat = parseFloat(latStr ?? '');
        lng = parseFloat(lngStr ?? '');
    }

    return (
        <PageLayout>
            <SeoHead seo={seo} />
            <article className="layout event-layout">
                {blockList.length > 0 && (
                    <>
                        {blockList.map((block: Block) => (
                            <BlockRenderer
                                key={block.id}
                                block={block}
                            />
                        ))}
                    </>
                )}

                <Map
                    lat={lat}
                    lng={lng}
                />
            </article>
        </PageLayout>
    );
}
