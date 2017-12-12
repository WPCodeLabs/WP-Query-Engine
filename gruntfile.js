
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
                    sassDir     : 'styles',
                    cssDir      : 'styles/temp',
                    outputStyle : 'compressed',
                    cacheDir    : 'styles/.sass-cache',
                    environment : 'production',
                    sourcemap   : true
                },
            },
            development : {
                options : {
                    sassDir     : 'styles',
                    cssDir      : 'styles/temp',
                    cacheDir    : 'styles/.sass-cache',
                    environment : 'development',
                    outputStyle : 'expanded',
                    sourcemap   : true
                },
            },
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
            public : {
                src  : 'styles/temp/style.css',
                dest : 'style.css'
            },
            admin : {
                src  : 'styles/temp/admin.css',
                dest : 'styles/dist/admin.min.css'
            }
        },
        // Clean temp files
        clean: {
          temp_css: ['styles/temp/'],
        },
        // JSHint - Check Javascript for errors
        jshint : {
            options : {
                globals  : {
                  jQuery : true,
                },
                smarttabs : true,
            },
            all : [ 'Gruntfile.js', 'scripts/**/*.js', '!scripts/dist/*.js', '!scripts/vendors/*.js' ],
        },
        // Combine & minify JS
        uglify : {
            options : {
              sourceMap : true
            },
            public : {
                files : {
                    'scripts/dist/public.min.js' : [ 'scripts/public.js' ]
                }
            },
            admin : {
                files : {
                    'scripts/dist/admin.min.js' : [ 'scripts/admin.js' ]
                }
            }
        },

        // Watch
        watch : {
			options: {
              livereload: true,
            },
            cssPostProcess : {
                files : 'styles/**/*.scss',
                tasks : [ 'compass:development', 'newer:postcss', 'clean' ]
            },
            jsPostProcess : {
                files : [ 'scripts/**/*.js', '!scripts/dist/*.js' ],
                tasks : [ 'newer:jshint', 'uglify' ],
            },
            livereload : {
                files   : [ 'styles/dist*.css', 'scripts/dist/*.js', '*.html', 'images/*', '*.php' ],
            },
        },
    });
    grunt.registerTask('default', ['openport:watch.options.livereload:35731', 'watch']);
};