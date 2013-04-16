<?php namespace Cartalyst\DataGrid;
/**
 * Part of the Data Grid package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Data Grid
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\DataGrid\RequestProviders\IlluminateProvider;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class DataGridServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerIlluminateRequestProvider();

		$this->registerDataGrid();
	}

	protected function registerIlluminateRequestProvider()
	{
		$this->app['datagrid.request'] = $this->app->share(function($app)
		{
			return new IlluminateProvider($app['request']);
		});
	}

	protected function registerDataGrid()
	{
		$this->app['datagrid'] = $this->app->share(function($app)
		{
			$dataHandlerMappings = array(

				'Cartalyst\DataGrid\DataHandlers\EloquentDataHandler' => function($data)
				{
					return ($data instanceof QueryBuilder or $data instanceof EloquentQueryBuilder);
				},

				// 'Cartalyst\DataGrid\DataHandlers\ArrayDataHandler' => function($data)
				// {
				// 	return is_array($data);
				// },

			);

			return new Environment($app['datagrid.request'], $dataHandlerMappings);
		});
	}

}
