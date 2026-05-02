# Contesto Backend

- Crea un nuovo modulo Twill: Subscriptions
- Non deve essere Seo compliant, verranno create Subscriptions dal frontend per il solo uso nell'admin di Twill, i dati non verranno esposti
- I campi da cui è strutturato sono:
    - band
    - nr_componenti
    - eta_media
    - citta
    - genere
    - durata
    - referente
    - telefono
    - email
    - video_file_path
    - video_link
    - privacy
    - evento
    - data_iscrizione
- Utilizza le modalità gestite da Twill per il file del video, un campo browser File
- Oltre che dall'admin, anche da un form pubblico nel frontend React del sito si potranno ricevere salvataggi su Subscriptions
- Crea un Block di Twill nominato "Subscription Form" che, quando utilizzato in una Page o altro modulo, farà comparire il Form di creazione di una Subscription per gli utenti di frontend
- il Block di Twill "Subscription Form" presenterà i seguenti campi:
    - Titolo
    - Sottotitolo
    - Nome Evento

# Contesto Frontend

- sei in uno stack Laravel-Inertia, utilizza le best practice di gestione di form di Inertia
- Nel frontend React sviluppa controlli di campo con i campi e attributi nativi html5.
- Per creare i campi del form e gli avvisi sull'esito della compilazione utilizza componenti nativi shadcn, se necessario installali
- per la disposizione dei campi in layout fai riferimento al file ./form.png

## Vista a frontend del form di iscrizione

Vengono esposti prima del form:

- Titolo -> valore impostato nel Block "Subscription Form"
- Sottotitolo -> valore impostato nel Block "Subscription Form"

A seguito si presenta il form da compilare per gli utenti:

### primo fieldset

**Titolo** : Informazioni della band
**Campi** :

- name: band
  label: Nome della Band \*
  testo di suggerimento: Non avete ancora un nome? è il momento di trovarlo!
  tipo di campo: testo
  controllo: obbligatorio

- name: nr_componenti
  label: Numero dei componenti \*
  testo di suggerimento: Basta un duo per iniziare una band!
  tipo di campo: numero
  controllo: obbligatorio, maggiore di 1

- name: eta_media
  label: Età media \*
  testo di suggerimento: Somma le età dei componenti della band e dividi per il numero dei componenti della band. L'età media deve essere inferiore ai 26 anni.
  tipo di campo: testo
  controllo: obbligatorio, maggiore di 13, minore di 26

- name: citta
  label: Città \*
  testo di suggerimento: La città in cui risiede il maggior numero dei componenti della band
  tipo di campo: testo libero
  controllo: obbligatorio

- name: genere
  label: Genere musicale \*
  testo di suggerimento: Come definireste il genere di musica che suonate
  tipo di campo: testo
  controllo: obbligatorio

- name: durata
  label: Durata del tuo live set in minuti \*
  testo di suggerimento: In base al tuo repertorio, qual'è la durata media del tuo live set in minuti?
  tipo di campo: numero
  controllo: obbligatorio, numerico

### secondo fieldset

**Titolo** : Contatti del referente
**descrizione fieldset** : Vi contatteremo in caso la vostra candidatura vada a buon fine. In caso siano presenti minorenni nella Band indicare il nominativo di un adulto di riferimento.

**campi** :

- name: referente
  label: Nome e Cognome referente \*
  tipo di campo: testo
  controllo: obbligatorio
- name: telefono
  label: Telefono referente \*
  tipo di campo: testo
  controllo: obbligatorio, formato di numero telefono
- name: email
  label: Email referente \*
  tipo di campo: email
  controllo: obbligatorio, forma di email valida

## terzo fieldset

**Titolo** : Video del gruppo
**descrizione fieldset** : Inviateci un video contenente un unico brano originale – edito o inedito – o anche una cover.
Potete caricare il link al video già presente su una piattaforma di streaming (youtube, vimeo, facebook, Instagram, Soundcloud, ...) o scaricabile da una piattaforma di storage (Google Drive, DropBox, WeTransfer, ...)

**campi** :

- name: video_file_path
  label: Carica un video dal tuo PC
  tipo di campo: file
  controllo: obbligatorietà alternativa con "Link al video", non oltre ai 100mb
- name: video_link
  label: Link al video
  testo di suggerimento: es. https://we.tl/t-wU4o9bUBny
  tipo di campo: testo
  controllo: obbligatorietà alternativa con "Carica un video dal tuo PC", formato di link valido

### terzo fieldset

**Titolo** : Il tuo consenso
**descrizione fieldset** : Il trattamento dei dati personali è regolato dal Regolamento UE 2016/679 (GDPR). I dati forniti saranno trattati esclusivamente per le finalità connesse alla partecipazione al Bricks Music Festival. Leggi la privacy policy.

**campi** :

- name: privacy
  label: Ho letto e accetto le condizioni di privacy policy. Fornisco il consenso.
  testo di suggerimento: Ci siamo
  tipo di campo: checkbox
  controllo: obbligatorio

### quarto fieldset

**Titolo** : Ci siamo
**descrizione fieldset** : Ok, clicca su "Invia" per inviare la tua candidatura.
Riceverai una email di conferma dell'avvenuta candidatura e se verrete selezionati contatteremo referente segnalato.

**campi** :

- label: Invia
  tipo di campo: submit
- name: evento
  tipo di campo: hidden
  valore preimpostato: secondo il valore stabilito nel Block "Subscription Form"
- name: data_iscrizione
  tipo di campo: hidden
  valore preimpostato: datetime
