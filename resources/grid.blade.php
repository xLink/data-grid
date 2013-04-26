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
<script>
jQuery(document).ready(function($){
	var dg = $.datagrid('main', '#grid', '#pagination', '#applied', {
		loader: '#loader',
		callback: function(filters, sort, pagination){
			console.log(filters);
		}
	});


	$("#foo").on('click', function(e){

		dg._trigger({ sort:'name:asc', filter:'pizza'});

	});

});
</script>

<div id="loader">Loading Data...</div>

<hr>

<a href="#" id="foo">Custom Trigger Filter by Pizza, order by name asc</a>


<a href="#" data-filter="bar:name, burger" data-key="main">Bars By Name and Burger</a>

<a href="#" data-filter="pizza" data-key="main">Filter By Pizza</a>

<a href="#" data-sort="name" data-key="main">Sort By Name</a>


<form method="post" action="" accept-charset="utf-8" data-search data-key="main">
	<select name="column">
		<option value="all">All</option>
		<option value="first_name">First Name</option>
		<option value="last_name">Last Name</option>
	</select>
	<input name="filter" type="text" placeholder="Filter All">
	<button class="btn add-global-filter">
		Add
	</button>
</form>

<hr>


<ul class="nav nav-tabs" id="applied" data-key="main">
	<li data-template>
		<a href="#">
			[? if column == undefined ?]
				[[ value ]]
			[? else ?]
				[[ value ]] in [[ column ]]
			[? endif ?]
		</a>
	</li>
</ul>

<ul id="pagination" data-key="main">
	<li data-template data-page="[[ page ]]">[[ page ]]</li>
</ul>

<table id="grid" data-source="{{ URL::toAdmin('users/grid') }}" data-key="main" class="table table-bordered table-striped">
	<thead>
		<tr>
			<th class="sortable">@lang('platform/users::users/table.id')</th>
			<th class="sortable">@lang('platform/users::users/table.first_name')</th>
			<th class="sortable">@lang('platform/users::users/table.last_name')</th>
			<th class="sortable">@lang('platform/users::users/table.email')</th>
			<th class="sortable">@lang('platform/users::users/table.activated')</th>
			<th class="sortable">@lang('platform/users::users/table.registered')</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<tr data-template>
			<td>[[ id ]]</td>
			<td>[? if first_name ?] [[ first_name ]] [? else ?] - [? endif ?]</td>
			<td>[? if last_name ?] [[ last_name ]] [? else ?] - [? endif ?]</td>
			<td><a href="mailto:[[email]]">[[email]]</a></td>
			<td>
				[? if activated == 1 ?]
					@lang('general.yes')
				[? else ?]
					@lang('general.no')
				[? endif ?]
			</td>
			<td>[[ created_at ]]</td>
			<td>
				<div class="btn-group">
					<a href="{{ URL::toAdmin('users/edit/[[id]]') }}" class="btn btn-small">
						@lang('button.edit')
					</a>

					[? if id != {{ Sentry::getId() }} ?]
						<a href="{{ URL::toAdmin('users/delete/[[id]]') }}" class="btn btn-small btn-danger">
							@lang('button.delete')
						</a>
					[? endif ?]
				</div>
			</td>
		</tr>
	</tbody>
</table>

<hr>
