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
    .copy('resources/images/logo.svg', 'public/static/images/favicon.svg')
    .copy('resources/images/logo-32.png', 'public/static/images/favicon.png')
    .copy('resources/images/cover.jpg', 'public/static/images/cover.jpg')
    .copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/static/webfonts').options({
        processCssUrls: false
    })
    .extract(['jquery']);

if (mix.inProduction()) {
    mix.version();
}
