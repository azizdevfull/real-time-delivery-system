<?php

return [
    'connection' => [
        'host' => env('MICRO_RABBITMQ_HOST', '127.0.0.1'),
        'port' => env('MICRO_RABBITMQ_PORT', 5672),
        'user' => env('MICRO_RABBITMQ_USER', 'guest'),
        'password' => env('MICRO_RABBITMQ_PASSWORD', 'guest'),
        'vhost' => env('MICRO_RABBITMQ_VHOST', '/'),
        'heartbeat' => env('MICRO_RABBITMQ_HEARTBEAT', 30),
    ],

    'exchange' => [
        'name' => env('MICRO_RABBITMQ_EXCHANGE', 'micro_events'),
        'type' => env('MICRO_RABBITMQ_EXCHANGE_TYPE', 'topic'),
    ],

    // TIZIMDAGI BARCHA QO'SHIMCHA IMKONIYATLARNI BOSHQARISH
    'features' => [
        'idempotency' => env('MICRO_RABBITMQ_IDEMPOTENCY', true), // Dublikat himoyasi
        'idempotency_ttl_seconds' => env('MICRO_RABBITMQ_IDEMPOTENCY_TTL', 86400),
        'idempotency_processing_ttl_seconds' => env('MICRO_RABBITMQ_IDEMPOTENCY_PROCESSING_TTL', 300),
        'publish_timeout_seconds' => env('MICRO_RABBITMQ_PUBLISH_TIMEOUT', 5),
        'publish_confirm_batch_size' => env('MICRO_RABBITMQ_PUBLISH_CONFIRM_BATCH', 1),
        'publish_confirm_max_wait_ms' => env('MICRO_RABBITMQ_PUBLISH_CONFIRM_MAX_WAIT_MS', 10),
        'tracing' => env('MICRO_RABBITMQ_TRACING', true),     // Trace ID zanjiri
        // 'outbox' => env('MICRO_RABBITMQ_OUTBOX', false),     // Xatni otib unutish
        'outbox' => [
            'enabled' => env('MICRO_RABBITMQ_OUTBOX', false),
            'max_attempts' => env('MICRO_RABBITMQ_OUTBOX_ATTEMPTS', 3), // <-- DINAMIK LIMIT
            'publish_timeout_seconds' => env('MICRO_RABBITMQ_OUTBOX_PUBLISH_TIMEOUT', 5),
            'batch_size' => env('MICRO_RABBITMQ_OUTBOX_BATCH_SIZE', 20),
        ],
    ],

    'consumer' => [
        'prefetch_count' => env('MICRO_RABBITMQ_PREFETCH', 1),
    ],

    // Event handlerlarni qaysi papkalardan topishni belgilaydi
    'paths' => [
        base_path('app'),
    ],

    // DEFAULT RETRY (QAYTA URINISH) SOZLAMALARI
    'retry' => [
        'enabled' => env('MICRO_RABBITMQ_RETRY_ENABLED', true), // Global yoqish/o'chirish
        'max_attempts' => env('MICRO_RABBITMQ_MAX_ATTEMPTS', 3),     // Necha marta?
        'delay_ms' => env('MICRO_RABBITMQ_RETRY_DELAY', 10000),  // Necha soniya kutsin?
        'backoff_multiplier' => env('MICRO_RABBITMQ_RETRY_BACKOFF_MULTIPLIER', 2.0),
        'max_delay_ms' => env('MICRO_RABBITMQ_RETRY_MAX_DELAY', 300000),
        'jitter_ms' => env('MICRO_RABBITMQ_RETRY_JITTER', 250),
        'non_retryable_exceptions' => [
            'Azizdev\\MicroRabbit\\Exceptions\\DoNotRetryException',
            'Illuminate\\Validation\\ValidationException',
        ],
    ],

    'monitoring' => [
        'enabled' => env('MICRO_RABBITMQ_MONITORING', true),
        'cache_prefix' => env('MICRO_RABBITMQ_METRICS_PREFIX', 'micro_rabbit_metrics'),
        'cache_ttl_seconds' => env('MICRO_RABBITMQ_METRICS_TTL', 86400),
        'outbox_health_interval_seconds' => env('MICRO_RABBITMQ_OUTBOX_HEALTH_INTERVAL', 10),
        'alert_outbox_backlog_threshold' => env('MICRO_RABBITMQ_ALERT_OUTBOX_BACKLOG', 1000),
        'alert_outbox_blocked_threshold' => env('MICRO_RABBITMQ_ALERT_OUTBOX_BLOCKED', 100),
        'alert_failed_queue_threshold' => env('MICRO_RABBITMQ_ALERT_FAILED_QUEUE', 100),
        'alert_publish_timeout_count_threshold' => env('MICRO_RABBITMQ_ALERT_PUBLISH_TIMEOUTS', 10),
    ],
];
