<?php

return [
    'subscribe' => [
        'pending' => 'Abbiamo ricevuto la tua richiesta. La conferma finale e ancora necessaria tramite il link di verifica.',
        'success' => 'La tua iscrizione e ora attiva.',
    ],
    'validation' => [
        'email_required' => 'Inserisci un indirizzo email.',
        'email_invalid' => 'Inserisci un indirizzo email valido.',
        'privacy_accepted' => 'Devi accettare la politica sulla privacy e le comunicazioni commerciali.',
        'already_subscribed' => 'Questo iscritto esiste gia.',
        'throttled' => 'Sono state inviate troppe richieste. Attendi un momento prima di riprovare.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Iscrizione confermata',
            'message' => 'La tua iscrizione alla newsletter e stata attivata correttamente.',
        ],
        'confirmation_invalid' => [
            'title' => 'Link di conferma non valido',
            'message' => 'Il link di conferma non e valido o e scaduto. Richiedi una nuova iscrizione.',
        ],
        'unsubscribed' => [
            'title' => 'Disiscrizione completata',
            'message' => 'La tua email e stata rimossa dalla newsletter correttamente.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Link di disiscrizione non valido',
            'message' => 'Il link di disiscrizione non e valido o non e piu disponibile.',
        ],
    ],
    'actions' => [
        'back_home' => 'Torna alla home',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Iscriviti a RadioChi',
        'description' => 'Ricevi novita e autorizza l invio di comunicazioni commerciali di RadioChi.',
        'email_label' => 'Email',
        'email_placeholder' => 'tu@dominio.com',
        'privacy_lead' => 'Ho letto e accetto ',
        'privacy_link' => 'la Politica sulla Privacy',
        'privacy_tail' => ' e l invio di comunicazioni commerciali di RadioChi.',
        'submit_idle' => 'Voglio iscrivermi',
        'submit_loading' => 'Invio richiesta...',
        'legal' => [
            'responsible_label' => 'Titolare',
            'responsible_value' => ':brand',
            'purpose_label' => 'Finalita',
            'purpose_value' => 'Gestire la tua iscrizione attiva e inviarti comunicazioni commerciali e novita di :brand.',
            'unsubscribe_label' => 'Disiscrizione',
            'unsubscribe_value' => 'Puoi disiscriverti in qualsiasi momento da qualsiasi email della newsletter.',
            'double_opt_in_label' => 'Attivazione',
            'double_opt_in_value' => 'La tua iscrizione si attiva nel momento in cui invii il modulo con il tuo consenso esplicito.',
        ],
        'modal' => [
            'duplicate_title' => 'Iscritto gia registrato',
            'duplicate_body' => 'Questa email e gia presente come iscritto attivo. Non verra duplicata.',
            'close' => 'Chiudi',
            'continue' => 'Capito',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Conferma la tua iscrizione alla newsletter',
            'headline' => 'Conferma la tua iscrizione',
            'intro' => 'Ci serve una conferma finale prima di attivare la tua iscrizione.',
            'body' => 'Abbiamo ricevuto una richiesta di iscrizione per :email. Premi il pulsante per confermare l iscrizione.',
            'cta' => 'Conferma iscrizione',
            'expiry' => 'Questo link di conferma restera disponibile per 7 giorni.',
            'ignore' => 'Se non hai richiesto questa iscrizione, puoi ignorare questa email senza fare nulla.',
        ],
        'campaign' => [
            'intro' => 'Comunicazione inviata agli iscritti attivi della newsletter.',
            'notice' => 'Ricevi questa email perche la tua iscrizione e attiva e puoi annullarla in qualsiasi momento dal link qui sotto.',
            'unsubscribe_helper' => 'Se non vuoi piu ricevere campagne future, usa questo link diretto di disiscrizione:',
            'unsubscribe_cta' => 'Annulla l iscrizione alla newsletter',
        ],
        'footer' => [
            'responsible_label' => 'Titolare del trattamento',
            'contact_label' => 'Email di contatto',
            'purpose_label' => 'Finalita',
            'purpose_value' => 'Gestire l iscrizione attiva e inviare comunicazioni newsletter agli iscritti attivi.',
            'privacy_label' => 'Consulta la politica sulla privacy',
            'unsubscribe_label' => 'Annulla l iscrizione alla newsletter',
        ],
    ],
];
