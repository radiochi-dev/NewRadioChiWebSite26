<?php

return [
    'subscribe' => [
        'pending' => 'Hem rebut la teva sollicitud. Encara falta la confirmacio final des de l enllac de verificacio.',
        'success' => 'La teva subscripcio ha quedat activada correctament.',
    ],
    'validation' => [
        'email_required' => 'Introdueix un correu electronic.',
        'email_invalid' => 'Introdueix un correu electronic valid.',
        'privacy_accepted' => 'Has d acceptar la politica de privacitat i les comunicacions comercials.',
        'already_subscribed' => 'Aquest subscriptor ja existeix.',
        'throttled' => 'Has enviat massa sollicituds. Espera un moment abans de tornar-ho a provar.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Subscripcio confirmada',
            'message' => 'La teva subscripcio a la newsletter ha quedat activada correctament.',
        ],
        'confirmation_invalid' => [
            'title' => 'Enllac de confirmacio no valid',
            'message' => 'L enllac de confirmacio no es valid o ha caducat. Sollicita una nova subscripcio.',
        ],
        'unsubscribed' => [
            'title' => 'Baixa completada',
            'message' => 'El teu correu ha quedat donat de baixa de la newsletter correctament.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Enllac de baixa no valid',
            'message' => 'L enllac de baixa no es valid o ja no esta disponible.',
        ],
    ],
    'actions' => [
        'back_home' => 'Tornar a l inici',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Subscriu-te a RadioChi',
        'description' => '',
        'email_label' => 'Correu electronic',
        'email_placeholder' => 'tu@domini.com',
        'privacy_lead' => 'He llegit i accepto ',
        'privacy_link' => 'la Politica de Privacitat',
        'privacy_tail' => ' i l enviament de comunicacions comercials de RadioChi.',
        'submit_idle' => 'Vull subscriure m',
        'submit_loading' => 'Enviant sollicitud...',
        'legal' => [
            'responsible_label' => 'Responsable',
            'responsible_value' => ':brand',
            'purpose_label' => 'Finalitat',
            'purpose_value' => 'Gestionar la teva subscripcio activa i enviar-te comunicacions comercials i novetats de :brand.',
            'unsubscribe_label' => 'Baixa',
            'unsubscribe_value' => 'Et pots donar de baixa en qualsevol moment des de qualsevol correu de newsletter.',
            'double_opt_in_label' => 'Activacio',
            'double_opt_in_value' => 'L alta queda activada en el moment d enviar el formulari amb el teu consentiment exprés.',
        ],
        'modal' => [
            'duplicate_title' => 'Subscriptor ja registrat',
            'duplicate_body' => 'Aquest correu ja figura com a subscriptor actiu. No es duplicara.',
            'close' => 'Tancar',
            'continue' => 'Entes',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Confirma la teva subscripcio a la newsletter',
            'headline' => 'Confirma la teva subscripcio',
            'intro' => 'Necessitem una confirmacio final abans d activar la teva alta.',
            'body' => 'Hem rebut una sollicitud de subscripcio per a :email. Prem el boto per confirmar l alta.',
            'cta' => 'Confirmar subscripcio',
            'expiry' => 'Aquest enllac de confirmacio estara disponible durant 7 dies.',
            'ignore' => 'Si no has sollicitat aquesta subscripcio, pots ignorar aquest correu sense fer cap accio.',
        ],
        'campaign' => [
            'intro' => 'Comunicacio enviada a subscriptors actius de la newsletter.',
            'notice' => 'Reps aquest correu perque la teva subscripcio esta activa i et pots donar de baixa en qualsevol moment des de l enllac inferior.',
            'unsubscribe_helper' => 'Si ja no vols rebre futures campanyes, fes servir aquest enllac directe de baixa:',
            'unsubscribe_cta' => 'Donar-me de baixa de la newsletter',
        ],
        'footer' => [
            'responsible_label' => 'Responsable del tractament',
            'contact_label' => 'Correu de contacte',
            'purpose_label' => 'Finalitat',
            'purpose_value' => 'Gestionar la subscripcio activa i enviar comunicacions de newsletter a subscriptors actius.',
            'privacy_label' => 'Consultar la politica de privacitat',
            'unsubscribe_label' => 'Donar-se de baixa de la newsletter',
        ],
    ],
];
