const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/static/js')
    .sass('resources/sass/app.scss', 'public/static/css').options({
        processCssUrls: false
    })
    .copy('resources/images/logo.svg', 'public/static/images/logo.svg')
    .copy('resources/images/logo-32.png', 'public/static/images/logo-32.png')
    .copy('resources/images/logo-144.png', 'public/static/images/logo-144.png')
    .copy('resources/images/logo-192.png', 'public/static/images/logo-192.png')
    .copy('resources/images/logo-512.png', 'public/static/images/logo-512.png')
    .copy('resources/images/cover.jpg', 'public/static/images/cover.jpg')
    .copy('resources/json/pwa.json', 'public/static/json/pwa-manifest.json')
    .copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/static/webfonts').options({
        processCssUrls: false
    })
    .extract(['jquery']);

if (mix.inProduction()) {
    mix.version();
}
