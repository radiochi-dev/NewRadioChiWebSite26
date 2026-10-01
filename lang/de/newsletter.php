<?php

return [
    'subscribe' => [
        'pending' => 'Wir haben deine Anfrage erhalten. Die finale Bestatigung uber den Verifizierungslink ist noch erforderlich.',
        'success' => 'Dein Abonnement ist jetzt aktiv.',
    ],
    'validation' => [
        'email_required' => 'Bitte gib eine E Mail Adresse ein.',
        'email_invalid' => 'Bitte gib eine gultige E Mail Adresse ein.',
        'privacy_accepted' => 'Du musst der Datenschutzerklarung und kommerziellen Kommunikation zustimmen.',
        'already_subscribed' => 'Dieser Abonnent existiert bereits.',
        'throttled' => 'Es wurden zu viele Anfragen gesendet. Bitte warte einen Moment, bevor du es erneut versuchst.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Abonnement bestatigt',
            'message' => 'Dein Newsletter Abonnement wurde erfolgreich aktiviert.',
        ],
        'confirmation_invalid' => [
            'title' => 'Ungultiger Bestatigungslink',
            'message' => 'Der Bestatigungslink ist ungunstig oder abgelaufen. Bitte fordere eine neue Anmeldung an.',
        ],
        'unsubscribed' => [
            'title' => 'Abmeldung abgeschlossen',
            'message' => 'Deine E Mail wurde erfolgreich vom Newsletter abgemeldet.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Ungultiger Abmeldelink',
            'message' => 'Der Abmeldelink ist ungunstig oder nicht mehr verfugbar.',
        ],
    ],
    'actions' => [
        'back_home' => 'Zur Startseite',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Bei RadioChi anmelden',
        'description' => 'Erhalte Neuigkeiten und erteile die Einwilligung fur kommerzielle Mitteilungen von RadioChi.',
        'email_label' => 'E Mail Adresse',
        'email_placeholder' => 'du@domain.de',
        'privacy_lead' => 'Ich habe ',
        'privacy_link' => 'die Datenschutzerklarung',
        'privacy_tail' => ' gelesen und akzeptiere den Erhalt kommerzieller Mitteilungen von RadioChi.',
        'submit_idle' => 'Jetzt anmelden',
        'submit_loading' => 'Anfrage wird gesendet...',
        'legal' => [
            'responsible_label' => 'Verantwortlicher',
            'responsible_value' => ':brand',
            'purpose_label' => 'Zweck',
            'purpose_value' => 'Dein aktives Abonnement verwalten und dir kommerzielle Mitteilungen sowie Neuigkeiten von :brand senden.',
            'unsubscribe_label' => 'Abmeldung',
            'unsubscribe_value' => 'Du kannst dich jederzeit uber jede Newsletter E Mail abmelden.',
            'double_opt_in_label' => 'Aktivierung',
            'double_opt_in_value' => 'Deine Anmeldung wird mit dem Absenden des Formulars und deiner ausdruecklichen Einwilligung aktiv.',
        ],
        'modal' => [
            'duplicate_title' => 'Abonnent bereits registriert',
            'duplicate_body' => 'Diese E Mail ist bereits als aktiver Abonnent registriert. Sie wird nicht dupliziert.',
            'close' => 'Schliessen',
            'continue' => 'Verstanden',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Bestatige deine Newsletter Anmeldung',
            'headline' => 'Bestatige deine Anmeldung',
            'intro' => 'Wir brauchen eine letzte Bestatigung, bevor wir deine Anmeldung aktivieren.',
            'body' => 'Wir haben eine Anmeldeanfrage fur :email erhalten. Klicke auf die Schaltflache, um die Anmeldung zu bestatigen.',
            'cta' => 'Anmeldung bestatigen',
            'expiry' => 'Dieser Bestatigungslink bleibt 7 Tage lang verfugbar.',
            'ignore' => 'Wenn du diese Anmeldung nicht angefordert hast, kannst du diese E Mail einfach ignorieren.',
        ],
        'campaign' => [
            'intro' => 'Mitteilung an aktive Newsletter Abonnenten.',
            'notice' => 'Du erhaltst diese E Mail, weil dein Abonnement aktiv ist. Du kannst dich jederzeit uber den untenstehenden Link abmelden.',
            'unsubscribe_helper' => 'Wenn du keine weiteren Kampagnen erhalten mochtest, nutze diesen direkten Abmeldelink:',
            'unsubscribe_cta' => 'Newsletter abbestellen',
        ],
        'footer' => [
            'responsible_label' => 'Verantwortlicher',
            'contact_label' => 'Kontakt E Mail',
            'purpose_label' => 'Zweck',
            'purpose_value' => 'Das aktive Abonnement verwalten und Newsletter Mitteilungen an aktive Abonnenten senden.',
            'privacy_label' => 'Datenschutzerklarung ansehen',
            'unsubscribe_label' => 'Newsletter abbestellen',
        ],
    ],
];
