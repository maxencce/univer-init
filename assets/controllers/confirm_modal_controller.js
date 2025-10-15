import { Controller } from '@hotwired/stimulus'

/**
 * Contrôleur Stimulus pour gérer une fenêtre modale de confirmation.
 * Utilisation :
 *  - Chaque bouton ou formulaire ajoute data-action="submit->confirm-modal#open" ou "click->confirm-modal#open"
 *  - Les attributs data-confirm-modal-* définissent le contenu dynamique
 */
export default class extends Controller {
    static targets = ['backdrop', 'title', 'text', 'form', 'token', 'confirmButton', 'cancelButton']
    static values = {
        title: String,
        message: String,
        confirmText: String,
        confirmClass: String
    }

    connect() {
        this._onEsc = this._onEsc.bind(this)
    }

    /**
     * Ouvre la modale et configure son contenu selon les data-* du bouton ou formulaire déclencheur.
     */
    open(event) {
        event.preventDefault()

        const trigger = event.currentTarget
        const actionUrl = trigger.action || trigger.dataset.actionUrl
        const csrfToken = trigger.querySelector('input[name="_token"]')?.value || trigger.dataset.csrfToken || ''

        // Configure les textes
        this.titleTarget.textContent = this.titleValue || trigger.dataset.confirmModalTitleValue || 'Confirmer'
        this.textTarget.textContent = this.messageValue || trigger.dataset.confirmModalMessageValue || 'Êtes-vous sûr ?'
        this.formTarget.action = actionUrl
        this.tokenTarget.value = csrfToken
        this.confirmButtonTarget.textContent =
            this.confirmTextValue || trigger.dataset.confirmModalConfirmTextValue || 'Confirmer'
        this.confirmButtonTarget.className =
            this.confirmClassValue ||
            trigger.dataset.confirmModalConfirmClassValue ||
            'inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700'

        // Affiche la modale
        this.backdropTarget.classList.remove('hidden')
        this.backdropTarget.classList.add('flex')

        // Focus sur le bouton d'annulation
        setTimeout(() => this.cancelButtonTarget.focus(), 0)

        document.addEventListener('keydown', this._onEsc)
    }

    /**
     * Ferme la modale.
     */
    close() {
        this.backdropTarget.classList.add('hidden')
        this.backdropTarget.classList.remove('flex')
        document.removeEventListener('keydown', this._onEsc)
    }

    cancel(event) {
        event?.preventDefault()
        this.close()
    }

    backdropClick(event) {
        if (event.target === this.backdropTarget) {
            this.close()
        }
    }

    _onEsc(event) {
        if (event.key === 'Escape') {
            this.close()
        }
    }
}
