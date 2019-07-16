const Encore = require('@symfony/webpack-encore');
const path = require('path');
const getEzConfig = require('./ez.webpack.config.js');
const eZConfigManager = require('./ez.webpack.config.manager.js');
const eZConfig = getEzConfig(Encore);
const customConfigs = require('./ez.webpack.custom.configs.js');

Encore.reset();
Encore.setOutputPath('web/assets/build')
    .setPublicPath('/assets/build')
    .enableSassLoader()
    .enableReactPreset()
    .enableSingleRuntimeChunk();

Encore.addEntry('ezp-com', [
    path.resolve(__dirname, './web/assets/scss/page.scss'),
    path.resolve(__dirname, './web/assets/js/prism.js'),
    path.resolve(__dirname, './web/assets/js/stringUtils.helper.js'),
    path.resolve(__dirname, './web/assets/js/GoogleAnalyticsService.js'),
    path.resolve(__dirname, './web/assets/js/app.js'),
]);

const projectConfig = Encore.getWebpackConfig();
module.exports = [ eZConfig, ...customConfigs, projectConfig ];
