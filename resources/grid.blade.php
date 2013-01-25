<div id="grid" data-source="http://example.com/grid/source" data-results=".grid-results" data-filters=".grid-filters" data-applied-filters=".grid-applied-filters" data-pagination=".grid-pagination">

	<div class="grid-filters">
		<div class="form-inline">
			<div class="input-append">
				<input type="text" placeholder="Filter All">
				<button class="btn add-global-filter">
					Add
				</button>
			</div>
		</div>

		<hr>

		<div class="clearfix">
			<div class="form-inline">

				<div class="pull-left" data-template>

					<!-- Build different HTML based on the type -->
					[? if type == 'select' ?]
						<select class="input-small" id="grid-filters-[[column]]" data-column="[[column]]">
							<option value="">
								-- [[label]] --
							</option>

							<!-- Need to work out how to embed each <option> inside the <optgroup> data-template... -->
							<optgroup data-template-for="mappings" label="Option">
								<option value="[[value]]">
									[[label]]
								</option>
							</optgroup>
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
			<li data-template>
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
						<th data-column="country">Country</th>
						<th data-column="activated">Activated</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr data-template>
						<td data-column="id">[[id]]</td>
						<td data-column="first_name">[[first_name]]</td>
						<td data-column="last_name">[[last_name]]</td>
						<td data-column="country">
							[? if country ?]
								[[country]]
							[? endif ?]
						</td>
						<td data-type="select" data-column="activated" data-mappings="Yes:1|No:0">
							[? if activated == 1 ?]
								Yes
							[? else ?]
								No
							[? endif ?]
						</td>
						<td data-static>
							<a href="{{ URL::to(ADMIN_URI.'/users/edit') }}/[[id]]">Edit [[first_name]]</a>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>

</div>
