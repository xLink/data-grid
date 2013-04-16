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
use Cartalyst\DataGrid\DataHandlers\EloquentDataHandler as Handler;

class EloquentDataHandlerTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testPreparingSelect()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$dataGrid->getData()->shouldReceive('select')->with(array(
			'foo',
			'bar.baz as qux',
		))->once();

		$handler->prepareSelect();
	}

	public function testSettingUpColumnFilters()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getFilters')->once()->andReturn(array(
			array('foo' => 'Filter 1'),
			array('qux' => 'Filter 2'),
			'Filter 3',
		));

		$dataGrid->getData()->shouldReceive('where')->with('foo', 'like', '%Filter 1%')->once();
		$dataGrid->getData()->shouldReceive('where')->with('bar.baz', 'like', '%Filter 2%')->once();
		$dataGrid->getData()->shouldReceive('whereNested')->with(m::type('Closure'))->once();

		$handler->prepareFilters();
	}

	public function testGlobalFilterOnQuery()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('orWhere')->with('foo', 'like', '%Global Filter%')->once();
		$query->shouldReceive('orWhere')->with('bar.baz', 'like', '%Global Filter%')->once();

		$handler->globalFilter($query, 'Global Filter');
	}

	public function testFilteredCount()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$dataGrid->getData()->shouldReceive('count')->once()->andReturn(5);

		$handler->prepareFilteredCount();
		$this->assertEquals(5, $handler->getFilteredCount());
	}

	public function testSortingWhenNoOrdersArePresent()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getSort')->once()->andReturn('qux');
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getDirection')->once()->andReturn('desc');
		$dataGrid->getData()->shouldReceive('getQuery')->once()->andReturn($query = m::mock('Illuminate\Database\Query\Builder'));

		$query->shouldReceive('orderBy')->with('bar.baz', 'desc')->once();

		$handler->prepareSort();
	}

	public function testSortingWhenOrdersAreAlreadyPresent()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getSort')->once()->andReturn('qux');
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getDirection')->once()->andReturn('desc');
		$dataGrid->getData()->shouldReceive('getQuery')->once()->andReturn($query = m::mock('Illuminate\Database\Query\Builder'));

		$query->orders = array(
			array(
				'column'    => 'corge',
				'direction' => 'asc',
			),
		);

		$handler->prepareSort();

		// Validate the orders are correct
		$this->assertCount(2, $query->orders);
		$this->assertEquals('bar.baz', $query->orders[0]['column']);
		$this->assertEquals('desc', $query->orders[0]['direction']);
		$this->assertEquals('corge', $query->orders[1]['column']);
		$this->assertEquals('asc', $query->orders[1]['direction']);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCalculatingPaginationThrowsExceptionIfRequestedPagesIsZero()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$handler->calculatePagination(10, 0, 10);
	}

	public function testCalculatingPagination()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$result = $handler->calculatePagination(100, 10, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(10, $totalPages);
		$this->assertSame(10, $perPage);

		$result = $handler->calculatePagination(120, 10, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(10, $totalPages);
		$this->assertSame(12, $perPage);

		$result = $handler->calculatePagination(200, 10, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(10, $totalPages);
		$this->assertSame(20, $perPage);

		$result = $handler->calculatePagination(1000, 5, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(5, $totalPages);
		$this->assertSame(200, $perPage);

		// Where we use the minimum per page
		$result = $handler->calculatePagination(100, 10, 50);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(2, $totalPages);
		$this->assertSame(50, $perPage);

		// Where the number of pages is greater than the result
		$result = $handler->calculatePagination(100, 200, 80);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(2, $totalPages);
		$this->assertSame(80, $perPage);

		// Where we just require two pages
		$result = $handler->calculatePagination(11, 10, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(2, $totalPages);
		$this->assertSame(10, $perPage);

		// Default parameters should be 10
		$result = $handler->calculatePagination(100);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(10, $totalPages);
		$this->assertSame(10, $perPage);

		$result = $handler->calculatePagination(10, 20, 10);
		$this->assertCount(2, $result);
		list($totalPages, $perPage) = $result;
		$this->assertSame(1, $totalPages);
		$this->assertSame(10, $perPage);
	}

	public function testSettingUpPaginationLeavesDefaultParametersIfNoFilteredResultsArePresent()
	{
		$handler = m::mock('Cartalyst\DataGrid\DataHandlers\EloquentDataHandler[calculatePagination]');

		$handler->preparePagination();
	}

	public function testSettingUpPaginationWithOnePage()
	{
		$handler = m::mock('Cartalyst\DataGrid\DataHandlers\EloquentDataHandler[calculatePagination]');
		$handler->__construct($dataGrid = $this->getMockDataGrid());
		$handler->setFilteredCount(10);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getPage')->once()->andReturn(1);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getRequestedPages')->once()->andReturn(20);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getMinimumPerPage')->once()->andReturn(10);

		$handler->shouldReceive('calculatePagination')->with(10, 20, 10)->once()->andReturn(array(1, 10));

		$dataGrid->getData()->shouldReceive('forPage')->with(1, 10)->once();

		$handler->preparePagination();

		$this->assertNull($handler->getPreviousPage());
		$this->assertNull($handler->getNextPage());
		$this->assertSame(1, $handler->getPagesCount());
	}

	public function testSettingUpPaginationOnPage2Of3()
	{
		$handler = m::mock('Cartalyst\DataGrid\DataHandlers\EloquentDataHandler[calculatePagination]');
		$handler->__construct($dataGrid = $this->getMockDataGrid());
		$handler->setFilteredCount(30);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getPage')->once()->andReturn(2);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getRequestedPages')->once()->andReturn(10);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getMinimumPerPage')->once()->andReturn(10);

		$handler->shouldReceive('calculatePagination')->with(30, 10, 10)->once()->andReturn(array(3, 10));

		$dataGrid->getData()->shouldReceive('forPage')->with(2, 10)->once();

		$handler->preparePagination();

		$this->assertSame(1, $handler->getPreviousPage());
		$this->assertSame(3, $handler->getNextPage());
		$this->assertSame(3, $handler->getPagesCount());
	}

	public function testSettingUpPaginationOnPage3Of3()
	{
		$handler = m::mock('Cartalyst\DataGrid\DataHandlers\EloquentDataHandler[calculatePagination]');
		$handler->__construct($dataGrid = $this->getMockDataGrid());
		$handler->setFilteredCount(30);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getPage')->once()->andReturn(3);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getRequestedPages')->once()->andReturn(10);
		$dataGrid->getEnvironment()->getRequestProvider()->shouldReceive('getMinimumPerPage')->once()->andReturn(10);

		$handler->shouldReceive('calculatePagination')->with(30, 10, 10)->once()->andReturn(array(3, 10));

		$dataGrid->getData()->shouldReceive('forPage')->with(3, 10)->once();

		$handler->preparePagination();

		$this->assertSame(2, $handler->getPreviousPage());
		$this->assertNull($handler->getNextPage());
		$this->assertSame(3, $handler->getPagesCount());
	}

	public function testHydrating()
	{
		$handler = new Handler($dataGrid = $this->getMockDataGrid());

		$results = array(
			$result1 = new StdClass,
			$result2 = new StdClass,
		);

		$result1->foo   = 'bar';
		$result1->baz   = 'qux';
		$result2->corge = 'fred';

		$dataGrid->getData()->shouldReceive('get')->andReturn($results);

		$handler->hydrate();

		$expected = array(
			array('foo' => 'bar', 'baz' => 'qux'),
			array('corge' => 'fred'),
		);

		$this->assertCount(count($expected), $results = $handler->getResults());
		$this->assertEquals($expected, $results);

		foreach ($results as $index => $result)
		{
			$this->assertTrue(array_key_exists($index, $results));
			$this->assertEquals($expected[$index], $result);
		}
	}

	protected function getMockDataGrid()
	{
		$dataGrid = m::mock('Cartalyst\DataGrid\DataGrid');
		$dataGrid->shouldReceive('getData')->andReturn(m::mock('Illuminate\Database\Eloquent\Builder'));
		$dataGrid->shouldReceive('getEnvironment')->andReturn($environment = m::mock('Cartalyst\DataGrid\Environment'));
		$environment->shouldReceive('getRequestProvider')->andReturn(m::mock('Cartalyst\DataGrid\RequestProviders\ProviderInterface'));
		$dataGrid->shouldReceive('getColumns')->andReturn(array(
			'foo',
			'bar.baz' => 'qux',
		));
		return $dataGrid;
	}

}
