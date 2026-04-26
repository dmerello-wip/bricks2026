<?php

/**
 * Route prefix translations for frontend module URLs.
 *
 * Convention for adding a new module (e.g. "projects"):
 *
 *   1. Add the prefix key here and in lang/it/routes.php.
 *   2. In routes/web.php, register two named routes BEFORE the catch-all
 *      (one per locale variant), pointing to the module's frontend controller.
 *   3. In the Twill module controller, override getLocalizedPermalinkBase()
 *      returning a locale-keyed array using these translation keys.
 */

return [
    'articles' => 'news',
];
