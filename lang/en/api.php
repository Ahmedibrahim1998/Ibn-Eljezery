<?php

// API messages shown to students in the mobile app.
return [
    'auth' => [
        'name_required' => 'Please enter your name.',
        'phone_required' => 'Please enter your phone number.',
        'phone_invalid' => 'The phone number is not valid.',
        'phone_taken' => 'This number is already registered — please log in.',
        'email_invalid' => 'The email address is not valid.',
        'email_taken' => 'This email is already registered.',
        'password_required' => 'Please enter a password.',
        'password_min' => 'The password must be at least 8 characters.',
        'login_required' => 'Please enter your phone number or email.',
        'failed' => 'These credentials do not match our records.',
        'panel_only' => 'This is a dashboard account — please sign in on the website.',
        'delete_staff' => 'Teacher and staff accounts are removed by the administration — please contact us.',
    ],
    'booking' => [
        'success' => 'Request received ✓ A supervisor will contact you to confirm.',
        'already' => 'You are already enrolled in this group.',
        'closed' => 'Enrollment for this group is closed.',
    ],
];
