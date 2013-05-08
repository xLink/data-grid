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
				$modified[is_numeric($key) ? $value : $key] = $item[$value];
			}

			return $modified;
		});
	}

	/**
	 * Loops through all filters provided in the data
	 * and manipulates the data.
	 *
	 * @return void
	 */
	public function prepareFilters()
	{
		$filters = $this->request->getFilters();
		$columns = $this->dataGrid->getColumns();

		$this->data = $this->data->filter(function($item) use ($filters, $columns)
		{
			foreach ($filters as $filter)
			{
				// If the filter is an array,
				if (is_array($filter))
				{
					$filterValue  = reset($filter);
					$filterColumn = key($filter);

					if (($index = array_search($filterColumn, $columns)) !== false)
					{
						if (is_numeric($index))
						{
							if (str_contains($item[$filterColumn], $filterValue))
							{
								return true;
							}
						}
						else
						{
							if (str_contains($item[$index], $filterValue))
							{
								return true;
							}
						}
					}
				}
				else
				{
					foreach ($item as $key => $value)
					{
						if (str_contains($value, $filter))
						{
							return true;
						}
					}
				}
			}

			return false;
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
		list($column, $direction) = $this->calculateSort();

		$this->data = $this->data->sort(function($a, $b) use ($column, $direction)
		{
			// @todo, see if we can use strnatcmp to naturally
			// compare strings. However, we'd need to do the same
			// on the database handler as well for consistency.
			$result = strcmp($a[$column], $b[$column]);

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

		list($this->pagesCount, $perPage) = $this->calculatePagination($this->filteredCount);

		list($this->page, $this->previousPage, $this->nextPage) = $this->calculatePages($perPage);

		// Calculate the offset that's needed to slice our collection
		$offset = ($this->page - 1) * $perPage;

		$this->data = $this->data->slice($offset, $perPage);
	}

}
