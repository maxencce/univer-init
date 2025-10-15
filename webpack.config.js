const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Configure runtime environment if not already configured
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Dossier où seront générés les fichiers compilés
    .setOutputPath('public/build/')
    // Chemin public pour accéder aux fichiers compilés depuis le navigateur
    .setPublicPath('/build')
    //.setManifestKeyPrefix('build/') // Pour les sous-domaines/CDN

    // Entrypoint principal (maintenant en TypeScript)
    .addEntry('app', './assets/app.ts')

    // Séparation des chunks pour optimiser les fichiers
    .splitEntryChunks()

    // Nécessaire pour runtime.js
    .enableSingleRuntimeChunk()

    // Nettoyer le dossier build avant chaque compilation
    .cleanupOutputBeforeBuild()

    // Afficher les sourcemaps uniquement en dev
    .enableSourceMaps(!Encore.isProduction())
    // Ajouter des hash dans les noms de fichiers en prod
    .enableVersioning(Encore.isProduction())

    // Config Babel + polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Activer le support Sass/SCSS
    .enableSassLoader()
    // Activer PostCSS pour TailwindCSS
    .enablePostCssLoader()

    // Activer TypeScript
    .enableTypeScriptLoader()

    .addAliases({
        '@symfony/stimulus-bridge/controllers.json': path.resolve(__dirname, 'assets/controllers.json'),
        '@': path.resolve(__dirname, 'assets'), // Ajout de l'alias manquant
    });

    // Décommenter si React est utilisé
    //.enableReactPreset()

    // Décommenter pour l’intégrité des fichiers
    //.enableIntegrityHashes(Encore.isProduction())

    // Décommenter si jQuery est nécessaire
    //.autoProvidejQuery()
;
module.exports = Encore.getWebpackConfig();