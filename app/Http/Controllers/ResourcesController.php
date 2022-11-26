<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function pwaManifest()
    {
        $json = file_get_contents(base_path('resources/json/pwa.json'));
        $json = str_replace('{{name}}', getSiteConfig('name'), $json);
        $json = str_replace('{{gcm_sender_id}}', config('webpush.gcm.sender_id'), $json);
        $json = str_replace('{{description}}', getSiteConfig('slogan'), $json);
        $json = str_replace('{{publish}}', __('Publish'), $json);
        $json = str_replace('{{featured}}', __('Golden Flowers'), $json);
        $json = str_replace('{{random}}', __('Random'), $json);
        $json = str_replace('{{url.publish}}', route('writings.create'), $json);
        $json = str_replace('{{url.featured}}', route('writings.awards'), $json);
        $json = str_replace('{{url.random}}', route('writings.random'), $json);
        $json = str_replace('{{play_store_url}}', config('services.google.play_store.url'), $json);
        $json = str_replace('{{play_store_id}}', config('services.google.play_store.id'), $json);
        $json = str_replace('{{iarc_rating_id}}', config('services.compliance.iarc_rating_id'), $json);

        return $json;
    }
}
