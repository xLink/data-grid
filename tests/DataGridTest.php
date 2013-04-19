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
use Cartalyst\DataGrid\DataHandlers\DataHandlerInterface;
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

	/**
	 * @expectedException RuntimeException
	 */
	public function testCreatingDataHandlerFailsWithNoMappings()
	{
		$dataGrid = new DataGrid($env = $this->getMockEnvironment(), array(), array());
		$env->shouldReceive('getDataHandlerMappings')->andReturn(array());
		$dataGrid->createDataHandler();
	}

	public function testCreatingDataHandler()
	{
		$dataGrid = new DataGrid($env = $this->getMockEnvironment(), array('bar'), array());
		$env->shouldReceive('getDataHandlerMappings')->andReturn(array(
			'DataHandlerStub' => function(array $data)
			{
				return (count($data) == 1);
			}
		));

		$this->assertInstanceOf('DataHandlerStub', $dataGrid->createDataHandler());
	}

	public function testCasting()
	{
		$dataGrid = new DataGrid($env = $this->getMockEnvironment(), array(), array());
		$dataGrid->setDataHandler($this->getMockHandler());

		$expected = array(
			'total_count'    => 100,
			'filtered_count' => 20,
			'page'           => 1,
			'pages_count'    => 2,
			'previous_page'  => null,
			'next_page'      => 2,
			'results'        => array('foo', 'bar'),
		);

		$this->assertEquals($expected, $dataGrid->toArray());
		$this->assertEquals($expectedJson = json_encode($expected), $dataGrid->toJson());
		$this->assertEquals($expectedJson, (string) $dataGrid);
	}

	protected function getMockEnvironment()
	{
		$environment = m::mock('Cartalyst\DataGrid\Environment');

		return $environment;
	}

	protected function getMockHandler()
	{
		$handler = m::mock('Cartalyst\DataGrid\DataHandlers\DataHandlerInterface');

		$handler->shouldReceive('getTotalCount')->andReturn(100);
		$handler->shouldReceive('getFilteredCount')->andReturn(20);
		$handler->shouldReceive('getPage')->andReturn(1);
		$handler->shouldReceive('getPagesCount')->andReturn(2);
		$handler->shouldReceive('getPreviousPage')->andReturn(null);
		$handler->shouldReceive('getNextPage')->andReturn(2);
		$handler->shouldReceive('getResults')->andReturn(array('foo', 'bar'));

		return $handler;
	}

}

class DataHandlerStub implements DataHandlerInterface {

	/**
	 * Create a new data source.
	 *
	 * @param  Cartalyst\DataGrid\DataGrid  $dataGrid
	 * @return void
	 */
	public function __construct(DataGrid $dataGrid) {}

	/**
	 * Sets up the data source context.
	 *
	 * @return Cartalyst\DataGrid\DataHandler\DataHandlerInterface
	 */
	public function setupDataHandlerContext() {}

	/**
	 * Get the total (unfiltered) count
	 * of results.
	 *
	 * @return int
	 */
	public function getTotalCount() {}

	/**
	 * Get the filtered count of results.
	 *
	 * @return int
	 */
	public function getFilteredCount() {}

	/**
	 * Get the current page we are on.
	 *
	 * @return int
	 */
	public function getPage() {}

	/**
	 * Get the number of pages.
	 *
	 * @return int
	 */
	public function getPagesCount() {}

	/**
	 * Get the previous page.
	 *
	 * @return int|null
	 */
	public function getPreviousPage() {}

	/**
	 * Get the next page.
	 *
	 * @return int|null
	 */
	public function getNextPage() {}

	/**
	 * Get the results.
	 *
	 * @return int
	 */
	public function getResults() {}

}
