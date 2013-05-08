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

abstract class BaseHandler implements HandlerInterface {

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
	 * Sets up the data source context.
	 *
	 * @return Cartalyst\DataGrid\Handler\HandlerInterface
	 */
	public function setupDataHandlerContext()
	{
		// Before we apply any filters, we need to setup the total count.
		$this->prepareTotalCount();

		// We'll now setup what columns we will select
		$this->prepareSelect();

		// Apply all the filters requested
		$this->prepareFilters();

		// Setup the requested sorting
		$this->prepareSort();

		// Setup filtered count
		$this->prepareFilteredCount();

		// And we'll setup pagination, pagination
		// is rather unique in the data grid.
		$this->preparePagination();

		return $this;
	}

	/**
	 * Get the total (unfiltered) count
	 * of results.
	 *
	 * @return int
	 */
	public function getTotalCount()
	{
		return $this->totalCount;
	}

	/**
	 * Get the filtered count of results.
	 *
	 * @return int
	 */
	public function getFilteredCount()
	{
		return $this->filteredCount;
	}

	/**
	 * Get the current page we are on.
	 *
	 * @return int
	 */
	public function getPage()
	{
		return $page;
	}

	/**
	 * Get the number of pages.
	 *
	 * @return int
	 */
	public function getPagesCount()
	{
		return $pagesCount;
	}

	/**
	 * Get the previous page.
	 *
	 * @return int|null
	 */
	public function getPreviousPage()
	{
		return $previousPage;
	}

	/**
	 * Get the next page.
	 *
	 * @return int|null
	 */
	public function getNextPage()
	{
		return $nextPage;
	}

	/**
	 * Get the results.
	 *
	 * @return int
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 * Calculates sort from the request.
	 *
	 * @return array
	 */
	public function calculateSort()
	{
		if ( ! $column = $this->request->getSort())
		{
			$columns = $this->dataGrid->getColumns();
			$column = reset($columns);
		}

		// If our column is an alias, we'll use the actual value instead of the
		// alias for sorting.
		if ( ! is_numeric($key = array_search($column, $this->dataGrid->getColumns())))
		{
			$column = $key;
		}

		return array($column, $this->request->getDirection());
	}

	/**
	 * Calculates the pagination for the data grid. We'll try
	 * divide calculate the results per page by dividing the
	 * results count by the requested dividend. If that
	 * result is outside the threshold and the throttle,
	 * we'll adjust it to sit inside the threshold and
	 * throttle. It's rather intelligent.
	 *
	 * We return an array with two values, the first one
	 * being the number of pages, the second one being
	 * the number of results per page.
	 *
	 * @param  int  $resultsCount
	 * @return array
	 */
	public function calculatePagination($resultsCount)
	{
		$dividend  = $this->request->getDividend();
		$threshold = $this->request->getThreshold();
		$throttle  = $this->request->getThrottle();

		if ($dividend < 1)
		{
			throw new \InvalidArgumentException("Invalid dividend of [$dividend], must be [1] or more.");
		}

		if ($threshold < 1)
		{
			throw new \InvalidArgumentException("Invalid threshold of [$threshold], must be [1] or more.");
		}

		if ($throttle < $threshold)
		{
			throw new \InvalidArgumentException("Invalid throttle of [$throttle], must be greater than the threshold, which is [$threshold].");
		}

		// If our results count is less than the threshold,
		// we're always returning one page with all of the items
		// on it. This will effectively remove pagination.
		if ($resultsCount < $threshold)
		{
			return array(1, $resultsCount);
		}

		// Firstly, we'll calculate the "per page" property
		// based off the dividend.
		$perPage = (int) ceil($resultsCount / $dividend);

		// Now, we'll calculate the maximum per page, which is the throttle
		// divided by the dividend.
		$maximumPerPage = floor($throttle / $dividend);

		// Now, if the results per page is greater than the
		// maximum per page, reduce it down accordingly
		if ($perPage > $maximumPerPage)
		{
			$perPage = $maximumPerPage;
		}

		// To work out the number of pages, we'll just
		// divide the results count by the number of
		// results per page. Simple!
		$pagesCount = ceil($resultsCount / $perPage);

		return array($pagesCount, $perPage);
	}

	/**
	 * Calculates the page, common logic used in multiple handlers.
	 *
	 * Returns the current page,
	 *
	 * @param  int  $resultsCount
	 * @param  int  $perPage
	 * @return array
	 */
	public function calculatePages($resultsCount, $perPage)
	{
		$page = $this->request->getPage();

		// Now we will generate the previous and next page links
		if ($page > 1)
		{
			if (($page * $perPage) <= $resultsCount)
			{
				$previousPage = $page - 1;
			}
			else
			{
				$previousPage = $pagesCount;
			}
		}
		if (($page * $perPage) < $resultsCount)
		{
			$nextPage = $page + 1;
		}

		return array($page, $previousPage, $nextPage);
	}

	/**
	 * Validate the data store.
	 *
	 * @param  mixed  $data
	 * @return mixed  $data
	 * @throws Exception
	 */
	abstract public function validateData($data);

	/**
	 * Prepares the total count of results before
	 * we apply filters.
	 *
	 * @return void
	 */
	abstract public function prepareTotalCount();

	/**
	 * Prepares the "select" component of the statement
	 * based on the columns array provided.
	 *
	 * @return void
	 */
	abstract public function prepareSelect();

	/**
	 * Loops through all filters provided in the data
	 * and manipulates the data.
	 *
	 * @return void
	 */
	abstract public function prepareFilters();

	/**
	 * Sets up the filtered results count (before pagination).
	 *
	 * @return void
	 */
	abstract public function prepareFilteredCount();

	/**
	 * Sets up the sorting for the data.
	 *
	 * @return void
	 */
	abstract public function prepareSort();

	/**
	 * Sets up pagination for the data grid. Our pagination
	 * is special, see calculatePerPage() for more information.
	 *
	 * @return void
	 */
	abstract public function preparePagination();

}
