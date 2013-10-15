## Install & Configure in Laravel 4

> **Note:** To use Cartalyst's Data Grid package you need to have a valid Cartalyst.com subscription.
Click [here](https://www.cartalyst.com/pricing) to obtain your subscription.

### 1. Composer {#composer}

---

Open your `composer.json` file and add the following lines:

	{
		"require": {
			"cartalyst/data-grid": "1.0.*"
		},
		"repositories": [
			{
				"type": "composer",
				"url": "http://packages.cartalyst.com"
			}
		],
		"minimum-stability": "stable"
	}

Run a composer update from the command line.

	composer update


### 2. Service Provider {#service-provider}

---

Add the following to the list of service providers in `app/config/app.php`.

	'Cartalyst\DataGrid\DataGridServiceProvider',


### 3. Alias {#alias}

---

Add the following to the list of class aliases in `app/config/app.php`.

	'DataGrid' => 'Cartalyst\DataGrid\Facades\DataGrid',


### 4. Configuration {#configuration}

---

After installing, you can publish the package's configuration file into you application by running the following command:

	php artisan config:publish cartalyst/data-grid

This will publish the config file to `app/config/packages/cartalyst/data-grid/config.php` where you can modify the package configuration.
