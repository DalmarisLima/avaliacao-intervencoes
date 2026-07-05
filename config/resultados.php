<?php

return [
    'cache_ttl' => (int) env('RESULTADOS_CACHE_TTL', 3600),

    'queue_generation' => (bool) env('RESULTADOS_QUEUE_GENERATION', false),
];
