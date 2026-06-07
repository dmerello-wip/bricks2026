import { useEffect, useRef } from 'react';

declare global {
    interface Window {
        google?: {
            maps: {
                Map: new (el: HTMLElement, opts: object) => object;
                Marker: new (opts: object) => object;
            };
        };
    }
}

const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string | undefined;
const MAP_ID = (import.meta.env.VITE_GOOGLE_MAPS_MAP_ID as string | undefined) ?? 'a5becd16a54992c1eb2f9d6d';

let scriptPromise: Promise<void> | null = null;

function loadScript(): Promise<void> {
    if (scriptPromise) return scriptPromise;
    scriptPromise = new Promise((resolve, reject) => {
        if (window.google?.maps) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Google Maps'));
        document.head.appendChild(script);
    });
    return scriptPromise;
}

export default function Map({ lat, lng }: { lat: number; lng: number }) {
    const mapRef = useRef<HTMLDivElement>(null);
    const hasCoords = Number.isFinite(lat) && Number.isFinite(lng);

    useEffect(() => {
        if (!API_KEY || !mapRef.current || !hasCoords) return;

        const center = { lat, lng };

        loadScript().then(() => {
            if (!mapRef.current || !window.google?.maps) return;
            const map = new window.google.maps.Map(mapRef.current, {
                center,
                zoom: 15,
                disableDefaultUI: false,
                mapId: MAP_ID,
            });
            new window.google.maps.Marker({ position: center, map });
        });
    }, [lat, lng, hasCoords]);

    if (!hasCoords) return null;

    return (
        <section className="map">
            <div className="w-full md:container">
                <div
                    ref={mapRef}
                    className="h-120 w-full overflow-hidden rounded-lg"
                />
            </div>
        </section>
    );
}
