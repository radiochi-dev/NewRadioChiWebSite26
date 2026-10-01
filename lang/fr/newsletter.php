<?php

return [
    'subscribe' => [
        'pending' => 'Nous avons bien recu votre demande. La confirmation finale reste necessaire via le lien de verification.',
        'success' => 'Votre abonnement est maintenant actif.',
    ],
    'validation' => [
        'email_required' => 'Veuillez saisir une adresse email.',
        'email_invalid' => 'Veuillez saisir une adresse email valide.',
        'privacy_accepted' => 'Vous devez accepter la politique de confidentialite et les communications commerciales.',
        'already_subscribed' => 'Cet abonne existe deja.',
        'throttled' => 'Trop de demandes ont ete envoyees. Veuillez patienter avant de reessayer.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Inscription confirmee',
            'message' => 'Votre inscription a la newsletter a ete activee avec succes.',
        ],
        'confirmation_invalid' => [
            'title' => 'Lien de confirmation invalide',
            'message' => 'Le lien de confirmation est invalide ou a expire. Veuillez demander une nouvelle inscription.',
        ],
        'unsubscribed' => [
            'title' => 'Desinscription terminee',
            'message' => 'Votre adresse email a bien ete desinscrite de la newsletter.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Lien de desinscription invalide',
            'message' => 'Le lien de desinscription est invalide ou n est plus disponible.',
        ],
    ],
    'actions' => [
        'back_home' => 'Retour a l accueil',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Abonnez-vous a RadioChi',
        'description' => 'Recevez les nouveautes et autorisez l envoi de communications commerciales de RadioChi.',
        'email_label' => 'Adresse email',
        'email_placeholder' => 'vous@domaine.com',
        'privacy_lead' => 'J ai lu et j accepte ',
        'privacy_link' => 'la Politique de Confidentialite',
        'privacy_tail' => ' ainsi que l envoi de communications commerciales de RadioChi.',
        'submit_idle' => 'Je veux m abonner',
        'submit_loading' => 'Envoi de la demande...',
        'legal' => [
            'responsible_label' => 'Responsable',
            'responsible_value' => ':brand',
            'purpose_label' => 'Finalite',
            'purpose_value' => 'Gerer votre abonnement actif et vous envoyer des communications commerciales et des nouveautes de :brand.',
            'unsubscribe_label' => 'Desinscription',
            'unsubscribe_value' => 'Vous pouvez vous desinscrire a tout moment depuis n importe quel email de newsletter.',
            'double_opt_in_label' => 'Activation',
            'double_opt_in_value' => 'Votre inscription devient active des l envoi du formulaire avec votre consentement explicite.',
        ],
        'modal' => [
            'duplicate_title' => 'Abonne deja enregistre',
            'duplicate_body' => 'Cette adresse email est deja abonnee activement. Elle ne sera pas dupliquee.',
            'close' => 'Fermer',
            'continue' => 'Compris',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Confirmez votre inscription a la newsletter',
            'headline' => 'Confirmez votre inscription',
            'intro' => 'Nous avons besoin d une confirmation finale avant d activer votre inscription.',
            'body' => 'Nous avons recu une demande d inscription pour :email. Cliquez sur le bouton pour confirmer cette inscription.',
            'cta' => 'Confirmer l inscription',
            'expiry' => 'Ce lien de confirmation restera disponible pendant 7 jours.',
            'ignore' => 'Si vous n avez pas demande cette inscription, vous pouvez ignorer cet email en toute securite.',
        ],
        'campaign' => [
            'intro' => 'Communication envoyee aux abonnes actifs de la newsletter.',
            'notice' => 'Vous recevez cet email car votre inscription est active et vous pouvez vous desinscrire a tout moment via le lien ci-dessous.',
            'unsubscribe_helper' => 'Si vous ne souhaitez plus recevoir de futures campagnes, utilisez ce lien direct de desinscription :',
            'unsubscribe_cta' => 'Me desinscrire de la newsletter',
        ],
        'footer' => [
            'responsible_label' => 'Responsable du traitement',
            'contact_label' => 'Email de contact',
            'purpose_label' => 'Finalite',
            'purpose_value' => 'Gerer l abonnement actif et envoyer des communications newsletter aux abonnes actifs.',
            'privacy_label' => 'Consulter la politique de confidentialite',
            'unsubscribe_label' => 'Se desinscrire de la newsletter',
        ],
    ],
];
