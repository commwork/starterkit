const { src, dest, watch, series } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const browserSync = require('browser-sync').create();

// Edit localhost URL
const siteUrl = 'http://localhost:8888/starterkit';

function compileSass() {
  return src('./assets/scss/style.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({ 
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(dest('./'))
    .pipe(browserSync.stream()); 
}

function compileAdminSass() {
  return src('./assets/scss/admin.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(dest('./'))
    .pipe(browserSync.stream());
}

function compileAdminFrontendSass() {
  return src('./assets/scss/admin-frontend.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(dest('./'))
    .pipe(browserSync.stream());
}

function serve(done) {
  browserSync.init({
    proxy: siteUrl,
    open: true,
    notify: true,
    ghostMode: false,
    // logLevel: 'debug' // Für mehr Logs bei Problemen auskommentieren
  });
  done();
}

function reload(done) {
  browserSync.reload();
  done();
}

function watcher() {
  // Compile SCSS → inject
  watch('./assets/scss/**/*.scss', compileSass);
  watch('./template-parts/**/*.scss', compileSass);
  watch('./assets/scss/admin.scss', compileAdminSass);
  watch('./assets/scss/admin-frontend.scss', compileAdminFrontendSass);

  // PHP changes → full reload
  watch([
    './**/*.php',
  ], reload);
}

// Compikle → server → watch
exports.default = series(compileSass, compileAdminSass, compileAdminFrontendSass, serve, watcher);

// Single tasks (optional)
exports.sass = compileSass;
exports.adminSass = compileAdminSass;
exports.adminFrontendSass = compileAdminFrontendSass;
exports.serve = serve;