# 🎬 Streamyz - Plateforme de Streaming de Films

## 📝 Description
Streamyz est une application web inspirée d'Allociné permettant de découvrir, rechercher et gérer des films en favoris. Ce projet est développé avec Symfony pour le back-end et Twig + Tailwind CSS pour le front-end. Il utilise l'API TMDb pour obtenir un catalogue de films actualisé.

## 🚀 Fonctionnalités

* 🎥 **Affichage des films populaires** - Découvrez les films tendances du moment
* 🔍 **Recherche de films** - Trouvez facilement vos films préférés par titre
* 🎭 **Filtrage par genre** - Explorez les films selon vos genres préférés
* ❤️ **Gestion des favoris** - Ajoutez et supprimez des films de votre liste personnelle
* 🛠 **Persistance des données** - Sauvegarde des favoris dans une base SQLite
* 📜 **Historique de navigation** - Suivez votre parcours de découverte

## 🛠️ Installation et Configuration

### Prérequis

* PHP 8.1+
* Composer
* Symfony CLI
* SQLite (ou autre SGBD compatible avec Doctrine)

### Installation

1. Clonez le projet :
```bash
git clone https://github.com/votre-repo/streamyz.git
cd streamyz
```

2. Installez les dépendances :
```bash
composer install
```

3. Configurez votre base de données dans `.env` :
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

4. Créez la base de données :
```bash
symfony console doctrine:database:create
```

5. Générez et appliquez les migrations :
```bash
symfony console make:migration
symfony console doctrine:migrations:migrate -n
```

6. Lancez le serveur Symfony :
```bash
symfony server:start
```

7. Accédez à l'application sur http://127.0.0.1:8000

## 🖥️ Technologies utilisées

* **Back-end** : Symfony, Doctrine ORM
* **Front-end** : Twig, Tailwind CSS
* **Base de données** : SQLite
* **API externe** : TMDb API

## 👥 Équipe

Ce projet a été développé par :

* [Janon](https://github.com/Ewillian9)
* [Chimène](https://github.com/ND-Chimene)
* [Marvin](https://github.com/MarvinDlls)

## 📜 Licence

Ce projet est sous licence MIT.