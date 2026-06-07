const path = require('path');
const webpack = require('webpack');

const dotenv = require('dotenv');

// SOLO local usa archivo
const isCI = process.env.CI === 'true';

let env = {};

if (!isCI) {
  const result = dotenv.config({ path: '.env.local' });
  env = result.parsed || {};
} else {
  env = process.env; // CI injecta variables
}

const envKeys = Object.keys(env).reduce((acc, key) => {
  acc[`process.env.${key}`] = JSON.stringify(env[key] || '');
  return acc;
}, {});

module.exports = {
  entry: './src/frontend/main.ts',

  output: {
    path: path.resolve(__dirname, 'public/dist'),
    filename: 'bundle.js',
    publicPath: '/dist/',
    clean: true,
  },

  resolve: {
    extensions: ['.ts', '.js'],
  },

  module: {
    rules: [
      {
        test: /\.ts$/,
        exclude: /node_modules/,
        use: 'ts-loader',
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },

  plugins: [new webpack.DefinePlugin(envKeys)],

  devtool: false,
};
