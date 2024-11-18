const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    entry: './src/block.js', // Ensure this points to your main source file
    output: {
        path: __dirname + '/build',
        filename: 'block.js',
    },
};