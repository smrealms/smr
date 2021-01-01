module.exports = function(grunt) {

    grunt.initConfig({
        uglify: {
            target: {
                files: [{
                    expand: true,
                    src: 'src/htdocs/js/**/*.js',
                }]
            }
        },

        cssmin: {
            target: {
                files: [{
                    expand: true,
                    src: 'src/htdocs/css/**/*.css',
                }]
            }
        },

        cacheBust: {
            options: {
                deleteOriginals: true,
                baseDir: 'src/htdocs/',
                assets: ['css/**/*.css', 'js/**/*.js'],
            },
            taskName: {
                files: [{
                    expand: true,
                    src: [
                        'src/**/*.php',
                        'src/htdocs/**/*.html',
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
