## 1. nuovo modulo Twill Events

- crea un nuovo modulo twill "Events" con i seguenti campi:
    - Title
    - data (datepicker in admin)
    - luogo
      usa: use A17\Twill\Services\Forms\Fields\Map as MapField; e un MapField nella getForm
    - usa le logiche seo della skill twill-module-creation
    - la route di frontend sarà /eventi/{slug}

## 2. relaziona il campo event_name

- sostituisci il tipo del campo event_name di SubscriptionForm utilizzando un campo browser Twill per relazionare un "Event"

## 3. relazione Event / Subscription

- il modulo Subscription avrà una relazione con il campo Event tramite campo Browser che sostituirà il campo Input "evento" di Subscription
- da admin creare e modificare una Subscription userà questo nuovo campo per selezionare un Event relazionato alla Subscription

## 3. scambio del parametro

- in SubscriptionForm.tsx valorizza il campo hidden "evento" con un valore corretto di event_name per poter salvare la relazione nel modulo Subscription al salvataggio
