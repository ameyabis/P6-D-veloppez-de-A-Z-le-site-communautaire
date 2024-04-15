# Snowtricks
Bienvenue sur le site communautaire Snowtricks

### Voici comment installer le projet sur votre environnement

## Clonez le projet sur votre espace
git clone https://github.com/ameyabis/P6-Developpez-de-A-Z-le-site-communautaire.git

## Installez composer
php composer.phar

## Configurez votre fichier .env

## Installez les dependences manquante
composer update

## Créez la base de données
php bin/console doctrine:schema:create nom_de_la_base_de_données

## Ajouter les champs dans les tables
php bin/console doctrine:migrations:migrate

## Ajout des données
php bin/console doctrine:fixtures:load
