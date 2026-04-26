import VideoEmbed from '@/components/VideoPlayer';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import Title from './atom/Title';

export default function Video({ block }: { block: Block }) {
    if (!block) return null;

    const noPaddingBottom = block.content?.no_padding_bottom ?? false;

    return (
        <section
            className={cn(
                'group',
                noPaddingBottom ? 'pt-16' : 'py-16',
                block.content?.text_color,
            )}
            style={{ backgroundColor: block.content?.bg_color || undefined }}
        >
            <div className="w-full md:container">
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
