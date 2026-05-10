'use client';

// import gsap from 'gsap';
import { ReactLenis, type LenisRef } from 'lenis/react';
import { useRef, type ReactNode } from 'react';

interface SmoothScrollingProps {
    children: ReactNode;
}

function SmoothScrolling({ children }: SmoothScrollingProps) {
    const lenisRef = useRef<LenisRef>(null);

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
