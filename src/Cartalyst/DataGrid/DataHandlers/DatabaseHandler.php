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
use Illuminate\Database\MySqlConnection as MySqlDatabaseConnection;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Contracts\ArrayableInterface;

class DatabaseHandler extends BaseHandler implements HandlerInterface {

	/**
	 * Validate the data store.
	 *
	 * @param  mixed  $data
	 * @return mixed  $data
	 * @throws Exception
	 */
	public function validateData($data)
	{
		// If the data is an instance of an Eloquent model,
		// we'll grab a new query from it.
		if ($data instanceof Model)
		{
			$data = $data->newQuery();
		}

		// We accept different data types for our data grid,
		// let's just check now that
		if ( ! $data instanceof QueryBuilder and
			 ! $data instanceof EloquentQueryBuilder and
			 ! $data instanceof HasMany and
			 ! $data instanceof BelongsToMany)
		{
			throw new \InvalidArgumentException("Invalid data source passed to database handler. Must be an Eloquent model / query / valid relationship, or a databse query.");
		}

		return $data;
	}

	/**
	 * Prepares the total count of results before
	 * we apply filters.
	 *
	 * @return void
	 */
	public function prepareTotalCount()
	{
		$this->totalCount = (int) $this->data->count();
	}

	/**
	 * Prepares the "select" component of the statement
	 * based on the columns array provided.
	 *
	 * @return void
	 */
	public function prepareSelect()
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
		foreach ($this->dataGrid->getColumns() as $key => $value)
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
	public function prepareFilters()
	{
		list($columnFilters, $globalFilters) = $this->getFilters();

		foreach ($columnFilters as $filter)
		{
			list($column, $operator, $value) = $filter;

			$this->applyFilter($this->data, $column, $operator, $value);
		}

		foreach ($globalFilters as $filter)
		{
			list($operator, $value) = $filter;
			$me = $this;

			$this->data->whereNested(function($data) use ($me, $operator, $value)
			{
				$me->globalFilter($data, $operator, $value);
			});
		}
	}

	/**
	 * Applies a global filter across all registered columns. The
	 * filter is applied in a "or where" fashion, where
	 * the value can be matched across any column.
	 *
	 * @param  Illuminate\Database\Query\Builder  $nestedQuery
	 * @param  string  $operator
	 * @param  string  $value
	 * @return void
	 */
	public function globalFilter(QueryBuilder $nestedQuery, $operator, $value)
	{
		foreach ($this->dataGrid->getColumns() as $key => $_value)
		{
			if (is_numeric($key))
			{
				$key = $_value;
			}

			$this->applyFilter($nestedQuery, $key, $operator, $value, 'or');
		}
	}

	/**
	 * Sets up the filtered results count (before pagination).
	 *
	 * @return void
	 */
	public function prepareFilteredCount()
	{
		$this->filteredCount = (int) $this->data->count();
	}

	/**
	 * Sets up the sorting for the data.
	 *
	 * @return void
	 */
	public function prepareSort()
	{
		$column    = $this->calculateSortColumn($this->request->getSort());
		$direction = $this->request->getDirection();

		$data = $this->data;

		if ($data instanceof HasMany or $data instanceof BelongsToMany)
		{
			$data = $data->getQuery();
		}

		if ($data instanceof EloquentQueryBuilder)
		{
			$data = $data->getQuery();
		}

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
	public function preparePagination()
	{
		// If our filtered results are zero, let's not set any pagination
		if ($this->filteredCount == 0) return;

		$dividend  = $this->request->getDividend();
		$threshold = $this->request->getThreshold();
		$throttle  = $this->request->getThrottle();
		$page      = $this->request->getPage();

		list($this->pagesCount, $this->perPage) = $this->calculatePagination($this->filteredCount, $dividend, $threshold, $throttle);

		list($this->page, $this->previousPage, $this->nextPage) = $this->calculatePages($this->filteredCount, $page, $this->perPage);

		$this->data->forPage($this->page, $this->perPage);
	}

	/**
	 * Hydrates the results.
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
		$this->results = array_map(function($result)
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
	 * Flag for whether the handler supports regex filters.
	 *
	 * @return void
	 */
	public function supportsRegexFilters()
	{
		return true;
	}

	/**
	 * Applies a filter to the given query.
	 *
	 * @param  mixed   $query
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @param  string  $boolean
	 * @return void
	 */
	protected function applyFilter($query, $column, $operator, $value, $boolean = 'and')
	{
		$method = ($boolean === 'and') ? 'where' : 'orWhere';

		switch ($operator)
		{
			case 'like':
				$value = "%{$value}%";
				break;

			case 'regex':

				$data = $this->data;
				if ($data instanceof EloquentQueryBuilder)
				{
					$data = $data->getQuery();
				}

				$method .= 'Raw';

				switch ($connection = $data->getConnection())
				{
					case $connection instanceof MySqlDatabaseConnection:
						$query->$method("{$column} {$operator} ?", array($value));
						return;
				}

				return;
		}

		$query->$method($column, $operator, $value);
	}

}
