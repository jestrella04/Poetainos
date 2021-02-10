<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function pwaManifest()
    {
        $json = file_get_contents(base_path('resources/json/pwa.json'));
        $json = str_replace('{{name}}', getSiteConfig('name'), $json);
        $json = str_replace('{{description}}', getSiteConfig('description'), $json);

        return $json;
    }
}
