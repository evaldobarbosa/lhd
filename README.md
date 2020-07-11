# Laravel App to Heroku

## Installation

```
composer require evaldobarbosa/lhd
```

## Usage

### Creating or updating file app.json

First copy your .env to .env.production file and change all values you need. So...

To fill app.json file, you should run this command:

```
php artisan heroku:app-from-env
```

After you can deploy your application on Heroku running this command:

```
php artisan heroku:deploy
```