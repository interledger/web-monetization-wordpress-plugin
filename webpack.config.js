const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  // Explicitly set entry points.
  entry: {
    frontend: './src/js/frontend.tsx',
    admin: './src/js/admin.tsx',
    widget: './src/js/banner/index.tsx',
    'banner-style': './src/scss/banner.scss',
  },

  // Optional: customize output or loaders.
  output: {
    ...defaultConfig.output,
    filename: '[name].js',
    path: __dirname + '/build',
  },

  module: {
    ...defaultConfig.module,
    rules: [
      // Replace TS loader manually.
      {
        test: /\.tsx?$/,
        exclude: /node_modules/,
        use: {
          loader: require.resolve('babel-loader'),
          options: {
            presets: [
              require.resolve('@wordpress/babel-preset-default'),
              require.resolve('@babel/preset-typescript'),
            ],
          },
        },
      },
      ...defaultConfig.module.rules,
    ],
  },
  externals: {
    ...defaultConfig.externals, // ✅ Keep WordPress externals like wp.element.
  },
};
