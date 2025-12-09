const gulp = require('gulp'),
    del = require('del'),
    zip = require('gulp-zip');
const { series } = require('gulp');

// ZIP Path
var zipPath = [
    './',
    './**',
    './**',
    '!./.git/**',
    '!./**/.gitignore',
    '!./**/*.md',
    '!./**/*.scss',
    '!./**/tailwind-input.css',
    '!./**/composer.json',
    '!./**/tailwind.config.js',
    '!./**/auth.json',
    '!./**/.gitignore',
    '!./**/LICENSE',
    '!./**/phpunit*',
    '!./tests/**',
    '!./node_modules/**',
    '!./build/**',
    '!./gulpfile.js',
    '!./package.json',
    '!./package-lock.json',
    '!./composer.json',
    '!./composer.lock',
    '!./phpcs.xml',
    '!./LICENSE',
    '!./README.md',
    '!./vendor/bin/**',
    '!./vendor/**/*.txt',
    '!./includes/file-manager/instawp*.php',
    '!./includes/database-manager/instawp*.php',
];

// Clean CSS, JS and ZIP
function clean_files() {
    let cleanPath = ['../iwp-mu.zip'];
    return del(cleanPath, { force: true });
}

// Create ZIP file
function create_zip() {
    return gulp.src(zipPath, { base: '../' })
        .pipe(zip('iwp-mu.zip'))
        .pipe(gulp.dest('../'))
}

exports.default = series(clean_files, create_zip);