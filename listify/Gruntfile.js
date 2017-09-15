'use strict';
module.exports = function(grunt) {

	grunt.initConfig({

		dirs: {
			js: 'js',
			wp_job_manager: 'inc/integrations/wp-job-manager/js',
			facetwp: 'inc/integrations/facetwp/js',
			woocommerce: 'inc/integrations/woocommerce/js',
			jetpack: 'inc/integrations/jetpack/js'
		},

		watch: {
			options: {
				livereload: 12348,
			},
			js: {
				files: [
					'Gruntfile.js',
					'js/source/**/*.js',
					'js/**/*.coffee',
					'inc/integrations/wp-job-manager/js/*.coffee',
					'inc/integrations/wp-job-manager/js/*.js',
					'inc/integrations/wp-job-manager/js/**/*.coffee',

					// Integrations
					'inc/integrations/**/js/*.js',
					'inc/integrations/**/js/*.coffee',
					'!inc/integrations/**/js/*.min.js',

					// Legacy /source directory.
					'inc/integrations/**/js/source/*.js',
					'inc/integrations/**/js/source/*.coffee',
				],
				tasks: ['coffee', 'uglify']
			},
			css: {
				files: [
					'css/sass/**/*.scss',
					'css/sass/*.scss'
				],
				tasks: ['sass', 'concat', 'cssjanus', 'cssmin' ]
			}
		},

		// uglify to concat, minify, and make source maps
		uglify: {
			dist: {
				options: {
					sourceMap: true
				},
				files: {
					'<%= dirs.wp_job_manager %>/wp-job-manager.min.js': [
						'<%= dirs.wp_job_manager %>/vendor/timepicker/jquery.timepicker.min.js',
						'<%= dirs.wp_job_manager %>/wp-job-manager.js',
						'<%= dirs.wp_job_manager %>/wp-job-manager-gallery.js',
					],
					'<%= dirs.wp_job_manager %>/map/app.min.js': [
						'<%= dirs.wp_job_manager %>/map/vendor/richmarker/richmarker.js',
						'<%= dirs.wp_job_manager %>/map/vendor/infobubble/infobubble.js',
						'<%= dirs.wp_job_manager %>/map/vendor/markerclusterer/markerclusterer.js',
						'<%= dirs.wp_job_manager %>/map/app.js'
					],
					'<%= dirs.wp_job_manager %>/listing/app.min.js': [
						'<%= dirs.wp_job_manager %>/listing/app.js'
					],
					'inc/integrations/wp-job-manager-bookmarks/js/wp-job-manager-bookmarks.min.js': [
						'inc/integrations/wp-job-manager-bookmarks/js/source/wp-job-manager-bookmarks.js'
					],
					'js/app.min.js': [
						'<%= dirs.js %>/vendor/**.js',
						'<%= dirs.js %>/vendor/**/*.js',
						'!<%= dirs.js %>/vendor/salvattore/salvattore.min.js',
						'!<%= dirs.js %>/vendor/flexibility/flexibility.min.js',
						'<%= dirs.wp_job_manager %>/wp-job-manager.min.js',
						'<%= dirs.facetwp %>/source/facetwp.js',
						'<%= dirs.woocommerce %>/source/woocommerce.js',
						'<%= dirs.jetpack %>/jetpack.js',
						'<%= dirs.js %>/source/app.js'
					],

					// Integrations.
					'inc/integrations/wp-job-manager-favorites/js/wp-job-manager-favorites.min.js': [
						'inc/integrations/wp-job-manager-favorites/js/wp-job-manager-favorites.js'
					],
				}
			}
		},

		coffee: {
			dist: {
				options: {
					sourceMap: true,
				},
				files: {
					'<%= dirs.wp_job_manager %>/map/app.js': [
						'<%= dirs.wp_job_manager %>/map/app.coffee'
					],
					'<%= dirs.wp_job_manager %>/listing/app.js': [
						'<%= dirs.wp_job_manager %>/listing/app.coffee'
					],
					'<%= dirs.js %>/admin/widget-features.js': [
						'<%= dirs.js %>/admin/widget-features.coffee'
					],
					'<%= dirs.jetpack %>/jetpack.js': [
						'<%= dirs.jetpack %>/jetpack.coffee'
					]
				}
			}
		},

		jsonlint: {
			dist: {
				src: [ 'inc/setup/import-content/**/*.json' ],
				options: {
					formatter: 'prose'
				}
			}
		},

		sass: {
			dist: {
				files: {
					'css/editor-style.css' : 'css/sass/modules/editor-style.scss',
					'css/style.css' : 'css/sass/style.scss'
				}
			}
		},

		concat: {
			dist: {
				files: {
					'css/style.css': ['css/vendor/*.css', 'css/style.css']
				}
			}
		},

		cssmin: {
			dist: {
				files: {
					'css/style.min.css': [ 'css/style.css' ],
					'css/style.min-rtl.css': [ 'css/style.rtl.css' ]
				}
			}
		},

		clean: {
			dist: {
				src: [
					'css/style.css',
					'css/style.rtl.css'
        ]
			}
		},

		cssjanus: {
			theme: {
				options: {
					swapLtrRtlInUrl: false
				},
				files: [
					{
						src: 'css/style.css',
						dest: 'css/style.rtl.css'
					}
				]
			}
		},

		makepot: {
			theme: {
				options: {
					type: 'wp-theme'
				}
			}
		},

		glotpress_download: {
			theme: {
				options: {
					url: 'https://astoundify.com/glotpress',
					domainPath: 'languages',
					slug: 'listify',
					textdomain: 'listify',
					formats: [ 'mo', 'po' ],
					file_format: '%domainPath%/%wp_locale%.%format%',
					filter: {
						translation_sets: false,
						minimum_percentage: 50,
						waiting_strings: false
					}
				}
			}
		},

		checktextdomain: {
			standard: {
				options:{
					force: true,
					text_domain: 'listify',
					create_report_file: false,
					correct_domain: true,
					keywords: [
						'__:1,2d',
						'_e:1,2d',
						'_x:1,2c,3d',
						'esc_html__:1,2d',
						'esc_html_e:1,2d',
						'esc_html_x:1,2c,3d',
						'esc_attr__:1,2d', 
						'esc_attr_e:1,2d', 
						'esc_attr_x:1,2c,3d', 
						'_ex:1,2c,3d',
						'_n:1,2,4d', 
						'_nx:1,2,4c,5d',
						'_n_noop:1,2,3d',
						'_nx_noop:1,2,3c,4d'
					]
				},
				files: [{
					src: ['**/*.php','!node_modules/**'],
					expand: true,
				}],
			},
		},

		bump: {
			options: {
				files: ['package.json', 'readme.txt', 'style.css'],
				push: false,
				createTag: false,
				commitMessage: 'Version bump %VERSION%',
				commitFiles: ['package.json', 'readme.txt', 'style.css']
			}
		}

	});

	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-coffee' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-cssjanus' );
	grunt.loadNpmTasks( 'grunt-exec' );
	grunt.loadNpmTasks( 'grunt-potomo' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-glotpress' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-bump' );

	// register task
	grunt.registerTask('default', ['watch']);
	grunt.registerTask( 'i18n', [ 'makepot', 'glotpress_download' ] );
	grunt.registerTask( 'clean', ['clean'] );

	grunt.registerTask('build', [ 'jsonlint', 'uglify', 'sass', 'concat', 'cssjanus', 'cssmin', 'i18n', 'clean' ]);
};
