
# TODO / TO CHECK

## Maintainance

- task make per import db


### Backend: Localizzazione e slug localizzati

Verificare la localizzazione di contenuti e slug:
- non è implementata la localizzazione degli slug. Nel vecchio starter era configurata con commenti su web.php
- mentre i menu sono forniti dal backend al frontend con la lingua corrente, i block forniti dal PageController vengono interpolati da un BlockService che con App->getlocale filtra i contenuti in lingua corrente prima di darli al frontend, non so (non penso) che sia il modo corretto.
- manca la gestione stringhe i18n


## Backend: 301 redirections

- valutare l'utilizzo di https://github.com/area17/twill-capsule-redirections in base al normale utilizzo che ne facciamo (redirezioni seo manuali -> redirezioni programmatiche)


## devops

tutto da costruire, rimuovere se inutili i .github/workflows

## Frontend: Hero: gestione lato tailwind delle condizioni di layout

Da rivalutare l'attuale logica:

- Ora si usa tailwind purissimo con il metodo delle classi condizionali "[group](https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-parent-state)":
- si potrebbe valutare di usare [cva](https://cva.style/docs): che è usatissimo in shadcCn per fare queste cose