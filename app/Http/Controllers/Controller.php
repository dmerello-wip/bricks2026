<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Public-facing endpoints for the Twill + Inertia.js CMS.',
    title: 'Twill Inertia StarterCMS',
)]
#[OA\Server(url: '/', description: 'Application root')]
abstract class Controller
{
    //
}
