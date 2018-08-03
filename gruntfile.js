
module.exports = function(grunt) {
    grunt.loadNpmTasks('grunt-newer');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-openport');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.initConfig({
        // Reference package.json
        pkg : grunt.file.readJSON('package.json'),

        // Compile SCSS with the Compass Compiler
        compass : {
            production : {
                options : {
                    sassDir     : 'src/styles',
                    cssDir      : 'assets/css',
                    outputStyle : 'compressed',
                    cacheDir    : 'src/styles/.sass-cache',
                    environment : 'production',
                    sourcemap   : true
                },
            }
        },
        postcss: {
            options: {
              map: true, // inline sourcemaps
              processors: [
                require('pixrem')(), // add fallbacks for rem units
                require('autoprefixer')({browsers: 'last 3 version'}), // add vendor prefixes
                require('cssnano')() // minify the result
              ]
            },
            dist: {
                src: 'assets/css/*.css'
            }
        },
        // JSHint - Check Javascript for errors
        jshint : {
            options : {
                globals  : {
                  jQuery : true,
                },
                smarttabs : true,
            },
            all: ['Gruntfile.js', 'src/scripts/**/*.js', '!assets/scripts/*.js', '!src/scripts/vendors/**/*.js'],
        },
        // Combine & minify JS
        uglify : {
            options : {
              sourceMap : true
            },
            public : {
                files : {
                    'assets/js/public.min.js' : [ 'src/scripts/vendors/jquery.waypoints.js', 'src/scripts/vendors/inview.js', 'src/scripts/includes/jquery.jumpscroll.js', 'src/scripts/includes/jquery.scrollview.js', 'src/scripts/includes/jquery.scrolltoggle.js', 'src/scripts/public.js' ]
                }
            },
            admin : {
                files : {
                    'assets/js/admin.min.js' : [ 'src/scripts/admin.js' ]
                }
            }
        },

        // Watch
        watch : {
			options: {
              livereload: true,
            },
            cssPostProcess : {
                files : 'src/styles/**/*.scss',
                tasks : [ 'compass:production', 'newer:postcss' ]
            },
            jsPostProcess : {
                files : [ 'src/scripts/**/*.js' ],
                tasks : [ 'newer:jshint', 'uglify' ],
            },
            livereload : {
                files   : [ 'assets/css/*.css', 'assets/js/*.js', '*.html', 'assets/images/*', '*.php' ],
            },
        },
    });
    grunt.registerTask('default', ['openport:watch.options.livereload:35731', 'watch']);
};