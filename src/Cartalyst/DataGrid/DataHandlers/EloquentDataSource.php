<?php namespace Cartalyst\DataGrid\DataHandlers;
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
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class EloquentDataHandler implements DataHandlerInterface {

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

	protected $totalCount = 0;

	protected $filteredCount;
	protected $page = 1;
	protected $pagesCount = 1;
	protected $previousPage;
	protected $nextPage;

	/**
	 * Create a new data source.
	 *
	 * @param  Cartalyst\DataGrid\DataGrid  $dataGrid
	 * @return void
	 */
	public function __construct(DataGrid $dataGrid, $data)
	{
		$data = $dataGrid->getData();

		// We accept different data types for our data grid,
		// let's just check now that
		if ( ! $data instanceof QueryBuilder and ! $data instanceof EloquentQueryBuilder)
		{
			throw new \InvalidArgumentException("Invalid query passed to Eloquent Data Source.");
		}

		$this->dataGrid = $dataGrid;
		$this->data     = $data;
		$this->request  = $this->dataGrid->getEnvironment()->getRequestProvider();
	}

	/**
	 * Sets up the data source context.
	 *
	 * @return Cartalyst\DataGrid\DataHandler\DataHandlerInterface
	 */
	public function setupDataHandlerContext()
	{
		// Before we apply any filters, we need to setup the total count.
		$this->setupTotalCount();

		// We'll now setup what columns we will select
		$this->setupSelect();

		// Apply all the filters requested
		$this->setupFilters();

		// Setup the requested sorting
		$this->setupSort();

		// Setup filtered count
		$this->setupFilteredCount();

		// And we'll setup pagination, pagination
		// is rather unique in the data grid.
		$this->setupPagination();

		// Hydrate our results
		$this->hydrate();

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
		return $this->page;
	}

	/**
	 * Get the number of pages.
	 *
	 * @return int
	 */
	public function getPagesCount()
	{
		return $this->pagesCount;
	}

	/**
	 * Get the previous page.
	 *
	 * @return int|null
	 */
	public function getPreviousPage()
	{
		return $this->previousPage;
	}

	/**
	 * Get the next page.
	 *
	 * @return int|null
	 */
	public function getNextPage()
	{
		return $this->nextPage;
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

	public function setupTotalCount()
	{
		$this->totalCount = (int) $this->data->count();
	}

	/**
	 * Prepares the "select" component of the statement
	 * based on the columns array provided.
	 *
	 * @return void
	 */
	public function setupSelect()
	{
		// Fallback array to select
		$toSelect = array();

		// Loop through columns and inspect whether
		// they are an alias or not. If the key is
		// not numeric, it is the real column name
		// and the value is the alias. Otherwise, there
		// is no alias and we're dealing directly with
		// the column name. Aliases are used quite often
		// for joined tables.
		foreach ($this->columns as $key => $value)
		{
			if (is_numeric($key))
			{
				$toSelect[] = $value;
			}
			else
			{
				$toSelect[] = "$key as $value";
			}
		}

		$this->data->select($toSelect);
	}

	/**
	 * Loops through all filters provided in the data
	 * and manipulates the data.
	 *
	 * @return void
	 */
	public function setupFilters()
	{
		foreach ((array) $this->getInput('filters', array()) as $filter)
		{
			// If the filter is an array where the key matches one of our
			// columns, we're filtering that column.
			if (is_array($filter))
			{
				$filterValue  = reset($filter);
				$filterColumn = key($filter);

				if (($index = array_search($filterColumn, $this->columns)) !== false)
				{
					if (is_numeric($index))
					{
						$this->data->where(
							$filterColumn,
							'like',
							"%{$filterValue}%"
						);
					}
					else
					{
						$this->data->where(
							$index,
							'like',
							"%{$filterValue}%"
						);
					}
				}
			}

			// Otherwise if a string was provided, the
			// filter is an "or where" filter across all
			// columns.
			elseif (is_string($filter))
			{
				$me = $this;
				$this->data->whereNested(function($data) use ($me, $filter)
				{
					$me->globalFilter($data, $filter);
				});
			}
		}
	}

	/**
	 * Applies a global filter across all registered columns. The
	 * filter is applied in a "or where" fashion, where
	 * the value can be matched across any column.
	 *
	 * @param  Illuminate\Database\Eloquent\Builder  $nestedQuery
	 * @param  string  $filter
	 * @return void
	 */
	public function globalFilter(QueryBuilder $nestedQuery, $filter)
	{
		foreach ($this->columns as $key => $value)
		{
			if (is_numeric($key))
			{
				$nestedQuery->orWhere($value, 'like', "%{$filter}%");
			}
			else
			{
				$nestedQuery->orWhere($key, 'like', "%{$filter}%");
			}
		}
	}

	/**
	 * Sets up the filtered results count (before pagination).
	 *
	 * @return void
	 */
	public function setupFilteredCount()
	{
		$this->filteredCount = (int) $this->data->count();
	}

	/**
	 * Sets up the sorting for the data.
	 *
	 * @return void
	 */
	public function setupSort()
	{
		if ( ! is_numeric($key = array_search($column = $this->getInput('sort', reset($this->columns)), $this->columns)))
		{
			$column = $key;
		}

		$direction = $this->request->getDirection();

		$data = ($this->data instanceof EloquentQueryBuilder) ? $this->data->getQuery() : $this->data;

		// We are going to prepend our sort order to the data
		// as SQL allows for multiple sort. By appending it, a predefined
		// sort may override ours.
		if (is_array($data->orders))
		{
			array_unshift($data->orders, compact('column', 'direction'));
		}

		// If no orders have been defined, the orders property
		// is set to null. At this point, we cannot unshift a
		// sort order to the front, so we will use the API.
		else
		{
			$data->orderBy($column, $direction);
		}
	}

	/**
	 * Sets up pagination for the data grid. Our pagination
	 * is special, see calculatePerPage() for more information.
	 *
	 * @return void
	 */
	public function setupPagination()
	{
		// If our filtered results are zero, let's not set any pagination
		if (($filteredResultsCount = $this->filteredCount) == 0)
		{
			return;
		}

		$this->page = $page = (int) $this->getInput('page', 1);

		if ($page < 1)
		{
			throw new \InvalidArgumentException("Invalid page [$page] given. Page must be greater than or equal to [1].");
		}

		list($pagesCount, $perPage) = $this->calculatePagination(
			$filteredResultsCount,
			$requestedPages = (int) $this->getInput('requested_pages', 10),
			$minimumPerPage = (int) $this->getInput('minimum_per_page', 10)
		);

		$this->pagesCount = $pagesCount;

		// Now we will generate the previous and next page links
		if (($this->page = $page) > 1)
		{
			if (($page * $perPage) <= $this->filteredCount)
			{
				$this->previousPage = $page - 1;
			}
			else
			{
				$this->previousPage = $pagesCount;
			}
		}
		if (($page * $perPage) < $this->filteredCount)
		{
			$this->nextPage = $page + 1;
		}

		$this->data->forPage($page, $perPage);
	}

	/**
	 * Calulate how many results should be returned
	 * per page, based off the requested number of
	 * pages in the pagination vs the minimum results
	 * per page as well as how many pages are to be used.
	 *
	 * If the requested number of pages leaves more items
	 * per page than the minimum per page, we will return
	 * the items from the requested page. For example, if
	 * there are 200 items, with 10 requested pages, we'll
	 * return 20 items per page.
	 *
	 * Otherwise, we'll fall back to the minimum results
	 * per page.
	 *
	 * We will return an array, the number of pages as well
	 * as the number per page.
	 *
	 * @param  int  $resultsCount
	 * @param  int  $requestedPages
	 * @param  int  $minimumPerPage
	 * @return array
	 */
	public function calculatePagination($resultsCount, $requestedPages = 10, $minimumPerPage = 10)
	{
		if ($requestedPages == 0)
		{
			throw new \InvalidArgumentException("Cannot divide by zero (requested pages was zero).");
		}

		if (($actualPerPage = ceil($resultsCount / $requestedPages)) < $minimumPerPage)
		{
			$actualPerPage = $minimumPerPage;
		}

		return array((int) ceil($resultsCount / $actualPerPage), (int) $actualPerPage);
	}

	/**
	 * Hydrates the results component of the output.
	 *
	 * @return void
	 */
	public function hydrate()
	{
		// The data builder class can be setup to
		// return results that are not arrays, but
		// instances of objects. We will cast as an
		// array now so that the results object is an array.
		if (($results = $this->data->get()) instanceof ArrayableInterface)
		{
			$results = $results->toArray();
		}

		// Now we return our results in an array form
		$this->response['results'] = array_map(function($result)
		{
			// The same goes for any result returned,
			// we'll see if we can call toArray() on it
			// or not.
			if ($result instanceof ArrayableInterface)
			{
				return $result->toArray();
			}

			// Fallback to casting as an array
			return (array) $result;

		}, (array) $results);
	}

}
