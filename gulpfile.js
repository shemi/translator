var elixir = require('laravel-elixir');

var _laravelElixir2 = _interopRequireDefault(elixir);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {

    mix
        .sass('app.scss', './public/css')

        .webpack('app.js', './public/js', './resources/assets/js', {

            devtool: "cheap-module-eval-source-map",

            themeLoader: {
                themes: ['./resources/assets/sass/variables.scss']
            },

            module: {
                loaders: [
                    { test: /\.vue$/, loader: 'vue' },
                    {
                        test: /\.js$/,
                        loader: 'buble',
                        exclude: /node_modules/
                    },
                    { test: /\.html$/, loader: 'html' },
                    { test: /\.scss$/, loader: 'style-loader!css-loader!autoprefixer-loader!sass-loader!vuestrap-theme-loader' }
                ]
            },

            babel: {
                "presets": [
                    ["es2015", {"modules": false, "loose": true}]
                ],
                plugins: [
                    'add-module-exports',
                    'transform-runtime'
                ]
            }

        })

        .browserSync({
            files: [
                './**/*.php',
                './public/css/**/*.css',
                './public/js/**/*.js'
            ],
            proxy: 'translator.dev'
        });

});
