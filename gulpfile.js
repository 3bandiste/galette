var gulp = require('gulp');

const { series, parallel } = require('gulp');
var del = require('del');
var uglify = require('gulp-uglify');
var cleanCSS = require('gulp-clean-css');
var merge = require('merge-stream');
var concat = require('gulp-concat');
var replace = require('gulp-replace');

var galette = {
    'modules': './node_modules/',
    'public': './galette/webroot/assets/'
};

var main_styles = [
    './galette/webroot/themes/default/galette.css',
    './node_modules/summernote/dist/summernote-lite.min.css',
];

var main_scripts = [
    './node_modules/jquery/dist/jquery.js',
    './node_modules/js-cookie/dist/js.cookie.min.js',
    './node_modules/summernote/dist/summernote-lite.min.js',
    './galette/webroot/js/common.js',
];

var main_assets = [
    {
        'src': './node_modules/summernote/dist/font/*',
        'dest': '/webfonts/'
    }, {
        'src': './node_modules/summernote/dist/lang/*.min.js',
        'dest': '/js/lang/'
    }
];

const clean = function(cb) {
  del([galette.public]);
  cb();
};

function watch() {
  //wilcards are mandatory for task not to run only once...
  gulp.watch('./galette/webroot/themes/**/*.css', series(styles));
  gulp.watch('./galette/webroot/js/*.js', series(scripts));
};

function styles() {
  var _dir = galette.public + '/css/';

  main = gulp.src(main_styles)
    .pipe(replace('url(images/', 'url(../images/'))
    .pipe(replace('url(font/', 'url(../webfonts/'))
    .pipe(cleanCSS())
    .pipe(concat('galette-main.bundle.min.css'))
    .pipe(gulp.dest(_dir));

  return merge(main);
};

function scripts() {
  var _dir = galette.public + '/js/';

  main = gulp.src(main_scripts)
    .pipe(concat('galette-main.bundle.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(_dir));

  chartjs = gulp.src([
        './node_modules/chart.js/dist/chart.min.js',
        './node_modules/chartjs-plugin-autocolors/dist/chartjs-plugin-autocolors.min.js',
        './node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js'
  ])
    .pipe(concat('galette-chartjs.bundle.min.js'))
    .pipe(gulp.dest(_dir));

  sortablejs = gulp.src([
        './node_modules/sortablejs/Sortable.min.js',
  ])
    .pipe(concat('galette-sortablejs.bundle.min.js'))
    .pipe(gulp.dest(_dir));

  return merge(main, chartjs, sortablejs);
};

function assets() {
    main = main_assets.map(function (asset) {
        return gulp.src(asset.src)
            .pipe(gulp.dest(galette.public + asset.dest));
        }
    );

    return merge(main);
};

exports.clean = clean;
exports.watch = watch;

exports.styles = styles;
exports.scripts = scripts;
exports.assets = assets;

exports.build = series(styles, scripts, assets);
exports.default = exports.build;
