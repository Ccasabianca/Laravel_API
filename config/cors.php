<?php

return [
    // Routes sur lesquelles appliquer CORS (API et endpoint Sanctum).
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'], // Méthodes HTTP autorisées.
    'allowed_origins' => ['http://localhost:5173'], // Origines autorisées (en dev : *, en prod : domaines précis).
    // Permet d’autoriser des origines via des motifs (regex). Rarement utilisé.
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Headers acceptés dans la requête.
    'exposed_headers' => [], // Headers lisibles par le client dans la réponse.
    'max_age' => 0, // Durée de mise en cache de la configuration CORS.
    // Autoriser ou non l’envoi de cookies/sessions.
    // API REST = false (stateless).
    'supports_credentials' => false,
];
