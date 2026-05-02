import { useForm } from '@inertiajs/react';
import { CheckCircle2Icon } from 'lucide-react';
import { type ChangeEvent, type FormEvent, useState } from 'react';
import InputError from '@/components/InputError';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/Alert';
import { Button } from '@/components/ui/Button';
import { Checkbox } from '@/components/ui/Checkbox';
import { Input } from '@/components/ui/Input';
import { Label } from '@/components/ui/Label';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import subscriptions from '@/routes/subscriptions';
import Title from '../editorial/atom/Title';
import Subtitle from '../editorial/atom/Subtitle';

interface SubscriptionFormData {
    band: string;
    nr_componenti: string;
    eta_media: string;
    citta: string;
    genere: string;
    durata: string;
    referente: string;
    telefono: string;
    email: string;
    video_file_path: File | null;
    video_link: string;
    privacy: boolean;
    evento: string;
    data_iscrizione: string;
    [key: string]: string | boolean | File | null;
}

const fieldsetClasses = 'border-b border-primary pb-8 pt-4';

const legendClasses =
    'mb-6 text-xl font-serif font-bold uppercase tracking-wide text-foreground';

const helperClasses = 'mt-1 text-xs text-muted-foreground';

const fieldsetDescriptionClasses = 'mb-6';
const fieldClasses = 'grid gap-3';
export default function SubscriptionForm({ block }: { block: Block }) {
    if (!block) return null;

    const title = block.content?.title as string | undefined;
    const subtitle = block.content?.subtitle as string | undefined;
    const eventName = (block.content?.event_name as string | undefined) ?? '';

    const [clientError, setClientError] = useState<string | null>(null);

    const {
        data,
        setData,
        post,
        processing,
        errors,
        progress,
        reset,
        clearErrors,
        recentlySuccessful,
    } = useForm<SubscriptionFormData>({
        band: '',
        nr_componenti: '',
        eta_media: '',
        citta: '',
        genere: '',
        durata: '',
        referente: '',
        telefono: '',
        email: '',
        video_file_path: null,
        video_link: '',
        privacy: false,
        evento: eventName,
        data_iscrizione: '',
    });

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setClientError(null);
        clearErrors();

        if (!data.video_file_path && !data.video_link.trim()) {
            setClientError(
                'Carica un video dal tuo PC oppure inserisci un link al video.',
            );
            return;
        }

        setData('data_iscrizione', new Date().toISOString());

        post(subscriptions.store().url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setData('evento', eventName);
            },
        });
    };

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        setData('video_file_path', file);
    };

    return (
        <section className="block-subscription-form relative w-full py-16">
            <div className="container mx-auto max-w-5xl px-6">
                {(title || subtitle) && (
                    <header className="mb-12 flex flex-col gap-2 text-center md:text-left">
                        {title && (
                            <Title
                                content={title}
                                seoTag={'div'}
                            />
                        )}
                        {subtitle && (
                            <Subtitle
                                content={subtitle}
                                seoTag={'div'}
                            />
                        )}
                    </header>
                )}

                {recentlySuccessful ? (
                    <Alert className="mb-8">
                        <CheckCircle2Icon />
                        <AlertTitle>Iscrizione inviata</AlertTitle>
                        <AlertDescription>
                            Grazie! Riceverai una email di conferma
                            dell'avvenuta candidatura.
                        </AlertDescription>
                    </Alert>
                ) : (
                    <form
                        onSubmit={handleSubmit}
                        noValidate
                        encType="multipart/form-data"
                        className="space-y-12"
                    >
                        {/* Fieldset 1 - Informazioni della Band */}
                        <fieldset className={fieldsetClasses}>
                            <legend className={legendClasses}>
                                <span className="text-secondary">1. </span>
                                Informazioni della Band
                            </legend>

                            <div className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <div className={fieldClasses}>
                                    <Label htmlFor="band">
                                        Nome della Band *
                                    </Label>
                                    <Input
                                        id="band"
                                        name="band"
                                        type="text"
                                        required
                                        value={data.band}
                                        onChange={(e) =>
                                            setData('band', e.target.value)
                                        }
                                        aria-invalid={Boolean(errors.band)}
                                    />
                                    <p className={helperClasses}>
                                        Non avete ancora un nome? è il momento
                                        di trovarlo!
                                    </p>
                                    <InputError message={errors.band} />
                                </div>

                                <div className={fieldClasses}>
                                    <Label htmlFor="citta">Città *</Label>
                                    <Input
                                        id="citta"
                                        name="citta"
                                        type="text"
                                        required
                                        value={data.citta}
                                        onChange={(e) =>
                                            setData('citta', e.target.value)
                                        }
                                        aria-invalid={Boolean(errors.citta)}
                                    />
                                    <p className={helperClasses}>
                                        La città in cui risiede il maggior
                                        numero dei componenti della band
                                    </p>
                                    <InputError message={errors.citta} />
                                </div>

                                <div className={fieldClasses}>
                                    <Label htmlFor="nr_componenti">
                                        Numero dei componenti *
                                    </Label>
                                    <Input
                                        id="nr_componenti"
                                        name="nr_componenti"
                                        type="number"
                                        min={2}
                                        step={1}
                                        required
                                        value={data.nr_componenti}
                                        onChange={(e) =>
                                            setData(
                                                'nr_componenti',
                                                e.target.value,
                                            )
                                        }
                                        aria-invalid={Boolean(
                                            errors.nr_componenti,
                                        )}
                                    />
                                    <p className={helperClasses}>
                                        Basta un duo per iniziare una band!
                                    </p>
                                    <InputError
                                        message={errors.nr_componenti}
                                    />
                                </div>

                                <div className={fieldClasses}>
                                    <Label htmlFor="genere">
                                        Genere musicale *
                                    </Label>
                                    <Input
                                        id="genere"
                                        name="genere"
                                        type="text"
                                        required
                                        value={data.genere}
                                        onChange={(e) =>
                                            setData('genere', e.target.value)
                                        }
                                        aria-invalid={Boolean(errors.genere)}
                                    />
                                    <p className={helperClasses}>
                                        Come definireste il genere di musica che
                                        suonate
                                    </p>
                                    <InputError message={errors.genere} />
                                </div>

                                <div className={fieldClasses}>
                                    <Label htmlFor="eta_media">
                                        Età media *
                                    </Label>
                                    <Input
                                        id="eta_media"
                                        name="eta_media"
                                        type="number"
                                        step={0.1}
                                        min={13.1}
                                        max={25.9}
                                        required
                                        value={data.eta_media}
                                        onChange={(e) =>
                                            setData('eta_media', e.target.value)
                                        }
                                        aria-invalid={Boolean(errors.eta_media)}
                                    />
                                    <p className={helperClasses}>
                                        Somma le età dei componenti della band e
                                        dividi per il numero dei componenti
                                        della band. L'età media deve essere
                                        inferiore ai 26 anni.
                                    </p>
                                    <InputError message={errors.eta_media} />
                                </div>

                                <div className={fieldClasses}>
                                    <Label htmlFor="durata">
                                        Durata del tuo live set in minuti *
                                    </Label>
                                    <Input
                                        id="durata"
                                        name="durata"
                                        type="number"
                                        min={1}
                                        step={1}
                                        required
                                        value={data.durata}
                                        onChange={(e) =>
                                            setData('durata', e.target.value)
                                        }
                                        aria-invalid={Boolean(errors.durata)}
                                    />
                                    <p className={helperClasses}>
                                        In base al tuo repertorio, qual'è la
                                        durata media del tuo live set in minuti?
                                    </p>
                                    <InputError message={errors.durata} />
                                </div>
                            </div>
                        </fieldset>

                        {/* Fieldset 2 - Contatti del referente */}
                        <fieldset className={fieldsetClasses}>
                            <legend className={legendClasses}>
                                <span className="text-secondary">2. </span>
                                Contatti del referente
                            </legend>

                            <div className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <p className={fieldsetDescriptionClasses}>
                                    Vi contatteremo in caso la vostra
                                    candidatura vada a buon fine.{' '}
                                    <strong>
                                        In caso siano presenti minorenni nella
                                        Band indicare il nominativo di un adulto
                                        di riferimento.
                                    </strong>
                                </p>
                                <div className="grid grid-cols-1 gap-y-6">
                                    <div className={fieldClasses}>
                                        <Label htmlFor="referente">
                                            Nome e Cognome referente *
                                        </Label>
                                        <Input
                                            id="referente"
                                            name="referente"
                                            type="text"
                                            required
                                            autoComplete="name"
                                            value={data.referente}
                                            onChange={(e) =>
                                                setData(
                                                    'referente',
                                                    e.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                errors.referente,
                                            )}
                                        />
                                        <InputError
                                            message={errors.referente}
                                        />
                                    </div>

                                    <div className={fieldClasses}>
                                        <Label htmlFor="telefono">
                                            Telefono referente *
                                        </Label>
                                        <Input
                                            id="telefono"
                                            name="telefono"
                                            type="tel"
                                            required
                                            autoComplete="tel"
                                            pattern="[+0-9\s\-\(\)]+"
                                            value={data.telefono}
                                            onChange={(e) =>
                                                setData(
                                                    'telefono',
                                                    e.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                errors.telefono,
                                            )}
                                        />
                                        <InputError message={errors.telefono} />
                                    </div>

                                    <div className={fieldClasses}>
                                        <Label htmlFor="email">
                                            Email referente *
                                        </Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            required
                                            autoComplete="email"
                                            value={data.email}
                                            onChange={(e) =>
                                                setData('email', e.target.value)
                                            }
                                            aria-invalid={Boolean(errors.email)}
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {/* Fieldset 3 - Video del gruppo */}
                        <fieldset className={fieldsetClasses}>
                            <legend className={legendClasses}>
                                <span className="text-secondary">3. </span>
                                Video del gruppo
                            </legend>

                            <div className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <p className={fieldsetDescriptionClasses}>
                                    Inviateci un video contenente un unico brano
                                    originale – edito o inedito – o anche una
                                    cover. Potete caricare il link al video già
                                    presente su una piattaforma di streaming
                                    (youtube, vimeo, facebook, Instagram,
                                    Soundcloud, ...) o scaricabile da una
                                    piattaforma di storage (Google Drive,
                                    DropBox, WeTransfer, ...)
                                </p>

                                <div className="grid grid-cols-1 gap-y-6">
                                    <div className={fieldClasses}>
                                        <Label htmlFor="video_file_path">
                                            Carica un video dal tuo pc
                                        </Label>
                                        <Input
                                            id="video_file_path"
                                            name="video_file_path"
                                            type="file"
                                            accept="video/*"
                                            onChange={handleFileChange}
                                            aria-invalid={Boolean(
                                                errors.video_file_path,
                                            )}
                                            className="h-auto py-2"
                                        />
                                        <p className={helperClasses}>
                                            Dimensione massima 100MB.
                                        </p>
                                        <InputError
                                            message={errors.video_file_path}
                                        />
                                    </div>

                                    <div className={fieldClasses}>
                                        <Label htmlFor="video_link">
                                            Link al video
                                        </Label>
                                        <Input
                                            id="video_link"
                                            name="video_link"
                                            type="url"
                                            inputMode="url"
                                            placeholder="es. https://we.tl/t-wU4o9bUBny"
                                            value={data.video_link}
                                            onChange={(e) =>
                                                setData(
                                                    'video_link',
                                                    e.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                errors.video_link,
                                            )}
                                        />
                                        <p className={helperClasses}>
                                            es. https://we.tl/t-wU4o9bUBny
                                        </p>
                                        <InputError
                                            message={errors.video_link}
                                        />
                                    </div>
                                </div>
                            </div>

                            {progress && (
                                <div className="mt-4">
                                    <progress
                                        value={progress.percentage}
                                        max={100}
                                        className="h-2 w-full overflow-hidden rounded-full"
                                    >
                                        {progress.percentage}%
                                    </progress>
                                    <p className={cn(helperClasses, 'mt-1')}>
                                        Caricamento file: {progress.percentage}%
                                    </p>
                                </div>
                            )}
                        </fieldset>

                        {/* Fieldset 4 - Il tuo consenso */}
                        <fieldset className={fieldsetClasses}>
                            <legend className={legendClasses}>
                                <span className="text-secondary">4. </span>
                                Il tuo consenso
                            </legend>

                            <div className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <p className={fieldsetDescriptionClasses}>
                                    Il trattamento dei dati personali è regolato
                                    dal Regolamento UE 2016/679 (GDPR). I dati
                                    forniti saranno trattati esclusivamente per
                                    le finalità connesse alla partecipazione al
                                    Bricks Music Festival.{' '}
                                    <a
                                        href="/privacy-policy"
                                        className="text-primary underline underline-offset-4 hover:no-underline"
                                    >
                                        Leggi la privacy policy
                                    </a>
                                    .
                                </p>

                                <div className="flex items-start gap-3">
                                    <Checkbox
                                        id="privacy"
                                        name="privacy"
                                        checked={data.privacy}
                                        onCheckedChange={(checked) =>
                                            setData('privacy', checked === true)
                                        }
                                        required
                                        aria-invalid={Boolean(errors.privacy)}
                                    />
                                    <div className="grid gap-1">
                                        <Label
                                            htmlFor="privacy"
                                            className="leading-snug"
                                        >
                                            Ho letto e accetto le condizioni di
                                            privacy policy. Fornisco il
                                            consenso. *
                                        </Label>
                                        <InputError message={errors.privacy} />
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {/* Fieldset 5 - Ci siamo */}
                        <fieldset className={fieldsetClasses}>
                            <legend className={legendClasses}>
                                <span className="text-secondary">5. </span>
                                Ci siamo!
                            </legend>

                            <div className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <div>
                                    {clientError && (
                                        <Alert
                                            variant="destructive"
                                            className="mb-4"
                                        >
                                            <AlertDescription>
                                                {clientError}
                                            </AlertDescription>
                                        </Alert>
                                    )}
                                    <p className={fieldsetDescriptionClasses}>
                                        Ok, clicca su "Invia" per inviare la tua
                                        candidatura. Riceverai una email di
                                        conferma dell'avvenuta candidatura e se
                                        verrete selezionati contatteremo
                                        referente segnalato.
                                    </p>
                                </div>
                                <input
                                    type="hidden"
                                    name="evento"
                                    value={data.evento}
                                />
                                <input
                                    type="hidden"
                                    name="data_iscrizione"
                                    value={data.data_iscrizione}
                                />

                                <InputError
                                    message={errors.evento}
                                    className="mb-4"
                                />

                                <div className="flex justify-end">
                                    <Button
                                        type="submit"
                                        size="lg"
                                        disabled={processing}
                                        className="px-10"
                                    >
                                        {processing
                                            ? 'Invio in corso...'
                                            : 'Invia la sottoscrizione'}
                                    </Button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                )}
            </div>
        </section>
    );
}
