<?php namespace Cartalyst\DataGrid\Tests;
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
use Cartalyst\DataGrid\RequestProviders\IlluminateProvider as Provider;
use PHPUnit_Framework_TestCase;

class IlluminateRequestProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGettingFilters()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('filters', array())->once()->andReturn(array('foo'));
		$this->assertEquals(array('foo'), $provider->getFilters());
	}

	public function testGettingSort()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('sort')->once()->andReturn('foo');
		$this->assertEquals('foo', $provider->getSort());
	}

	public function testGettingDirection()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('direction', 'asc')->once()->andReturn('desc');
		$this->assertEquals('desc', $provider->getDirection());

		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('direction', 'asc')->once()->andReturn('foo');
		$this->assertEquals('asc', $provider->getDirection());
	}

	public function testGettingPage()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('page', 1)->once()->andReturn('4');
		$this->assertSame(4, $provider->getPage());

		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('page', 1)->once()->andReturn(0);
		$this->assertSame(1, $provider->getPage());
	}

	public function testGettingRequestedPages()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('requested_pages', 10)->once()->andReturn('4');
		$this->assertSame(4, $provider->getRequestedPages());

		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('requested_pages', 10)->once()->andReturn(0);
		$this->assertSame(10, $provider->getRequestedPages());
	}

	public function testGettingMinimumPerPage()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('minimum_per_page', 10)->once()->andReturn('4');
		$this->assertSame(4, $provider->getMinimumPerPage());

		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('minimum_per_page', 10)->once()->andReturn(0);
		$this->assertSame(10, $provider->getMinimumPerPage());
	}

}
