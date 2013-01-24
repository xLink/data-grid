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

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DataGrid implements ArrayableInterface, JsonableInterface {

	/**
	 * The data grid environment.
	 *
	 * @var Cartalyst\DataGrid\Environment
	 */
	protected $env;

	/**
	 * The query object passed to the
	 * datagrid, used for manipulation
	 * and returning of results.
	 *
	 * @var mixed
	 */
	protected $query;

	/**
	 * Array of columns presented in the
	 * data-grid. The values of this array
	 * should match the properties (or indexes)
	 * of each result returned from the query.
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * The response to be served when the data grid
	 * is cast to an array or string. This is manipulated
	 * through methods defined in this class.
	 *
	 * @var array
	 */
	protected $response = array(

		// An array of results to be returned.
		'results' => array(),

		// The counts provided
		'counts' => array(
			'total'    => 0,
			'filtered' => 0,
		),

		// Pagination
		'pagination' => array(
			'page'             => 1,
			'requested_pages'  => 10,
			'minimum_per_page' => 10,
			'total_pages'      => 1,
			'previous_page'    => false,
			'next_page'        => false,
		),

		// Sorting
		'sort' => array(
			'column'    => null,
			'direction' => 'asc',
		),
	);

	/**
	 * Creates a new data grid object.
	 *
	 * @param  Cartalyst\DataGrid\Environment  $env
	 * @param  mixed  $query
	 * @param  array  $columns
	 * @return void
	 */
	public function __construct(Environment $env, $query, array $columns)
	{
		// We accept different query types for our data grid,
		// let's just check now that
		if ( ! $query instanceof QueryBuilder and ! $query instanceof EloquentQueryBuilder)
		{
			throw new \InvalidArgumentException("Invalid query object passed to Data Grid.");
		}

		$this->env     = $env;
		$this->query   = $query;
		$this->columns = $columns;
	}

	/**
	 * Sets up the data grid context (with filters, ordering,
	 * searching etc).
	 *
	 * This method simply calls a bunch of other methods. The
	 * way SQL works means the order we call the methods in
	 * matters.
	 *
	 * @return Cartalyst\DataGrid\DataGrid
	 */
	public function setupDataGridContext()
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

	public function setupTotalCount()
	{
		$this->response['counts']['total'] = (int) $this->query->count();
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

		$this->query->select($toSelect);
	}

	/**
	 * Loops through all filters provided in the query
	 * and manipulates the query.
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
						$this->query->where(
							$filterColumn,
							'like',
							"%{$filterValue}%"
						);
					}
					else
					{
						$this->query->where(
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
				$this->query->whereNested(function($query) use ($me, $filter)
				{
					$me->globalFilter($query, $filter);
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
		$this->response['counts']['filtered'] = (int) $this->query->count();
	}

	/**
	 * Sets up the sorting for the query.
	 *
	 * @return void
	 */
	public function setupSort()
	{
		if ( ! is_numeric($key = array_search($column = $this->getInput('sort', reset($this->columns)), $this->columns)))
		{
			$column = $key;
		}

		$this->response['sort']['column']    = $column;
		$this->response['sort']['direction'] = $direction = $this->getInput('direction', 'asc');

		$query = ($this->query instanceof EloquentQueryBuilder) ? $this->query->getQuery() : $this->query;

		// We are going to prepend our sort order to the query
		// as SQL allows for multiple sort. By appending it, a predefined
		// sort may override ours.
		if (is_array($query->orders))
		{
			array_unshift($query->orders, compact('column', 'direction'));
		}

		// If no orders have been defined, the orders property
		// is set to null. At this point, we cannot unshift a
		// sort order to the front, so we will use the API.
		else
		{
			$query->orderBy($column, $direction);
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
		if (($filteredResultsCount = $this->response['counts']['filtered']) == 0)
		{
			return;
		}

		$this->response['pagination']['page'] = $page = (int) $this->getInput('page', 1);

		if ($page < 1)
		{
			throw new \InvalidArgumentException("Invalid page [$page] given. Page must be greater than or equal to [1].");
		}

		list($totalPages, $perPage) = $this->calculatePagination(
			$filteredResultsCount,
			$this->response['pagination']['requested_pages'] = $requestedPages = (int) $this->getInput('requested_pages', 10),
			$this->response['pagination']['minimum_per_page'] = $minimumPerPage = (int) $this->getInput('minimum_per_page', 10)
		);

		$this->response['pagination']['total_pages'] = $totalPages;

		// Now we will generate the previous and next page links
		if (($this->response['pagination']['page'] = $page) > 1)
		{
			if (($page * $perPage) <= $this->response['counts']['filtered'])
			{
				$this->response['pagination']['previous_page'] = $page - 1;
			}
			else
			{
				$this->response['pagination']['previous_page'] = $totalPages;
			}
		}
		if (($page * $perPage) < $this->response['counts']['filtered'])
		{
			$this->response['pagination']['next_page'] = $page + 1;
		}

		$this->query->forPage($page, $perPage);
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
		// The query builder class can be setup to
		// return results that are not arrays, but
		// instances of objects. We will cast as an
		// array now so that the results object is an array.
		if (($results = $this->query->get()) instanceof ArrayableInterface)
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

	/**
	 * Proxy for getting input from the environment's request provider.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return string
	 */
	public function getInput($key, $default = null)
	{
		return $this->env->getRequestProvider()->input($key, $default);
	}

	/**
	 * Returns the query used in the data grid.
	 *
	 * @return mixed
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Gets the data grid response.
	 *
	 * @return array
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Sets the response for the data grid.
	 *
	 * @param  array  $response
	 * @return void
	 */
	public function setResponse(array $response)
	{
		$this->response = $response;
	}

	/**
	 * Sets an attribute in the response array, where the
	 * attribute provided is a dot-notation key in the array.
	 *
	 * @param  string  $attribute
	 * @param  mixed   $value
	 * @return void
	 */
	public function setResponseAttribute($attribute, $value = null)
	{
		array_set($this->response, $attribute, $value);
	}

	/**
	 * Returns the value of an attribute in the response array.
	 *
	 * @param  string $attribute
	 * @return mixed
	 */
	public function getResponseAttribute($attribute)
	{
		return array_get($this->response, $attribute);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->response;
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param  int  $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Conver the data grid to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();
	}

}
