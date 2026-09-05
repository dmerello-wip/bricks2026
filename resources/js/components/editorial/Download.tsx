import { ChevronUp, Download as DownloadIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/Button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/Collapsible';
import { revealProps } from '@/lib/reveal';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';

function AccordionRowItem({
    block,
    itemIndex,
}: {
    block: Block;
    itemIndex: number;
}) {
    const { title, description } = block.content ?? {};
    const download_url = block.files?.download_url;

    return (
        <div
            className="border-b border-border py-4"
            {...revealProps(itemIndex)}
        >
            <div className="flex items-center gap-4">
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <p className="text-sm leading-5 font-medium text-foreground">
                        {title}
                    </p>
                    {description && (
                        <p className="text-sm leading-5 text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                {download_url && (
                    <a
                        href={download_url}
                        download
                    >
                        <Button
                            variant="outline"
                            size="icon"
                            className="size-8 rounded-(--radius) shadow-2xs"
                        >
                            <DownloadIcon className="size-4" />
                            <span className="sr-only">Download</span>
                        </Button>
                    </a>
                )}
            </div>
        </div>
    );
}

function AccordionGroup({ block }: { block: Block }) {
    const [isOpen, setIsOpen] = useState(true);
    const items = block.children ?? [];

    return (
        <Collapsible
            open={isOpen}
            onOpenChange={setIsOpen}
            className="w-full"
        >
            <CollapsibleTrigger className="flex w-full cursor-pointer items-center gap-4 py-4 text-left">
                <span className="flex-1 truncate font-bold">
                    {block.content?.title}
                </span>
                <ChevronUp
                    className={cn(
                        'size-4 shrink-0 text-foreground transition-transform duration-200',
                        !isOpen && 'rotate-180',
                    )}
                />
            </CollapsibleTrigger>
            <CollapsibleContent>
                {items.map((item, i) => (
                    <AccordionRowItem
                        key={item.id}
                        block={item}
                        itemIndex={i}
                    />
                ))}
            </CollapsibleContent>
        </Collapsible>
    );
}

export default function Download({ block }: { block: Block }) {
    if (!block) return null;

    const groups = block.children ?? [];

    return (
        <section
            id={`block-${block.id}`}
            className="bg-background py-16"
        >
            <div className="container mx-auto px-6">
                <div className="mx-auto w-full max-w-5xl">
                    {groups.map((group) => (
                        <AccordionGroup
                            key={group.id}
                            block={group}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}
