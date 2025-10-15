import { Application } from '@hotwired/stimulus';

const application = Application.start();

// Enregistrer automatiquement tous les contrôleurs présents dans assets/controllers/*_controller.js
// Utilise require.context fourni par webpack
try {
    const context = require.context('./controllers', true, /_controller\.ts$/);
    console.log('🔍 Contrôleurs trouvés:', context.keys());
    context.keys().forEach((key) => {
        const controllerModule = context(key);
        const match = key.match(/^\.\/([a-zA-Z0-9_\-]+)_controller\.ts$/);
        if (!match) return;
        const identifier = match[1];
        const controller = controllerModule.default;
        if (controller) {
            console.log('📝 Enregistrement contrôleur:', identifier);
            application.register(identifier, controller);
        }
    });
    console.log('✅ Contrôleurs enregistrés:', Object.keys(application.controllers));
} catch (e) {
    console.error('❌ Erreur enregistrement contrôleurs:', e);
    // require.context may not be available in some environments; in that case controllers can be registered manually
    // console.warn('Auto-registration des controllers Stimulus non disponible', e);
}

window.Stimulus = application;
