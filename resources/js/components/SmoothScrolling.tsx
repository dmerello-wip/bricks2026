'use client';

// import gsap from 'gsap';
import { ReactLenis, useLenis, type LenisRef } from 'lenis/react';
import { useRef, useEffect, type ReactNode } from 'react';

interface SmoothScrollingProps {
    children: ReactNode;
}

function SmoothScrolling({ children }: SmoothScrollingProps) {
    const lenisRef = useRef<LenisRef>(null);

    // for debugging purposes:
    // useLenis(({ scroll }: { scroll: number }) => {
    //     console.log('scroll', scroll);
    // });

    useEffect(() => {
        function update(time: number): void {
            lenisRef.current?.lenis?.raf(time * 1000);
        }

        // for gsap integration purposes:
        // in case of gsap set autoRaf={false} to ReactLenis component

        // gsap.ticker.add(update)
        // return () => {
        //   gsap.ticker.remove(update)
        // }
    });

    return (
        <ReactLenis
            root
            className="lenis-wrapper"
            ref={lenisRef}
            options={{
                lerp: 0.1,
                duration: 0.5,
                wheelMultiplier: 0.4,
                touchMultiplier: 1,
                touchInertiaExponent: 1.7,
                smoothWheel: true,
                gestureOrientation: 'vertical',
                autoRaf: true,
            }}
        >
            {children}
        </ReactLenis>
    );
}

export default SmoothScrolling;
