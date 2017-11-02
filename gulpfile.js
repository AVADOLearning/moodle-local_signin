/**
 * Local signin.
 *
 * @author Luke Carrier <luke.carrier@avadolearning.com>
 * @copyright 2016 AVADO Learning Limited
 */

var gulp = require('gulp');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');

gulp.task('js', function() {
    return gulp.src('./amd/src/*.js')
        .pipe(rename(function(path) {
            path.extname = '.min.js';
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./amd/build'));
});

gulp.task('default', ['js']);
