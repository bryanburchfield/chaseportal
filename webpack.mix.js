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

// mix.js('resources/js/master.js', 'public/js')
 mix.sass('resources/sass/app.scss', 'public/css/app.css');

// mix.scripts([
//     'resources/js/moment.js',
//     'resources/js/multiselect.js',
//     'resources/js/datetimepicker.js',

// ], 'public/js/assets.js');

// mix.scripts([
//     'resources/js/master.js',

// ], 'public/js/master.js');