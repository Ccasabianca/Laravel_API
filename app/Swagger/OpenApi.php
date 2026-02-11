<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Laravel Books API", version="1.0.0")
 * @OA\Server (url="http://localhost:8000",description="Laravel serve")
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class OpenApi {}
