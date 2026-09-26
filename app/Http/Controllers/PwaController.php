<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * What the installable web app needs: its manifest and the page shown offline.
 */
class PwaController extends Controller
{
    public function manifest(): \stdClass
    {
        $json = json_decode((string) file_get_contents(base_path('resources/json/manifest.json')));

        $json->name = getSiteConfig('name');
        $json->gcm_sender_id = config('webpush.gcm.sender_id');
        $json->short_name = getSiteConfig('name');
        $json->description = getSiteConfig('slogan');

        $shortcuts = [
            'account' => [__('My account'), route('users.account')],
            'publish' => [__('Publish'), route('writings.create')],
            'featured' => [__('Golden Flowers'), route('writings.awards')],
            'random' => [__('Random'), route('writings.random')],
            'authors' => [__('Writers'), route('users.index')],
        ];

        foreach ($json->shortcuts as $shortcut) {
            if (! isset($shortcuts[$shortcut->name])) {
                continue;
            }

            [$label, $url] = $shortcuts[$shortcut->name];
            $shortcut->name = $label;
            $shortcut->short_name = $label;
            $shortcut->url = $url;
        }

        foreach ($json->related_applications as $app) {
            if ($app->platform === 'webapp') {
                $app->url = route('pwa.manifest');
            } elseif ($app->platform === 'play') {
                $app->url = config('services.google.play_store.url');
                $app->id = config('services.google.play_store.id');
            }
        }

        $json->iarc_rating_id = config('services.compliance.iarc_rating_id');

        return $json;
    }

    public function offline(): Response
    {
        return Inertia::render('generic/PoOffline', [
            'meta' => [
                'title' => getPageTitle([__('Offline')]),
            ],
        ]);
    }
}
