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
		'count' => array(
			'total'    => 0,
			'filtered' => 0,
		),

		// Pagination
		'pagination' => array(
			'page'     => 1,
			'division' => 10,
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
		if ($query instanceof QueryBuilder or $query instanceof EloquentQueryBuilder)
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
	 * @return Cartalyst\DataGrid\DataGrid
	 */
	public function setupDataGridContext()
	{
		$this->setupFilters();

		$this->setupSort();
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

				if (isset($this->columns[$filterColumn]))
				{
					$this->query->where(
						$this->columns[$filterColumn],
						'like',
						"%{$filterValue}%"
					);
				}
			}

			// Otherwise if a string was provided, the
			// filter is an "or where" filter across all
			// columns.
			elseif (is_string($filter))
			{
				$me = $this;
				$this->query->whereNested(function($query) use ($me)
				{
					$me->filterColumns($query, $filter);
				});
				$this->searchAllColumns($filter);
			}
		}
	}

	/**
	 * Sets up the sorting for the query.
	 *
	 * @return void
	 */
	public function setupSort()
	{
		$this->response['sort']['column']    = $column = $this->getInput('sort.column', reset($this->columns));
		$this->response['sort']['direction'] = $direction = $this->getInput('sort.direction', 'asc');

		// We are going to prepend our sort order to the query
		// as SQL allows for multiple sort. By appending it, a predefined
		// sort may override ours.
		array_unshift($this->query->orders, compact('column', 'direction'));
	}

	/**
	 * Applies a filter across all registered columns. The
	 * filter is applied in a "or where" fashion, where
	 * the value can be matched across any column.
	 *
	 * @param  Illuminate\Database\Eloquent\Builder  $query
	 * @param  string  $filter
	 * @return void
	 */
	public function filterColumns(QueryBuilder $query, $filter)
	{
		foreach ($this->columns as $column)
		{
			$query->orWhere($column, 'like', "%{$filter}%");
		}
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