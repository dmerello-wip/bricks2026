<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width,initial-scale=1"
        />
        <title>Iscrizione ricevuta</title>
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
                padding: 40px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            }
            .header {
                text-align: center;
                margin-bottom: 28px;
                color: #111;
                font-weight: 600;
                font-size: 24px;
            }
            h1 {
                font-size: 20px;
                margin: 0 0 16px;
                color: #111;
            }
            p {
                font-size: 16px;
                line-height: 1.5;
                color: #333;
                margin: 0 0 16px;
            }
            .band-name {
                font-weight: 600;
                color: #111;
            }
            .footer {
                margin-top: 28px;
                padding-top: 18px;
                border-top: 1px solid #eee;
                color: #666;
                font-size: 13px;
            }
            .footer p {
                margin: 4px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="header">{{ config('app.name') }}</div>

                <h1>Iscrizione ricevuta</h1>
                <p>
                    Ciao
                    <span class="band-name">
                        {{ $subscription->referente ?? $subscription->band }}
                    </span>
                    ,
                </p>
                <p>
                    Abbiamo ricevuto la tua iscrizione correttamente. TI
                    invieremo aggiornamenti sulla partecipazione al Bricks!
                </p>

                <div class="footer">
                    <p>Dati iscrizione:</p>
                    <p>
                        <strong>Band:</strong>
                        {{ $subscription->band }}
                    </p>
                    <p>
                        <strong>Numero componenti:</strong>
                        {{ $subscription->nr_componenti ?? 'N/A' }}
                    </p>
                    <p>
                        <strong>Genere:</strong>
                        {{ $subscription->genere ?? 'N/A' }}
                    </p>
                </div>

                <div
                    class="footer"
                    style="border-top: none; margin-top: 28px"
                >
                    <p style="margin: 0">
                        {{ config('app.name') }}
                        <br />
                        Bricks Music Festival
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
