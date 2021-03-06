<?php
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

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

return array(

	/*
	|--------------------------------------------------------------------------
	| Dividend
	|--------------------------------------------------------------------------
	|
	| Define the defualt dividend (ideal number of pages, once the results
	| count is greater than the threshold and each page has
	| less results than the throttle).
	|
	*/

	'dividend' => 10,

	/*
	|--------------------------------------------------------------------------
	| Threshold
	|--------------------------------------------------------------------------
	|
	| Define the default threshold (number of results before pagination begins).
	|
	*/

	'threshold' => 100,

	/*
	|--------------------------------------------------------------------------
	| Throttle
	|--------------------------------------------------------------------------
	|
	| Define the default throttle, which is the maximum results set. If the
	| results total is greater than this (before we apply the
	| dividend, we'll reduce the results set).
	|
	*/

	'throttle' => 100,

	/*
	|--------------------------------------------------------------------------
	| Direction
	|--------------------------------------------------------------------------
	|
	| Define the default sort direction.
	|
	*/

	'direction' => 'asc',

	/*
	|--------------------------------------------------------------------------
	| Sort
	|--------------------------------------------------------------------------
	|
	| Define the default sort column.
	|
	*/

	'sort' => 'id',

	/*
	|--------------------------------------------------------------------------
	| Data Handler Mappings
	|--------------------------------------------------------------------------
	|
	| Here you may specify any "data handlers" which handle the dataset given
	| to a data grid instance. The key is the class which handles the data
	| and the value is a closure which must return true. There may be multiple
	| classes which can handle the same data type but only one for that
	| specific data set.
	|
	| Supported: Any class which implements:
	|            Cartalyst\DataGrid\DataHandlers\HandlerInterface
	|
	*/
	'handlers' => array(

		'Cartalyst\DataGrid\DataHandlers\DatabaseHandler' => function($data)
		{
			return (
				$data instanceof EloquentModel or
				$data instanceof EloquentQueryBuilder or
				$data instanceof HasMany or
				$data instanceof BelongsToMany or
				$data instanceof QueryBuilder
			);
		},

		'Cartalyst\DataGrid\DataHandlers\CollectionHandler' => function($data)
		{
			return (
				$data instanceof Collection or
				is_array($data)
			);
		},

	),

);
