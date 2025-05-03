/* eslint-env node */
module.exports = function ( grunt ) {
	const conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		banana: conf.MessagesDirs,
		stylelint: {
			options: {
				cache: true
			},
			all: [
				'**/*.css',
				'!modules/jquery.ucompare/**',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'banana', 'stylelint' ] );
};
