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

interface ProviderInterface {

	/**
	 * Get an array of filters. Filters which
	 * have a string for the key are treated as
	 * filters for an attribute whereas others
	 * are treated as global filters.
	 *
	 * @return array
	 */
	public function getFilters();

	/**
	 * Get the column by which we sort our
	 * datatable.
	 *
	 * @return string
	 */
	public function getSort();

	/**
	 * Get the direction which we apply
	 * sort.
	 *
	 * @return string
	 */
	public function getDirection();

	/**
	 * Get the page which we are on.
	 *
	 * @return int
	 */
	public function getPage();

	/**
	 * Get the dividend (ideal number of pages).
	 *
	 * @return int
	 */
	public function getDividend();

	/**
	 * Get the threshold (minimum results per page).
	 *
	 * @return int
	 */
	public function getThreshold();

	/**
	 * Get the throttle (maximum results per page).
	 *
	 * @return int
	 */
	public function getThrottle();

}
