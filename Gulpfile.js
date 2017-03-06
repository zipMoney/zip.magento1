/*!
 * gulp
 * $ npm install gulp-ruby-sass gulp-autoprefixer gulp-cssnano gulp-jshint gulp-concat gulp-uglify gulp-imagemin gulp-notify gulp-rename gulp-livereload gulp-cache del --save-dev
 */

// Load plugins
var gulp = require('gulp'),
  uglify = require('gulp-uglify'),
  jshint = require('gulp-jshint'),
  rename = require('gulp-rename'),
  concat = require('gulp-concat'),
  notify = require('gulp-notify'),
  livereload = require('gulp-livereload'),
  del = require('del')

// Scripts
gulp.task('scripts', ['jshint'], function() {
  return gulp.src(['js/zipmoney/src/**/*.js'])
    .pipe(concat('zipmoney-checkout.js'))
    .pipe(gulp.dest('js/zipmoney/dist/scripts'))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(uglify())
    .pipe(gulp.dest('js/zipmoney/dist/scripts'))
    .pipe(notify({
      message: 'Scripts task complete'
    }));
});

// Scripts
gulp.task('jshint', function() {
  return gulp.src('js/zipmoney/src/scripts/**/*.js')
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter('default'))
});


// Clean
gulp.task('clean', function() {
  return del('js/zipmoney/dist/scripts');
});

// Default task
gulp.task('default', ['clean'], function() {
  return gulp.start('scripts');
});

// Watch
gulp.task('watch', function() {
  gulp.watch('js/zipmoney/src/**/*.js', ['scripts']);
  livereload.listen();
  gulp.watch(['js/zipmoney/dist/scripts']).on('change', livereload.changed);
});
