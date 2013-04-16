<?php namespace Cartalyst\DataGrid\RequestProviders;
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

use Illuminate\Http\Request;

class IlluminateProvider implements ProviderInterface {

	/**
	 * The request instance.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * Creates a new Illuminate data grid
	 * request provider.
	 *
	 * @param  Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Get an array of filters. Filters which
	 * have a string for the key are treated as
	 * filters for an attribute whereas others
	 * are treated as global filters.
	 *
	 * @return array
	 */
	public function getFilters()
	{
		return $this->request->input('filters', array());
	}

	/**
	 * Get the column by which we sort our
	 * datatable.
	 *
	 * @return string
	 */
	public function getSort()
	{
		return $this->request->input('sort');
	}

	/**
	 * Get the direction which we apply
	 * sort.
	 *
	 * @return string
	 */
	public function getDirection()
	{
		$direction = $this->request->input('direction', 'asc');

		return in_array($direction, array('asc', 'desc')) ? $direction : 'asc';
	}

	/**
	 * Get the page which we are on.
	 *
	 * @return int
	 */
	public function getPage()
	{
		$page = (int) $this->request->input('page', 1);

		return ($page > 0) ? $page : 1;
	}

	/**
	 * Get the number of requested pages.
	 *
	 * @return int
	 */
	public function getRequestedPages()
	{
		$requestedPages = (int) $this->request->input('requested_pages', 10);

		return ($requestedPages > 0) ? $requestedPages : 10;
	}

	/**
	 * Get the minimum results per page.
	 *
	 * @return int
	 */
	public function getMinimumPerPage()
	{
		$minimumPerPage = (int) $this->request->input('minimum_per_page', 10);

		return ($minimumPerPage > 0) ? $minimumPerPage : 10;
	}

}
