import VideoEmbed from '@/components/VideoPlayer';
import { revealProps } from '@/lib/reveal';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';

export default function Video({ block }: { block: Block }) {
    if (!block) return null;

    const noPaddingBottom = block.content?.no_padding_bottom ?? false;

    return (
        <section
            id={`block-${block.id}`}
            className={cn(
                'group',
                noPaddingBottom ? 'pt-16' : 'py-16',
                block.content?.text_color,
            )}
            style={{ backgroundColor: block.content?.bg_color || undefined }}
        >
            <div
                className="w-full md:container"
                {...revealProps()}
            >
                <VideoEmbed
                    videoType={block.content?.video_type}
                    youtubeInput={block.content?.youtube_id}
                    vimeoInput={block.content?.vimeo_id}
                    fileUrl={block.files?.video_file}
                />
            </div>
        </section>
    );
}
