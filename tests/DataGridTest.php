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

use Mockery as m;
use Cartalyst\DataGrid\DataGrid;

class DataGridTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFoo(){}

	// public function testSettingResponseAndResponseAttributes()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	$response = array(
	// 		'baz' => 'qux',
	// 	);

	// 	$dataGrid->setResponse($response);
	// 	$this->assertEquals($response, $dataGrid->getResponse());

	// 	$dataGrid->setResponseAttribute('foo', 'bar');
	// 	$dataGrid->setResponseAttribute('corge.waldo', 'fred');

	// 	$expected = array(
	// 		'baz' => 'qux',
	// 		'foo' => 'bar',
	// 		'corge' => array(
	// 			'waldo' => 'fred',
	// 		),
	// 	);

	// 	$this->assertEquals($expected, $dataGrid->getResponse());
	// 	$this->assertEquals('fred', $dataGrid->getResponseAttribute('corge.waldo'));
	// }

	// public function testGettingInputCallsCorrectMethodOnRequestProvider()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	$environment->shouldReceive('getRequestProvider')->once()->andReturn($requestProvider = m::mock('Cartalyst\DataGrid\RequestProviders\ProviderInterface'));

	// 	$requestProvider->shouldReceive('input')->with('foo', 'baz')->once()->andReturn('bar');

	// 	$this->assertEquals('bar', $dataGrid->getInput('foo', 'baz'));
	// }

	// public function testTotalCount()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	// Purposely return a string
	// 	$query->shouldReceive('count')->once()->andReturn('5');

	// 	$dataGrid->setupTotalCount();
	// 	$this->assertSame(5, $dataGrid->getResponseAttribute('counts.total'));
	// }

	// public function testSelectColumns()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$query->shouldReceive('select')->with(array(
	// 		'foo',
	// 		'bar.baz as qux',
	// 	))->once();

	// 	$dataGrid->setupSelect();
	// }

	// public function testSettingUpColumnFilters()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->shouldReceive('getInput')->with('filters', array())->once()->andReturn(array(
	// 		array('foo' => 'Filter 1'),
	// 		array('qux' => 'Filter 2'),
	// 	));

	// 	$query->shouldReceive('where')->with('foo', 'like', '%Filter 1%')->once();
	// 	$query->shouldReceive('where')->with('bar.baz', 'like', '%Filter 2%')->once();

	// 	$dataGrid->setupFilters();
	// }

	// public function testSettingUpGlobalFilters()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->shouldReceive('getInput')->with('filters', array())->once()->andReturn(array(
	// 		'Global Filter'
	// 	));

	// 	$query->shouldReceive('whereNested')->with(m::type('Closure'))->once();

	// 	$dataGrid->setupFilters();
	// }

	// public function testGlobalFilterOnQuery()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$query->shouldReceive('orWhere')->with('foo', 'like', '%Global Filter%')->once();
	// 	$query->shouldReceive('orWhere')->with('bar.baz', 'like', '%Global Filter%')->once();

	// 	$dataGrid->globalFilter($query, 'Global Filter');
	// }

	// public function testFilteredCount()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	// Purposely return a string
	// 	$query->shouldReceive('count')->once()->andReturn('5');

	// 	$dataGrid->setupFilteredCount();
	// 	$this->assertSame(5, $dataGrid->getResponseAttribute('counts.filtered'));
	// }

	// public function testSortingWhenNoOrdersArePresent()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->shouldReceive('getInput')->with('sort', 'foo')->once()->andReturn('qux');
	// 	$dataGrid->shouldReceive('getInput')->with('direction', 'asc')->once()->andReturn('desc');

	// 	$query->shouldReceive('orderBy')->with('bar.baz', 'desc')->once();

	// 	$dataGrid->setupSort();
	// }

	// public function testSortingWhenOrdersAreAlreadyPresent()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->shouldReceive('getInput')->with('sort', 'foo')->once()->andReturn('qux');
	// 	$dataGrid->shouldReceive('getInput')->with('direction', 'asc')->once()->andReturn('desc');

	// 	$query->orders = array(
	// 		array(
	// 			'column'    => 'corge',
	// 			'direction' => 'asc',
	// 		),
	// 	);

	// 	$dataGrid->setupSort();

	// 	// Validate the orders are correct
	// 	$this->assertCount(2, $query->orders);
	// 	$this->assertEquals('bar.baz', $query->orders[0]['column']);
	// 	$this->assertEquals('desc', $query->orders[0]['direction']);
	// 	$this->assertEquals('corge', $query->orders[1]['column']);
	// 	$this->assertEquals('asc', $query->orders[1]['direction']);
	// }

	// /**
	//  * @expectedException InvalidArgumentException
	//  */
	// public function testCalculatingPaginationThrowsExceptionIfRequestedPagesIsZero()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	$dataGrid->calculatePagination(10, 0, 10);
	// }

	// public function testCalculatingPagination()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	$result = $dataGrid->calculatePagination(100, 10, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(10, $totalPages);
	// 	$this->assertSame(10, $perPage);

	// 	$result = $dataGrid->calculatePagination(120, 10, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(10, $totalPages);
	// 	$this->assertSame(12, $perPage);

	// 	$result = $dataGrid->calculatePagination(200, 10, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(10, $totalPages);
	// 	$this->assertSame(20, $perPage);

	// 	$result = $dataGrid->calculatePagination(1000, 5, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(5, $totalPages);
	// 	$this->assertSame(200, $perPage);

	// 	// Where we use the minimum per page
	// 	$result = $dataGrid->calculatePagination(100, 10, 50);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(2, $totalPages);
	// 	$this->assertSame(50, $perPage);

	// 	// Where the number of pages is greater than the result
	// 	$result = $dataGrid->calculatePagination(100, 200, 80);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(2, $totalPages);
	// 	$this->assertSame(80, $perPage);

	// 	// Where we just require two pages
	// 	$result = $dataGrid->calculatePagination(11, 10, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(2, $totalPages);
	// 	$this->assertSame(10, $perPage);

	// 	// Default parameters should be 10
	// 	$result = $dataGrid->calculatePagination(100);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(10, $totalPages);
	// 	$this->assertSame(10, $perPage);

	// 	$result = $dataGrid->calculatePagination(10, 20, 10);
	// 	$this->assertCount(2, $result);
	// 	list($totalPages, $perPage) = $result;
	// 	$this->assertSame(1, $totalPages);
	// 	$this->assertSame(10, $perPage);
	// }

	// public function testSettingUpPaginationLeavesDefaultParametersIfNoFilteredResultsArePresent()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[calculatePagination]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	// No mock expectations becuase the method returns right away
	// 	$dataGrid->setupPagination();
	// }

	// /**
	//  * @expectedException InvalidArgumentException
	//  */
	// public function testSettingUpPaginationThrowsExceptionForInvalidPage()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[calculatePagination,getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->setResponseAttribute('counts.filtered', 10);

	// 	$dataGrid->shouldReceive('getInput')->with('page', 1)->once()->andReturn(-1);

	// 	// No mock expectations becuase the method returns right away
	// 	$dataGrid->setupPagination();
	// }

	// public function testSettingUpPaginationWithOnePage()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[calculatePagination,getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->setResponseAttribute('counts.filtered', 10);

	// 	$dataGrid->shouldReceive('getInput')->with('page', 1)->once()->andReturn(1);
	// 	$dataGrid->shouldReceive('getInput')->with('requested_pages', 10)->once()->andReturn(20);
	// 	$dataGrid->shouldReceive('getInput')->with('minimum_per_page', 10)->once()->andReturn(10);
	// 	$dataGrid->shouldReceive('calculatePagination')->with(10, 20, 10)->once()->andReturn(array(1, 10));

	// 	$query->shouldReceive('forPage')->with(1, 10)->once();

	// 	// No mock expectations becuase the method returns right away
	// 	$dataGrid->setupPagination();

	// 	// Validate response attributes
	// 	$this->assertSame(false, $dataGrid->getResponseAttribute('pagination.previous_page'));
	// 	$this->assertSame(false, $dataGrid->getResponseAttribute('pagination.next_page'));
	// 	$this->assertSame(1, $dataGrid->getResponseAttribute('pagination.total_pages'));
	// }

	// public function testSettingUpPaginationOnPage2Of3()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[calculatePagination,getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->setResponseAttribute('counts.filtered', 30);

	// 	$dataGrid->shouldReceive('getInput')->with('page', 1)->once()->andReturn(2);
	// 	$dataGrid->shouldReceive('getInput')->with('requested_pages', 10)->once()->andReturn(10);
	// 	$dataGrid->shouldReceive('getInput')->with('minimum_per_page', 10)->once()->andReturn(10);
	// 	$dataGrid->shouldReceive('calculatePagination')->with(30, 10, 10)->once()->andReturn(array(3, 10));

	// 	$query->shouldReceive('forPage')->with(2, 10)->once();

	// 	// No mock expectations becuase the method returns right away
	// 	$dataGrid->setupPagination();

	// 	// Validate response attributes
	// 	$this->assertSame(1, $dataGrid->getResponseAttribute('pagination.previous_page'));
	// 	$this->assertSame(3, $dataGrid->getResponseAttribute('pagination.next_page'));
	// 	$this->assertSame(3, $dataGrid->getResponseAttribute('pagination.total_pages'));
	// }

	// public function testSettingUpPaginationOnPage3Of3()
	// {
	// 	$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid[calculatePagination,getInput]');
	// 	$dataGrid->__construct(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar.baz' => 'qux')
	// 	);

	// 	$dataGrid->setResponseAttribute('counts.filtered', 30);

	// 	$dataGrid->shouldReceive('getInput')->with('page', 1)->once()->andReturn(3);
	// 	$dataGrid->shouldReceive('getInput')->with('requested_pages', 10)->once()->andReturn(10);
	// 	$dataGrid->shouldReceive('getInput')->with('minimum_per_page', 10)->once()->andReturn(10);
	// 	$dataGrid->shouldReceive('calculatePagination')->with(30, 10, 10)->once()->andReturn(array(3, 10));

	// 	$query->shouldReceive('forPage')->with(3, 10)->once();

	// 	// No mock expectations becuase the method returns right away
	// 	$dataGrid->setupPagination();

	// 	// Validate response attributes
	// 	$this->assertSame(2, $dataGrid->getResponseAttribute('pagination.previous_page'));
	// 	$this->assertSame(false, $dataGrid->getResponseAttribute('pagination.next_page'));
	// 	$this->assertSame(3, $dataGrid->getResponseAttribute('pagination.total_pages'));
	// }

	// public function testHydrating()
	// {
	// 	$dataGrid = new DataGrid(
	// 		$environment = m::mock('Cartalyst\DataGrid\Environment'),
	// 		$query       = m::mock('Illuminate\Database\Query\Builder'),
	// 		$columns     = array('foo', 'bar')
	// 	);

	// 	$results = array();
	// 	$result1 = new StdClass;
	// 	$result1->foo = 'bar';
	// 	$result1->baz = 'qux';
	// 	$results[] = $result1;
	// 	$result2 = new StdClass;
	// 	$result2->foo = 'bar';
	// 	$result2->baz = 'qux';
	// 	$results[] = $result2;

	// 	$query->shouldReceive('get')->andReturn($results);

	// 	$dataGrid->hydrate();
	// }

}
