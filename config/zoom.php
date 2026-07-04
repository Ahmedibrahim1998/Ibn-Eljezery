<?php

return [
    /*
     * Zoom Server-to-Server OAuth app credentials.
     * Create an app at https://marketplace.zoom.us (type: Server-to-Server OAuth)
     * and copy Account ID, Client ID and Client Secret into your .env.
     */
    'account_id' => env('ZOOM_ACCOUNT_ID'),
    'client_id' => env('ZOOM_CLIENT_ID'),
    'client_secret' => env('ZOOM_CLIENT_SECRET'),

    // Default meeting timezone.
    'timezone' => env('ZOOM_TIMEZONE', 'Africa/Cairo'),
];
