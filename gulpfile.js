var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass'),
    pipeline = require('readable-stream').pipeline;

// Gulp Css Minify
gulp.task('css', function () {
    return gulp.src(['./assets/css/*.css', '!./assets/css/*.min.css'])
        .pipe(cleanCSS({
            keepSpecialComments: 1,
            level: 2
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// Gulp Sass Compiler
sass.compiler = require('node-sass');
gulp.task('sass', function () {
    return gulp.src('./assets/sass/**/*.scss')
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(gulp.dest('./assets/css/'));
});

// Gulp Script Minify
gulp.task('js', function () {
    return gulp.src(['./assets/js/*.js', '!./assets/js/*.min.js'])
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// global Task
gulp.task('default', gulp.parallel('sass', 'css', 'js'));