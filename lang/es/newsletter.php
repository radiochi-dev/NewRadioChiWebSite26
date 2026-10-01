<?php

return [
    'subscribe' => [
        'pending' => 'Hemos recibido tu solicitud. Falta la confirmacion final desde el enlace de verificacion.',
    ],
    'validation' => [
        'email_required' => 'Introduce un correo electronico.',
        'email_invalid' => 'Introduce un correo electronico valido.',
        'privacy_accepted' => 'Debes aceptar la politica de privacidad y las comunicaciones comerciales.',
        'already_subscribed' => 'Este suscriptor ya existe.',
        'throttled' => 'Has enviado demasiadas solicitudes. Espera un momento antes de volver a intentarlo.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Suscripcion confirmada',
            'message' => 'Tu suscripcion a la newsletter ha quedado activada correctamente.',
        ],
        'confirmation_invalid' => [
            'title' => 'Enlace de confirmacion no valido',
            'message' => 'El enlace de confirmacion no es valido o ha caducado. Solicita una nueva suscripcion.',
        ],
        'unsubscribed' => [
            'title' => 'Baja completada',
            'message' => 'Tu correo ha quedado dado de baja de la newsletter correctamente.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Enlace de baja no valido',
            'message' => 'El enlace de baja no es valido o ya no esta disponible.',
        ],
    ],
    'actions' => [
        'back_home' => 'Volver al inicio',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Suscribete a RadioChi',
        'description' => 'Recibe novedades, confirma tu alta por correo y autoriza el envio de comunicaciones comerciales de RadioChi.',
        'email_label' => 'Correo electronico',
        'email_placeholder' => 'tuemail@dominio.com',
        'privacy_lead' => 'He leido y acepto ',
        'privacy_link' => 'la Politica de Privacidad',
        'privacy_tail' => ' y el envio de comunicaciones comerciales de RadioChi.',
        'submit_idle' => 'Quiero suscribirme',
        'submit_loading' => 'Enviando solicitud...',
        'legal' => [
            'responsible_label' => 'Responsable',
            'responsible_value' => ':brand',
            'purpose_label' => 'Finalidad',
            'purpose_value' => 'Gestionar tu suscripcion, confirmar el alta y enviarte comunicaciones comerciales y novedades de :brand.',
            'unsubscribe_label' => 'Baja',
            'unsubscribe_value' => 'Puedes darte de baja en cualquier momento desde cualquier correo de newsletter.',
            'double_opt_in_label' => 'Confirmacion',
            'double_opt_in_value' => 'Tu alta no se activa hasta que confirmes el enlace recibido por correo.',
        ],
        'modal' => [
            'duplicate_title' => 'Suscriptor ya registrado',
            'duplicate_body' => 'Este correo ya figura como suscriptor activo. No vamos a duplicarlo.',
            'close' => 'Cerrar',
            'continue' => 'Entendido',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Confirma tu suscripcion a la newsletter',
            'headline' => 'Confirma tu suscripcion',
            'intro' => 'Necesitamos una confirmacion final antes de activar tu alta.',
            'body' => 'Hemos recibido una solicitud de suscripcion para :email. Pulsa el boton para confirmar el alta.',
            'cta' => 'Confirmar suscripcion',
            'expiry' => 'Este enlace de confirmacion estara disponible durante 7 dias.',
            'ignore' => 'Si no has solicitado esta suscripcion, puedes ignorar este correo sin realizar ninguna accion.',
        ],
        'campaign' => [
            'intro' => 'Comunicacion enviada a suscriptores activos de la newsletter.',
            'notice' => 'Recibes este correo porque tu suscripcion esta activa y puedes darte de baja en cualquier momento desde el enlace incluido abajo.',
            'unsubscribe_helper' => 'Si ya no quieres recibir futuras campanas, usa este enlace directo de baja:',
            'unsubscribe_cta' => 'Darme de baja de la newsletter',
        ],
        'footer' => [
            'responsible_label' => 'Responsable del tratamiento',
            'contact_label' => 'Correo de contacto',
            'purpose_label' => 'Finalidad',
            'purpose_value' => 'Gestionar la suscripcion, confirmar el alta y enviar comunicaciones de newsletter a suscriptores activos.',
            'privacy_label' => 'Consultar la politica de privacidad',
            'unsubscribe_label' => 'Darse de baja de la newsletter',
        ],
    ],
];
