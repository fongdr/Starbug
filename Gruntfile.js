/*global module:false*/
module.exports = function(grunt) {

	var db = grunt.file.readJSON('app/etc/db/default.json');
	var local = {
		title: "Local",
		database:db.db,
		user:db.username,
		pass:db.password,
		host:db.host
	};

	// Project configuration.
	grunt.initConfig({
		phplint: {
			all: ['app/**/*.php', 'modules/**/*.php', 'core/**/*.php']
		},
		jsvalidate: {
			all: {
				files: {
					src:['app/**/*.js', 'core/**/*.js', 'modules/**/*.js']
				}
			}
		},
		jshint: {
			options: {
				force: true,
				eqeqeq: true,
				immed: true,
				latedef: true,
				noarg: true,
				undef: true,
				browser: true,
				unused: true,
				eqnull: true,
				globals: {
					define: true,
					require: true,
					WEBSITE_URL:true
				}
			},
			local: {
				src: ['.']
			},
			ci: {
				src: ['.'],
				options:{
					reporter:'jslint',
					reporterOutput:'build/logs/jslint.xml'
				}
			}
		},
		lesslint: {
			src: ['app/**/custom-screen.less'],
			options: {
				failOnError: false,
				csslint:{
					'adjoining-classes': false
				},
				formatters: [{id: 'lint-xml', dest: 'build/logs/lesslint.xml'}]
			}
		},
		phploc: {
			default: {
				dir: 'app core modules'
			},
			options: {
				bin: 'vendor/bin/phploc',
				countTests: true,
				reportFileCSV: 'build/logs/phploc.csv',
				reportFileXML: 'build/logs/phploc.xml',
				progress: true,
				quiet: false
			}
		},
		shell: {
			'phpcpd': {
				command: 'vendor/bin/phpcpd --log-pmd build/logs/pmd-cpd.xml app core modules || true'
			}
		},
		phpmd: {
			local: {
				dir: '.',
				options: {
					bin: 'vendor/bin/phpmd',
					reportFormat: 'text',
					exclude:'libraries,var,node_modules,vendor',
					rulesets:'etc/phpmd.xml'
				}
			},
			ci: {
				dir: '.',
				options: {
					reportFormat: 'xml',
					reportFile: 'build/logs/phpmd.xml',
					exclude: 'libraries,var,node_modules,vendor',
					rulesets: 'etc/phpmd.xml'
				}
			}
		},
		phpcs: {
			local: {
				dir: ['core', 'modules', 'app'],
				options: {
					bin: 'vendor/bin/phpcs',
					extensions: 'php',
					ignore: 'views,templates,layouts',
					standard: 'etc/phpcs.xml',
					ignoreExitCode: true
				}
			},
			ci: {
				dir: ['core', 'app', 'modules'],
				options: {
					extensions: 'php',
					ignore: 'views,templates,layouts,forms',
					standard: 'etc/phpcs.xml',
					ignoreExitCode: true,
					report: 'checkstyle',
					reportFile: 'build/logs/checkstyle.xml'
				}
			}
		},
		phpunit: {
			all: {
				options: {
					bin: 'vendor/bin/phpunit',
					configuration: "etc/phpunit.xml",
					execMaxBuffer: Infinity
				}
			}
		},
		intern: {
			local: {
				 options: {
					runType: 'runner',
					config: 'core/app/public/js/tests/intern'
				 }
			},
			ci: {
				 options: {
					 runType: 'runner',
					 reporters: ['cobertura'],
					 config: 'core/app/public/js/tests/intern'
				 }
			}
		},
		deployments: {
			options:{
				backups_dir: "backups"
			},
			local:local
		},
		rsync: {
			options: {
				args: ["--verbose"],
				exclude: [".git", "node_modules"],
				recursive: true
			},
			dev: {
				options: {
					ssh:true,
					src:"remote:/path/to/app/public/uploads/",
					dest:"./app/public/uploads/",
					delete:true
				}
			}
		},
		less: {
			"starbug-1": {
				files: {
					"app/themes/starbug-1/public/stylesheets/dist/screen.css": "app/themes/starbug-1/public/stylesheets/src/screen.less"
				}
			},
			storm: {
				files: {
					"app/themes/storm/public/stylesheets/dist/screen.css": "app/themes/storm/public/stylesheets/src/screen.less"
				}
			},
			semantic: {
				files: {
					"app/themes/semantic/public/stylesheets/dist/screen.css": "app/themes/semantic/public/stylesheets/src/screen.less"
				}
			}
		},
		postcss: {
			options: {
				processors: [
					require("lost")(),
					require("postcss-cssnext")(),
					require("cssnano")({autoprefixer: false, discardComments:{removeAll:true}})
				]
			},
			"starbug-1": {
				src: "app/themes/starbug-1/public/stylesheets/dist/*.css"
			},
			storm: {
				src: "app/themes/storm/public/stylesheets/dist/*.css"
			}
		},
		watch: {
			"starbug-1": {
				files: ["app/themes/starbug-1/public/stylesheets/src/**/*"],
				tasks: ["css:starbug-1"]
			},
			storm: {
				files: ["app/themes/storm/public/stylesheets/src/**/*"],
				tasks: ["css:storm"]
			},
			semantic: {
				files: ["app/themes/semantic/public/stylesheets/src/**/*"],
				tasks: ["css:semantic"]
			}
		}
	});

	// These plugins provide necessary tasks.
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-deployments');
	grunt.loadNpmTasks('grunt-jsvalidate');
	grunt.loadNpmTasks('grunt-lesslint');
	grunt.loadNpmTasks('grunt-phpcs');
	grunt.loadNpmTasks('grunt-phplint');
	grunt.loadNpmTasks('grunt-phploc');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-phpmd');
	grunt.loadNpmTasks('grunt-phpunit');
	grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-rsync');
	grunt.loadNpmTasks('intern');

	grunt.registerTask('lint', ['phplint', 'jsvalidate', 'jshint:local', 'lesslint']);
	grunt.registerTask('lint-ci', ['phplint', 'jsvalidate', 'jshint:ci', 'lesslint']);

	grunt.registerTask('local', ['lint', 'phploc', 'phpmd:local', 'phpcs:local', 'shell:phpcpd', 'phpunit', 'intern:local']);
	grunt.registerTask('ci', ['lint-ci', 'phploc', 'phpmd:ci', 'phpcs:ci', 'shell:phpcpd', 'phpunit', 'intern:ci']);

	grunt.registerTask('default', ['local']);

	grunt.registerTask('css', function(target) {
		grunt.task.run.apply(grunt.task, ['less', 'postcss'].map(function(task) {
			return (target == null) ? task : task + ':' + target
		}));
	});
	grunt.registerTask('build', function(target) {
		grunt.task.run.apply(grunt.task, ['css'].map(function(task) {
			return (target == null) ? task : task + ':' + target
		}));
	});
};
