## Install & Configure through Composer

> **Note:** To use Cartalyst's Data Grid package you need to have a valid Cartalyst.com subscription.
Click [here](https://www.cartalyst.com/pricing) to obtain your subscription.

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

If you haven't yet, make sure to require Composer's autoload file in your app root to autoload the installed packages.

	require 'vendor/autoload.php';
