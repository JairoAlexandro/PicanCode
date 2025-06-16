const Encore = require('@symfony/webpack-encore');
const path   = require('path');

// configura entorno si aún no lo está
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  // directorio y ruta pública
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .setManifestKeyPrefix('build/')

  // tu entry principal
  .addEntry('app', './assets/app.js')

  // React, Stimulus, splits, etc.
  .enableReactPreset()
  .enableStimulusBridge('./assets/controllers.json')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()

  // POSTCSS / Tailwind
  .enablePostCssLoader()         // lee postcss.config.cjs

  // —— ESTA LÍNEA ES CRUCIAL ——
  .disableCssExtraction()        // inyecta el CSS en JS, como en `npm run dev`

  // limpieza, notificaciones, sourcemaps y versionado
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())

  // Babel, polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs    = '3.23';
  })

  // alias para React
  .addAliases({
    react:     path.resolve(__dirname, 'node_modules/react'),
    'react-dom': path.resolve(__dirname, 'node_modules/react-dom'),
  })
;

module.exports = Encore.getWebpackConfig();
