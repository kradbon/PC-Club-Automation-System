<?php

return [
    'locales' => ['en', 'ru', 'tj'],
    'currency' => env('CLUB_CURRENCY', 'TJS'),
    'booking' => [
        'minimum_minutes' => (int) env('BOOKING_MINIMUM_MINUTES', 60),
        'maximum_minutes' => (int) env('BOOKING_MAXIMUM_MINUTES', 480),
        'maximum_days_ahead' => (int) env('BOOKING_MAXIMUM_DAYS_AHEAD', 30),
        'maximum_active_per_user' => (int) env('BOOKING_MAXIMUM_ACTIVE_PER_USER', 4),
        'cancellation_cutoff_minutes' => (int) env('BOOKING_CANCELLATION_CUTOFF_MINUTES', 120),
        'bonus_first' => (bool) env('BOOKING_BONUS_FIRST', true),
    ],
    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'simulation'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'simulation_amount_limit' => (float) env('PAYMENT_SIMULATION_LIMIT', 500),
    ],
    'food' => [
        'delivery_fee_cents' => (int) env('FOOD_DELIVERY_FEE_CENTS', 0),
    ],
    'session' => [
        'checkin_early_minutes' => (int) env('SESSION_CHECKIN_EARLY_MINUTES', 30),
        'no_show_grace_minutes' => (int) env('SESSION_NO_SHOW_GRACE_MINUTES', 15),
    ],
    'opening_hours' => [
        'start' => env('CLUB_OPENING_TIME', '10:00'),
        'end' => env('CLUB_CLOSING_TIME', '02:00'),
    ],
];
