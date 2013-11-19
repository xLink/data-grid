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
use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;

class CollectionHandler extends BaseHandler implements HandlerInterface {

	/**
	 * Validate the data store.
	 *
	 * @param  mixed  $data
	 * @return mixed  $data
	 * @throws Exception
	 */
	public function validateData($data)
	{
		// If we have an array, we'll throw it into a collection now
		if (is_array($data)) $data = new Collection($data);

		// We must have a collection by this point. No collection? No go.
		if ( ! $data instanceof Collection)
		{
			throw new \InvalidArgumentException("Invalid data source passsed to collection handler. Must be an array or collection object.");
		}

		// Ensure that our items are arrays as we accept various data types
		$data = $data->map(function($item)
		{
			if ($item instanceof ArrayableInterface)
			{
				$item = $item->toArray();
			}

			return (array) $item;
		});

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
		$this->totalCount = $this->data->count();
	}

	/**
	 * Prepares the "select" component of the statement
	 * based on the columns array provided.
	 *
	 * @return void
	 */
	public function prepareSelect()
	{
		$columns = $this->dataGrid->getColumns();

		// We'll go ahead and map the columns, only selecting the ones which
		// are required.
		$this->data = $this->data->map(function($item) use ($columns)
		{
			$modified = array();

			// If the person is using an alias, we'll
			// be sure to modify the select to work off
			// the alias and not the actual key.
			foreach ($columns as $key => $value)
			{
				$modified[$value] = $item[is_numeric($key) ? $value : $key];
			}

			return $modified;
		});
	}

	/**
	 * Loops through all filters provided in the data
	 * and manipulates the data.
	 *
	 * @return void
	 * @todo   Sub-out hard-coding to "like"
	 */
	public function prepareFilters()
	{
		list($columnFilters, $globalFilters) = $this->getFilters();

		if (count($columnFilters) === 0 and count($globalFilters) === 0)
		{
			return;
		}

		$me = $this;
		$this->data = $this->data->filter(function($item) use ($me, $columnFilters, $globalFilters)
		{
			foreach ($columnFilters as $filter)
			{
				list($column, $operator, $value) = $filter;

				if ( ! $me->checkColumnFilter($item, $column, $operator, $value))
				{
					return false;
				}
			}

			foreach ($globalFilters as $filter)
			{
				list($operator, $value) = $filter;

				return $me->checkGlobalFilter($item, $operator, $value);
			}

			return true;
		});
	}

	/**
	 * Sets up the filtered results count (before pagination).
	 *
	 * @return void
	 */
	public function prepareFilteredCount()
	{
		$this->filteredCount = $this->data->count();
	}

	/**
	 * Sets up the sorting for the data.
	 *
	 * @return void
	 */
	public function prepareSort()
	{
		$column = $this->calculateSortColumn($this->request->getSort());
		$direction = $this->request->getDirection();

		$this->data = $this->data->sort(function($a, $b) use ($column, $direction)
		{
			$result = strnatcasecmp($a[$column], $b[$column]);

			$invert = ($direction == 'desc');

			return $result * ($invert ? -1 : 1);
		});
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

		// Calculate the offset that's needed to slice our collection
		$offset = ($this->page - 1) * $this->perPage;

		$this->data = $this->data->slice($offset, $this->perPage);
	}

	/**
	 * Hydrates the results.
	 *
	 * @return void
	 */
	public function hydrate()
	{
		$this->results = $this->data->all();
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
	 * Checks a coclumn filter against the given item in the collection.
	 *
	 * @param  array   $item
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @return bool
	 */
	public function checkColumnFilter(array $item, $column, $operator, $value)
	{
		$columns = $this->dataGrid->getColumns();

		if (($index = array_search($column, $columns)) !== false)
		{
			if ( ! is_numeric($index))
			{
				$column = $index;
			}

			if ( ! $this->checkColumnFilterValue($operator, $item[$column], $value))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks a filter globally against all columns.
	 *
	 * @param  array   $item
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @return bool
	 */
	public function checkGlobalFilter(array $item, $operator, $value)
	{
		foreach ($item as $columnValue)
		{
			if (is_array($columnValue))
			{
				foreach ($columnValue as $_columnKey => $_columnValue)
				{
					if ($this->checkColumnFilterValue($operator, $_columnValue, $value))
					{
						return true;
					}
				}
			}
			elseif ($this->checkColumnFilterValue($operator, $columnValue, $value))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * The "guts" of checking whether a filtered value matches a column value,
	 * given the operator.
	 *
	 * @param  string  $operator
	 * @param  mixed   $columnValue
	 * @param  mixed   $filterValue
	 * @return bool
	 */
	protected function checkColumnFilterValue($operator, $columnValue, $filterValue)
	{
		return (stripos($columnValue, $filterValue) !== false);
	}

}
