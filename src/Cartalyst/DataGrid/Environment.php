<?php namespace Cartalyst\DataGrid;
/**
 * Part of the Platform application.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Platform
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\DataGrid\RequestProviders\ProviderInterface as RequestProviderInterface;

class Environment {

	/**
	 * The request instance.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $requestProvider;

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
	 * @param  mixed  $query
	 * @param  array  $columns
	 * @return Cartalyst\DataGrid\DataGrid
	 */
	public function make($query, array $columns)
	{
		$dataGrid = new DataGrid($this, $query, $columns);

		return $dataGrid->setupDataGridContext();
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
		$this->request = $requestProvider;
	}

}