import { useEffect, useRef, useState } from 'react';
import 'plyr/dist/plyr.css';

type VideoConfig =
    | { provider: 'youtube'; id: string }
    | { provider: 'vimeo'; id: string }
    | { provider: 'file'; url: string };

function extractId(url: string, provider: 'youtube' | 'vimeo'): string {
    if (provider === 'youtube') {
        return url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/)?.[1] ?? url;
    }
    return url.match(/vimeo\.com\/(\d+)/)?.[1] ?? url;
}

function VideoPlayer({ video }: { video: VideoConfig }) {
    const ref = useRef<HTMLDivElement & HTMLVideoElement>(null);
    const [isIdle, setIsIdle] = useState(true);

    useEffect(() => {
        if (!ref.current) return;
        let player: InstanceType<(typeof import('plyr'))['default']> | null =
            null;
        import('plyr').then(({ default: Plyr }) => {
            player = new Plyr(ref.current!, {
                controls: [
                    'play-large',
                    'play',
                    'progress',
                    'current-time',
                    'mute',
                    'volume',
                    'fullscreen',
                ],
                hideControls: true,
            });
            player.on('play', () => setIsIdle(false));
            player.on('ended', () => setIsIdle(true));
        });
        return () => {
            player?.destroy();
        };
    }, []);

    const mediaEl =
        video.provider === 'youtube' ? (
            <div
                ref={ref}
                data-plyr-provider="youtube"
                data-plyr-embed-id={video.id}
            />
        ) : video.provider === 'vimeo' ? (
            <div
                ref={ref}
                data-plyr-provider="vimeo"
                data-plyr-embed-id={video.id}
            />
        ) : (
            <video ref={ref}>
                <source
                    src={video.url}
                    type="video/mp4"
                />
            </video>
        );

    return (
        <div className={isIdle ? 'plyr-phase-idle' : undefined}>{mediaEl}</div>
    );
}

export default function Video({
    videoType = 'file',
    youtubeInput,
    vimeoInput,
    fileUrl,
}: {
    videoType?: string;
    youtubeInput?: string;
    vimeoInput?: string;
    fileUrl?: string;
}) {
    const youtubeId =
        videoType === 'youtube' && youtubeInput
            ? extractId(youtubeInput, 'youtube')
            : null;

    const vimeoId =
        videoType === 'vimeo' && vimeoInput
            ? extractId(vimeoInput, 'vimeo')
            : null;

    const videoConfig: VideoConfig | null = (() => {
        if (youtubeId) return { provider: 'youtube', id: youtubeId };
        if (vimeoId) return { provider: 'vimeo', id: vimeoId };
        if (videoType === 'file' && fileUrl)
            return { provider: 'file', url: fileUrl };
        return null;
    })();

    return <>{videoConfig && <VideoPlayer video={videoConfig} />}</>;
}
