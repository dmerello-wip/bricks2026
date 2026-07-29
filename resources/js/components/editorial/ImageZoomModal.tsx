import { XIcon } from 'lucide-react';
import { useRef, useState } from 'react';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogTitle,
} from '@/components/ui/Dialog';
import { Spinner } from '@/components/ui/Spinner';
import type { ImageData } from '@/lib/types';
import { cn } from '@/lib/utils';

type Size = { width: number; height: number };
type Offset = { x: number; y: number };

type DragOrigin = {
    pointerX: number;
    pointerY: number;
    offset: Offset;
};

const CENTERED: Offset = { x: 0, y: 0 };

/** Sub-pixel overflow is not worth a grab cursor or a drag handler. */
const PAN_THRESHOLD = 1;

function clampToPanRange(value: number, limit: number): number {
    return Math.min(limit, Math.max(-limit, value));
}

/**
 * Renders the image at cover scale — smallest side filling the frame, the other
 * overflowing — and lets the user drag along whichever axis overflows.
 */
function PannableImage({ image }: { image: ImageData }) {
    const dragOriginRef = useRef<DragOrigin | null>(null);

    const [frameSize, setFrameSize] = useState<Size | null>(null);
    const [naturalSize, setNaturalSize] = useState<Size | null>(null);
    const [offset, setOffset] = useState<Offset>(CENTERED);
    const [isDragging, setIsDragging] = useState(false);

    const observeFrame = (frameEl: HTMLDivElement | null) => {
        if (!frameEl) {
            return;
        }

        const observer = new ResizeObserver(([entry]) => {
            setFrameSize({
                width: entry.contentRect.width,
                height: entry.contentRect.height,
            });
        });

        observer.observe(frameEl);

        return () => observer.disconnect();
    };

    /**
     * An image restored from cache can finish loading before onLoad is wired up,
     * in which case the completed state has to be read on attach instead.
     */
    const readNaturalSizeOnAttach = (imageEl: HTMLImageElement | null) => {
        if (imageEl?.complete && imageEl.naturalWidth > 0) {
            setNaturalSize({
                width: imageEl.naturalWidth,
                height: imageEl.naturalHeight,
            });
        }
    };

    const isReady = frameSize !== null && naturalSize !== null;

    const coverScale = isReady
        ? Math.max(
              frameSize.width / naturalSize.width,
              frameSize.height / naturalSize.height,
          )
        : 1;

    const displayedSize: Size | null = isReady
        ? {
              width: naturalSize.width * coverScale,
              height: naturalSize.height * coverScale,
          }
        : null;

    const maxPanX =
        frameSize && displayedSize
            ? Math.max(0, (displayedSize.width - frameSize.width) / 2)
            : 0;
    const maxPanY =
        frameSize && displayedSize
            ? Math.max(0, (displayedSize.height - frameSize.height) / 2)
            : 0;

    const isPannable = maxPanX > PAN_THRESHOLD || maxPanY > PAN_THRESHOLD;

    /** Re-clamped on the fly so a frame resize cannot leave a gap at the edges. */
    const panOffset: Offset = {
        x: clampToPanRange(offset.x, maxPanX),
        y: clampToPanRange(offset.y, maxPanY),
    };

    const handlePointerDown = (event: React.PointerEvent<HTMLDivElement>) => {
        if (!isPannable) {
            return;
        }

        event.currentTarget.setPointerCapture(event.pointerId);
        dragOriginRef.current = {
            pointerX: event.clientX,
            pointerY: event.clientY,
            offset: panOffset,
        };
        setIsDragging(true);
    };

    const handlePointerMove = (event: React.PointerEvent<HTMLDivElement>) => {
        const origin = dragOriginRef.current;

        if (!origin) {
            return;
        }

        setOffset({
            x: clampToPanRange(
                origin.offset.x + (event.clientX - origin.pointerX),
                maxPanX,
            ),
            y: clampToPanRange(
                origin.offset.y + (event.clientY - origin.pointerY),
                maxPanY,
            ),
        });
    };

    const handlePointerUp = (event: React.PointerEvent<HTMLDivElement>) => {
        if (!dragOriginRef.current) {
            return;
        }

        event.currentTarget.releasePointerCapture(event.pointerId);
        dragOriginRef.current = null;
        setIsDragging(false);
    };

    return (
        <div
            ref={observeFrame}
            onPointerDown={handlePointerDown}
            onPointerMove={handlePointerMove}
            onPointerUp={handlePointerUp}
            onPointerCancel={handlePointerUp}
            className={cn(
                'relative h-full w-full touch-none overflow-hidden',
                isPannable && (isDragging ? 'cursor-grabbing' : 'cursor-grab'),
            )}
        >
            {!isReady && (
                <Spinner className="absolute top-1/2 left-1/2 size-8 -translate-x-1/2 -translate-y-1/2 text-white" />
            )}

            <img
                ref={readNaturalSizeOnAttach}
                src={image.src}
                alt={image.alt || ''}
                draggable={false}
                onLoad={(event) =>
                    setNaturalSize({
                        width: event.currentTarget.naturalWidth,
                        height: event.currentTarget.naturalHeight,
                    })
                }
                className="absolute top-1/2 left-1/2 max-w-none select-none"
                style={
                    displayedSize
                        ? {
                              width: displayedSize.width,
                              height: displayedSize.height,
                              transform: `translate(-50%, -50%) translate(${panOffset.x}px, ${panOffset.y}px)`,
                          }
                        : { visibility: 'hidden' }
                }
            />
        </div>
    );
}

export default function ImageZoomModal({
    image,
    open,
    onOpenChange,
}: {
    image: ImageData;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    return (
        <Dialog
            open={open}
            onOpenChange={onOpenChange}
        >
            <DialogContent
                aria-describedby={undefined}
                showCloseButton={false}
                className="h-[90vh] w-[90vw] max-w-[90vw] gap-0 overflow-hidden border-0 bg-black p-0 sm:max-w-[90vw]"
            >
                <DialogTitle className="sr-only">
                    {image.alt || 'Zoomed image'}
                </DialogTitle>

                {/* Keyed so a src change starts over from centered and unloaded. */}
                <PannableImage
                    key={image.src}
                    image={image}
                />

                <DialogClose className="absolute top-3 right-3 z-10 rounded-full bg-black/60 p-2 text-white transition-colors hover:bg-black/80 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-hidden">
                    <XIcon className="size-5" />
                    <span className="sr-only">Close</span>
                </DialogClose>
            </DialogContent>
        </Dialog>
    );
}
