# Snowtricks
Bienvenue sur le site communautaire Snowtricks

### Voici comment installer le projet sur votre environnement

## Clonez le projet sur votre espace
git clone https://github.com/ameyabis/P6-Developpez-de-A-Z-le-site-communautaire.git

## Installez composer
Suivez la documentation composer: https://getcomposer.org/

## Configurez votre fichier .env
Créer un fichier .env a partir du fichier .env.dist
Remplire la variable DATA_URL pour pouvoir se connecter à la base de donnéess MYSQL
Remplire la variable JWT_SECRET avec une chaine de caractère
Remplire la variable MAILER_DSN avec votre serveur SMTP

## Installez les dependences manquante
composer install

## Créez la base de données
php bin/console doctrine:database:create

## Ajouter les champs dans les tables
php bin/console doctrine:migrations:migrate

## Ajout des données
php bin/console doctrine:fixtures:load

## Démarer le serveur
symfony server:start
