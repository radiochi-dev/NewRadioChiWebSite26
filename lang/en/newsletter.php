<?php

return [
    'subscribe' => [
        'pending' => 'We have received your request. Final confirmation is still required from the verification link.',
    ],
    'validation' => [
        'email_required' => 'Please enter an email address.',
        'email_invalid' => 'Please enter a valid email address.',
        'privacy_accepted' => 'You must accept the privacy policy and commercial communications.',
        'already_subscribed' => 'This subscriber already exists.',
        'throttled' => 'Too many requests were sent. Please wait a moment before trying again.',
    ],
    'status' => [
        'confirmed' => [
            'title' => 'Subscription confirmed',
            'message' => 'Your newsletter subscription has been activated successfully.',
        ],
        'confirmation_invalid' => [
            'title' => 'Invalid confirmation link',
            'message' => 'The confirmation link is invalid or has expired. Please request a new subscription.',
        ],
        'unsubscribed' => [
            'title' => 'Unsubscription completed',
            'message' => 'Your email has been unsubscribed from the newsletter successfully.',
        ],
        'unsubscribe_invalid' => [
            'title' => 'Invalid unsubscribe link',
            'message' => 'The unsubscribe link is invalid or no longer available.',
        ],
    ],
    'actions' => [
        'back_home' => 'Back to home',
    ],
    'front' => [
        'eyebrow' => 'Newsletter',
        'title' => 'Subscribe to RadioChi',
        'description' => 'Receive updates, confirm your signup by email and authorize the sending of RadioChi commercial communications.',
        'email_label' => 'Email address',
        'email_placeholder' => 'you@example.com',
        'privacy_lead' => 'I have read and accept ',
        'privacy_link' => 'the Privacy Policy',
        'privacy_tail' => ' and the sending of RadioChi commercial communications.',
        'submit_idle' => 'Subscribe now',
        'submit_loading' => 'Sending request...',
        'legal' => [
            'responsible_label' => 'Controller',
            'responsible_value' => ':brand',
            'purpose_label' => 'Purpose',
            'purpose_value' => 'Manage your subscription, confirm your signup and send you commercial communications and updates from :brand.',
            'unsubscribe_label' => 'Unsubscribe',
            'unsubscribe_value' => 'You may unsubscribe at any time from any newsletter email.',
            'double_opt_in_label' => 'Confirmation',
            'double_opt_in_value' => 'Your signup is not activated until you confirm the link sent by email.',
        ],
        'modal' => [
            'duplicate_title' => 'Subscriber already registered',
            'duplicate_body' => 'This email is already an active subscriber. It will not be duplicated.',
            'close' => 'Close',
            'continue' => 'Got it',
        ],
    ],
    'mail' => [
        'double_opt_in' => [
            'subject' => 'Confirm your newsletter subscription',
            'headline' => 'Confirm your subscription',
            'intro' => 'We need one final confirmation before activating your signup.',
            'body' => 'We received a subscription request for :email. Click the button below to confirm the signup.',
            'cta' => 'Confirm subscription',
            'expiry' => 'This confirmation link will remain available for 7 days.',
            'ignore' => 'If you did not request this subscription, you can safely ignore this email.',
        ],
        'campaign' => [
            'intro' => 'Message sent to active newsletter subscribers.',
            'notice' => 'You are receiving this email because your subscription is active, and you may unsubscribe at any time using the link below.',
            'unsubscribe_helper' => 'If you no longer want to receive future campaigns, use this direct unsubscribe link:',
            'unsubscribe_cta' => 'Unsubscribe from the newsletter',
        ],
        'footer' => [
            'responsible_label' => 'Data controller',
            'contact_label' => 'Contact email',
            'purpose_label' => 'Purpose',
            'purpose_value' => 'Manage the subscription, confirm the signup and send newsletter communications to active subscribers.',
            'privacy_label' => 'View the privacy policy',
            'unsubscribe_label' => 'Unsubscribe from the newsletter',
        ],
    ],
];
