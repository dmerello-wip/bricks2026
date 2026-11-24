import { useEffect, useRef } from 'react';

type GoogleMapsApi = {
    importLibrary: (name: 'maps' | 'marker') => Promise<unknown>;
    Map: new (el: HTMLElement, opts: object) => object;
    Marker: new (opts: object) => object;
};

declare global {
    interface Window {
        google?: { maps: GoogleMapsApi };
        __googleMapsReady?: () => void;
    }
}

const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string | undefined;
const MAP_ID =
    (import.meta.env.VITE_GOOGLE_MAPS_MAP_ID as string | undefined) ??
    'a5becd16a54992c1eb2f9d6d';
const READY_CALLBACK = '__googleMapsReady';

let mapsApiPromise: Promise<GoogleMapsApi> | null = null;

/**
 * Loads the Maps JavaScript API with `loading=async`, the pattern Google now
 * recommends. In that mode the script's `onload` fires before the API has
 * bootstrapped, so readiness comes from the `callback` query parameter; the
 * libraries are then pulled in through `importLibrary`, which is the only
 * guaranteed way to get the constructors.
 */
function loadMapsApi(): Promise<GoogleMapsApi> {
    if (mapsApiPromise) return mapsApiPromise;

    mapsApiPromise = new Promise<void>((resolve, reject) => {
        if (window.google?.maps?.importLibrary) {
            resolve();
            return;
        }
        window[READY_CALLBACK] = () => resolve();
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&loading=async&callback=${READY_CALLBACK}`;
        script.async = true;
        script.onerror = () => reject(new Error('Failed to load Google Maps'));
        document.head.appendChild(script);
    }).then(async (): Promise<GoogleMapsApi> => {
        const maps = window.google?.maps;
        if (!maps) throw new Error('Google Maps API did not initialise');
        await Promise.all([
            maps.importLibrary('maps'),
            maps.importLibrary('marker'),
        ]);
        return maps;
    });

    return mapsApiPromise;
}

export default function Map({ lat, lng }: { lat: number; lng: number }) {
    const mapRef = useRef<HTMLDivElement>(null);
    const hasCoords = Number.isFinite(lat) && Number.isFinite(lng);

    useEffect(() => {
        if (!API_KEY || !mapRef.current || !hasCoords) return;

        const center = { lat, lng };
        let cancelled = false;

        loadMapsApi()
            .then((maps) => {
                if (cancelled || !mapRef.current) return;
                const map = new maps.Map(mapRef.current, {
                    center,
                    zoom: 15,
                    disableDefaultUI: false,
                    mapId: MAP_ID,
                });
                new maps.Marker({ position: center, map });
            })
            .catch((error: unknown) => console.error(error));

        return () => {
            cancelled = true;
        };
    }, [lat, lng, hasCoords]);

    if (!hasCoords) return null;

    return (
        <section className="map">
            <div className="w-full">
                <div
                    ref={mapRef}
                    className="h-120 w-full overflow-hidden rounded-lg"
                />
            </div>
        </section>
    );
}
