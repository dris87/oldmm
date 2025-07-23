var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .autoProvideVariables({
        "window.jQuery": "jquery",
    })
    .enableSassLoader()
    .addEntry('js/main', './app/Resources/assets/ts/main.ts')
    .enableVersioning(false)
    .createSharedEntry('js/common', ['jquery'])
    .addStyleEntry('css/app', ['./app/Resources/assets/scss/app.scss'])
    .enableTypeScriptLoader(function (typeScriptConfigOptions) {});

var config = Encore.getWebpackConfig();
config.node = { fs: 'empty' };
module.exports = config;
