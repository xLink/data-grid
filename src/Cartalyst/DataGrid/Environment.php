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
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\DataGrid\RequestProviders\ProviderInterface as RequestProviderInterface;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Environment {

	/**
	 * The request instance.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $requestProvider;

	/**
	 * Array of data source mappings for data types,
	 * where the key is the applicable class and the
	 * value is a closure which determines if the class
	 * is applicable for the data type.
	 *
	 * @var array
	 */
	protected $dataHandlerMappings = array(

		'Cartalyst\DataGrid\DataHandlers\EloquentDataHandler' => function($data)
		{
			return ($data instanceof QueryBuilder or $data instanceof EloquentQueryBuilder);
		},

		'Cartalyst\DataGrid\DataHandlers\ArrayDataHandler' => function($data)
		{
			return is_array($data);
		},

	);

	/**
	 * Create a new pagination environment.
	 *
	 * @param  Cartalyst\DataGrid\RequestProviders\ProviderInterface  $requestProvider
	 * @return void
	 */
	public function __construct(RequestProviderInterface $requestProvider)
	{
		$this->requestProvider = $requestProvider;
	}

	/**
	 * Show a new data grid instance.
	 *
	 * @param  mixed  $dataHandler
	 * @param  array  $columns
	 * @return Cartalyst\DataGrid\DataGrid
	 */
	public function make($dataHandler, array $columns)
	{
		return $this->createDataGrid($dataHandler, $columns)->setupDataGridContext();
	}

	/**
	 * Creates a new instance of the data grid.
	 *
	 * @param  mixed  $dataHandler
	 * @param  array  $columns
	 * @return Cartalyst\DataGrid\DataGrid
	 */
	public function createDataGrid($dataHandler, array $columns)
	{
		return new DataGrid($this, $dataHandler, $columns);
	}

	/**
	 * Get the active request instance.
	 *
	 * @return Cartalyst\DataGrid\RequestProviders\ProviderInterface
	 */
	public function getRequestProvider()
	{
		return $this->requestProvider;
	}

	/**
	 * Set the active request instance.
	 *
	 * @param  Cartalyst\DataGrid\RequestProviders\ProviderInterface  $requestProvider
	 * @return void
	 */
	public function setRequestProvider(RequestProviderInterface $requestProvider)
	{
		$this->requestProvider = $requestProvider;
	}

	public function getDataHandlerMappings()
	{
		return $this->dataHandlerMappings;
	}

}
