<?php namespace Cartalyst\DataGrid\Handlers;
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

use Cartalyst\DataGrid\DataGrid;

abstract class BaseHandler {

	/**
	 * The shared data grid instance.
	 *
	 * @var Cartalyst\DataGrid\DataGrid
	 */
	protected $dataGrid;

	/**
	 * The data we use.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * The request provider.
	 *
	 * @var Cartalyst\DataGrid\RequestProviders\ProviderInterface
	 */
	protected $request;

	/**
	 * Cached total (unfiltered) count of results.
	 *
	 * @var int
	 */
	protected $totalCount = 0;

	/**
	 * Cached filtered count of results.
	 *
	 * @var int
	 */
	protected $filteredCount = 0;

	/**
	 * Cached current page.
	 *
	 * @var int
	 */
	protected $page = 1;

	/**
	 * Cached number of pages.
	 *
	 * @var int
	 */
	protected $pagesCount = 1;

	/**
	 * Cached previous page.
	 *
	 * @var int|null
	 */
	protected $previousPage;

	/**
	 * Cached next page.
	 *
	 * @var int|null
	 */
	protected $nextPage;

	/**
	 * Cached results.
	 *
	 * @var array
	 */
	protected $results = array();

	/**
	 * Create a new data handler.
	 *
	 * @param  Cartalyst\DataGrid\DataGrid  $dataGrid
	 * @return void
	 */
	public function __construct(DataGrid $dataGrid)
	{
		$this->dataGrid = $dataGrid;
		$this->data     = $this->validateData($dataGrid->getData());
		$this->request  = $this->dataGrid->getEnvironment()->getRequestProvider();
	}

	/**
	 * Validate the data store.
	 *
	 * @param  mixed  $data
	 * @return mixed  $data
	 * @throws Exception
	 */
	abstract public function validateData($data);

}
