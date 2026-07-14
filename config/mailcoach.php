<?php

return [
    /**
     * Pruning settings determine after how many days the models should be pruned.
     * This will keep aggregated (calculated) statistics available, individual
     * subscriber rows will be deleted. Using null will disable pruning.
     *
     * @since 8.13.0
     */
    'prune_after_days' => [
        'sends' => env('MAILCOACH_PRUNE_SENDS_AFTER_DAYS'),
        'opens' => env('MAILCOACH_PRUNE_OPENS_AFTER_DAYS'),
        'clicks' => env('MAILCOACH_PRUNE_CLICKS_AFTER_DAYS'),
        'unsubscribes' => env('MAILCOACH_PRUNE_UNSUBSCRIBES_AFTER_DAYS'),
        'send_feedback_items' => env('MAILCOACH_PRUNE_SEND_FEEDBACK_ITEMS_AFTER_DAYS'),
        'transactional_mail_log_items' => env('MAILCOACH_PRUNE_TRANSACTIONAL_MAIL_LOG_ITEMS_AFTER_DAYS'),
        'subscriber_imports' => env('MAILCOACH_PRUNE_SUBSCRIBER_IMPORTS_AFTER_DAYS', 7),
    ],
];
