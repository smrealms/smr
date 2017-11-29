module.exports = function(grunt) {

    grunt.initConfig({
        uglify: {
            target: {
                files: [{
                    expand: true,
                    src: 'htdocs/js/**/*.js',
                }]
            }
        },

        cssmin: {
            target: {
                files: [{
                    expand: true,
                    src: 'htdocs/css/**/*.css',
                }]
            }
        },

        cacheBust: {
            options: {
                deleteOriginals: true,
                baseDir: 'htdocs/',
                assets: ['css/**/*.css', 'js/**/*.js'],
            },
            taskName: {
                files: [{
                    expand: true,
                    src: [
                        'templates/**/*.php',
                        'templates/**/*.inc',
                        'htdocs/**/*.php',
                        'htdocs/**/*.html',
                    ],
                }]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-cache-bust');

    grunt.registerTask('default', ['uglify', 'cssmin', 'cacheBust']);
};
