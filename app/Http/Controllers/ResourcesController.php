<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;

class ResourcesController extends Controller
{
    public function pwaManifest()
    {
        $json = json_decode(file_get_contents(base_path('resources/json/pwa.json')));

        $json->name = getSiteConfig('name');
        $json->gcm_sender_id = config('webpush.gcm.sender_id');
        $json->short_name = getSiteConfig('name');
        $json->description = getSiteConfig('slogan');

        foreach ($json->icons as $icon) {
            $icon->src = Vite::asset($icon->src);
        }

        foreach ($json->shortcuts as $shortcut) {
            if ("publish" === $shortcut->name) {
                $shortcut->name = __('Publish');
                $shortcut->short_name = __('Publish');
                $shortcut->url = route('writings.create');
            }

            if ("featured" === $shortcut->name) {
                $shortcut->name = __('Golden Flowers');
                $shortcut->short_name = __('Golden Flowers');
                $shortcut->url = route('writings.awards');
            }

            if ("random" === $shortcut->name) {
                $shortcut->name = __('Random');
                $shortcut->short_name = __('Random');
                $shortcut->url = route('writings.random');
            }
        }

        foreach ($json->related_applications as $app) {
            if ("play" === $app->platform) {
                $app->url = config('services.google.play_store.url');
                $app->id = config('services.google.play_store.id');
            }
        }

        $json->iarc_rating_id = config('services.compliance.iarc_rating_id');

        return $json;
    }
}
