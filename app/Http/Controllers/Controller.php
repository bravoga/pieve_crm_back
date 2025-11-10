<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Ficha Digital API",
    description: "API para el sistema de gestión de solicitudes y fichas digitales"
)]
#[OA\Server(
    url: "/api",
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}
