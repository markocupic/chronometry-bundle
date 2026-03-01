const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/markocupicchronometry')
    .setManifestKeyPrefix('')
    .addEntry('app', './assets/js/app.js')
    .enableVueLoader()
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps()
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .enablePostCssLoader()
    // Preprocessing scss in css
    .enableSassLoader()
    .enablePostCssLoader()
    .addStyleEntry('styles', './assets/scss/main.scss')
;

module.exports = Encore.getWebpackConfig();
