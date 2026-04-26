import { ReactNode } from 'react';
import { Card, CardContent } from '../ui/Card';
import type { Block } from '@/lib/types';

export default function BlockFallback({
    block,
    children,
}: {
    block: Block;
    children: ReactNode;
}) {
    return (
        <div className="container">
            <Card
                data-block-id={block.id}
                className="my-4"
            >
                <CardContent className="pt-6">
                    <div className="mb-2 text-xs text-muted-foreground uppercase">
                        Type: <span className="font-bold">{block.type}</span>{' '}
                        contents (Fallback)
                    </div>
                    <pre className="mb-8 max-h-40 overflow-auto rounded-md bg-muted p-4 text-xs">
                        {JSON.stringify(block.content, null, 2)}
                    </pre>
                    {(block.medias?.length ?? 0) > 0 && (
                        <>
                            <div className="mb-2 text-xs font-bold text-muted-foreground uppercase">
                                Type: {block.type} medias (Fallback)
                            </div>
                            <pre className="mb-8 max-h-40 overflow-auto rounded-md bg-muted p-4 text-xs">
                                {JSON.stringify(block.medias, null, 2)}
                            </pre>
                        </>
                    )}

                    {(block.children?.length ?? 0) > 0 && (
                        <>
                            <div className="mb-2 pl-4 text-xs font-bold text-muted-foreground uppercase">
                                Type: {block.type} childrens (Fallback)
                            </div>
                            {children && (
                                <div className="mt-4 border-l-2 border-dashed pl-4">
                                    {children}
                                </div>
                            )}
                        </>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
