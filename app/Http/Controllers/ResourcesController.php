<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function pwaManifest()
    {
        $json = file_get_contents(base_path('resources/json/pwa.json'));

        return str_replace('{{name}}', getSiteConfig('name'), $json);
    }
}
