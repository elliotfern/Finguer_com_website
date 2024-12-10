import path from 'path';

export default {
  entry: './src/frontend/main.ts',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist'),
  },
  module: {
    rules: [
      {
        test: /\.ts$/,
        use: 'ts-loader',
        exclude: /node_modules/,
      },

      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'], // Para manejar CSS
      },
    ],
  },
  resolve: {
    extensions: ['.ts', '.js'],
  },
};
