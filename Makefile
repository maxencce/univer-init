# Makefile pour démarrer l'environnement de dev

# Commandes
SYMFONY_SERVE = symfony serve
NPM_WATCH = npm run watch

# -------------------------------------------------
# Démarrage complet
dev: start-npm-dev start-symfony-dev
	@echo "Environnement de dev démarré !"

# -------------------------------------------------
# Symfony dans le terminal courant
start-symfony-dev:
	@echo "Demarrage du serveur Symfony dans le terminal courant..."
	$(SYMFONY_SERVE)

# -------------------------------------------------
# NPM watch dans une nouvelle fenêtre PowerShell
start-npm-dev:
	@echo "Demarrage de npm watch dans une nouvelle fenetre..."
	start powershell -NoExit -Command "cd '$(CURDIR)'; npm run watch"
