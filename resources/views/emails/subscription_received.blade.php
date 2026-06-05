<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width,initial-scale=1"
        />
        <title>Nuova Iscrizione Band</title>
        <style>
            body {
                font-family:
                    -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                    'Helvetica Neue', Arial, sans-serif;
                background: #f7f7f7;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 700px;
                margin: 0 auto;
            }
            .card {
                background: #ffffff;
                border-radius: 6px;
                padding: 28px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            }
            h1 {
                font-size: 20px;
                margin: 0 0 16px;
            }
            .field {
                margin: 8px 0;
            }
            .label {
                font-weight: 600;
                color: #333;
                display: inline-block;
                width: 120px;
            }
            .value {
                color: #111;
            }
            .button {
                display: inline-block;
                margin-top: 18px;
                padding: 10px 16px;
                background: #111827;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
            }
            .footer {
                margin-top: 18px;
                color: #666;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div
                style="
                    text-align: center;
                    margin-bottom: 18px;
                    color: #111;
                    font-weight: 600;
                "
            >
                {{ config('app.name') }}
            </div>
            <div class="card">
                <h1>Nuova Iscrizione Band</h1>

                <div class="field">
                    <span class="label">Band:</span>
                    <span class="value">
                        {{ $subscription->band ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Members:</span>
                    <span class="value">
                        {{ $subscription->nr_componenti ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Average age:</span>
                    <span class="value">
                        {{ $subscription->eta_media ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">City:</span>
                    <span class="value">
                        {{ $subscription->citta ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Genre:</span>
                    <span class="value">
                        {{ $subscription->genere ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Duration:</span>
                    <span class="value">
                        {{ $subscription->durata ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Contact:</span>
                    <span class="value">
                        {{ $subscription->referente ?? 'N/A' }} —
                        {{ $subscription->telefono ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Email:</span>
                    <span class="value">
                        {{ $subscription->email ?? 'N/A' }}
                    </span>
                </div>
                <div class="field">
                    <span class="label">Video link:</span>
                    <span class="value">
                        @if ($subscription->video_link)
                            <a href="{{ $subscription->video_link }}">
                                {{ $subscription->video_link }}
                            </a>
                        @else
                                N/A
                        @endif
                    </span>
                </div>
                <div class="field">
                    <span class="label">Event ID:</span>
                    <span class="value">
                        {{ $subscription->event_id ?? 'N/A' }}
                    </span>
                </div>

                <a
                    class="button"
                    href="{{ url('/' . app()->getLocale() . '/admin') }}"
                >
                    Open Admin
                </a>

                <div class="footer">
                    Thanks,
                    <br />
                    {{ config('app.name') }}
                </div>
            </div>
        </div>
    </body>
</html>
