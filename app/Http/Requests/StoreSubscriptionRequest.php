<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'band' => ['required', 'string', 'max:255'],
            'nr_componenti' => ['required', 'integer', 'min:2'],
            'eta_media' => ['required', 'numeric', 'gt:13', 'lt:26'],
            'citta' => ['required', 'string', 'max:255'],
            'genere' => ['required', 'string', 'max:255'],
            'durata' => ['required', 'integer', 'min:1'],
            'referente' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^[+0-9\s\-()]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'video_file_path' => ['required_without:video_link', 'nullable', 'file', 'max:102400'],
            'video_link' => ['required_without:video_file_path', 'nullable', 'url', 'max:2048'],
            'privacy' => ['required', 'accepted'],
            'evento' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'band.required' => 'Il nome della band è obbligatorio.',
            'nr_componenti.required' => 'Il numero dei componenti è obbligatorio.',
            'nr_componenti.min' => 'La band deve avere almeno 2 componenti.',
            'eta_media.required' => 'L\'età media è obbligatoria.',
            'eta_media.gt' => 'L\'età media deve essere maggiore di 13 anni.',
            'eta_media.lt' => 'L\'età media deve essere inferiore ai 26 anni.',
            'citta.required' => 'La città è obbligatoria.',
            'genere.required' => 'Il genere musicale è obbligatorio.',
            'durata.required' => 'La durata del live set è obbligatoria.',
            'referente.required' => 'Il nome del referente è obbligatorio.',
            'telefono.required' => 'Il telefono del referente è obbligatorio.',
            'telefono.regex' => 'Il formato del telefono non è valido.',
            'email.required' => 'L\'email del referente è obbligatoria.',
            'email.email' => 'L\'email del referente non è valida.',
            'video_file_path.required_without' => 'Carica un video oppure inserisci un link.',
            'video_file_path.file' => 'Il file caricato non è valido.',
            'video_file_path.max' => 'Il file video non può superare i 100MB.',
            'video_link.required_without' => 'Inserisci un link oppure carica un video.',
            'video_link.url' => 'Il link al video non è in un formato valido.',
            'privacy.required' => 'Devi accettare la privacy policy.',
            'privacy.accepted' => 'Devi accettare la privacy policy.',
            'evento.required' => 'Il riferimento all\'evento è mancante.',
        ];
    }
}
