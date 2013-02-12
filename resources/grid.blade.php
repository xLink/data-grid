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
?>
<div id="grid" data-source="http://example.com/grid/source" data-results=".grid-results" data-filters=".grid-filters" data-applied-filters=".grid-applied-filters" data-pagination=".grid-pagination">

	<div class="grid-filters">

		<div class="clearfix">
			<div class="form-inline">

				<div class="pull-left">
					<div class="input-append">
						<input type="text" placeholder="Filter All">
						<button class="btn add-global-filter">
							Add
						</button>
					</div>
					&nbsp;
				</div>

				<div class="pull-left" data-template>

					<!-- Build different HTML based on the type -->
					[? if type == 'select' ?]
						<select class="input-small" id="grid-filters-[[column]]" data-column="[[column]]">
							<option>
								-- [[label]] --
							</option>

							<!-- Need to work out how to embed each <option> inside the <optgroup> data-template... -->
							<option data-template-for="mappings" value="[[value]]">
								[[label]]
							</option>
						</select>

						<button class="btn add-filter">
							Add
						</button>
					[? else ?]
						<div class="input-append">
							<input type="text" class="input-small" id="grid-filters-[[column]]" data-column="[[column]]" placeholder="[[label]]">

							<button class="btn add-filter">
								Add
							</button>
						</div>
						&nbsp;
					[? endif ?]

				</div>

			</div>
		</div>

	</div>

	<br>

	<ul class="nav nav-tabs grid-applied-filters">
		<li data-template>
			<a href="#" class="remove-filter">
				[? if type == 'global' ?]
					<strong>[[value]]</strong>
				[? else ?]
					<small><em>([[column]])</em></small> <strong>[[value]]</strong>
				[? endif ?]
				<span class="close" style="float: none;">&times;</span>
			</a>
		</li>
	</ul>

	<div class="tabbable tabs-right">

		<ul class="nav nav-tabs grid-pagination">
			<li data-template class="[? if active ?] active [? endif ?]">
				<a href="#" data-page="[[page]]" data-toggle="tab" class="goto-page">
					Page #[[page]]
				</a>
			</li>
		</ul>

		<div class="tab-content">

			<table class="table table-striped table-bordered grid-results">
				<thead>
					<tr>
						<th data-column="id">ID</th>
						<th data-column="first_name">First Name</th>
						<th data-column="last_name">Last Name</th>
						<th data-column="activated">Activated</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr data-template>
						<td data-column="id">[[id]]</td>
						<td data-column="first_name">[[first_name]]</td>
						<td data-column="last_name">[[last_name]]</td>
						<td data-type="select" data-column="activated" data-mappings="Yes:1|No:0">
							[? if activated == 1 ?]
								Yes
							[? else ?]
								No
							[? endif ?]
						</td>
						<td data-static>
							<a href="{{ URL::to(ADMIN_URI.'/users/edit') }}/[[id]]">
								Edit
								[? if first_name ?]
									[[first_name]]
								[? else ?]
									Un-named #[[id]]
								[? endif ?]
							</a>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>

</div>
