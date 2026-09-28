<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Groups
    |--------------------------------------------------------------------------
    |
    | The route table is sent to every browser, so visitors only get the
    | routes they can use: the admin panel's paths stay out of their copy.
    | ziggyRouteGroup() picks the group for the current user.
    |
    */

    'groups' => [
        'visitor' => ['!admin.*'],
        'admin' => ['*'],
    ],

];
