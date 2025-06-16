const path = require('path');

module.exports = {
  content: [
    path.resolve(__dirname, 'assets/**/*.{js,ts,jsx,tsx}'),
    path.resolve(__dirname, 'templates/**/*.twig'),
    path.resolve(__dirname, 'templates/**/*.html.twig'),
  ],
  theme: { extend: {} },
  plugins: [],
  safelist: [
  ],
};
