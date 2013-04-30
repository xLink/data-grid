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
 ;(function($, window, document, undefined){

	'use strict';

	var defaults = {
		source: undefined,
        sort: {
            column: undefined,
            direction: 'asc'
        },
		dividend: 10,
		threshold: 20,
		throttle: 500,
		type: 'infiniteload',
        tempoOptions: {
            var_braces: '\\[\\[\\]\\]',
            tag_braces: '\\[\\?\\?\\]'
        },
        loader: undefined,
        callback: undefined
	};


	function DataGrid(key, results, pagination, filters, options){

		this.opt = $.extend({}, defaults, options);

		//Binding Key
		this.key = '[data-key='+key+']';

		//Common Selectors
		this.$results = $(results + this.key);
		this.$pagi = $(pagination + this.key);
		this.$filters = $(filters + this.key);
		this.$body = $(document.body);

		//Get Our Source
		this.source = this.$results.data('source') || this.opt.source;

		//Helpers
		this.appliedFilters = [];
		this.templates = {};
		this.killScroll = false;
		this.pagination = 1;
		this.isActive = false;
		this.orgThrottle = this.opt.throttle;
		this.sort = {
			column: this.opt.sort.column,
			direction: this.opt.sort.direction
		};

		this._init();

	}

	DataGrid.prototype = {

		_init: function(){

			if(window.console && window.console.log){
				console.log('%c Data-Grid initialized...', 'background: #222; color: #bada55');
			}

			//Check Dependencies
			this._checkDeps();

			//Find Our Templates
            this._prepTemplates();

            //Event Listners
            this._events();

            //Go Go Gadget
            this._fetch();


		},

		_checkDeps: function(){

			if (typeof Tempo === 'undefined') {
				$.error('$.datagrid requires TempoJS v2.0.0 or later to run.');
			}

			if(!this.$results.length){
				$.error('$.datagrid requires a results container');
			}

			if(!this.$pagi.length){ //might not need this check
				$.error('$.datagrid requires a pagination container');
			}

			if(!this.$filters.length){
				$.error('$.datagrid requires an applied filters container');
			}

		},

		_prepTemplates: function(){

			this.templates.results = Tempo.prepare(this.$results, this.opt.tempoOptions);
			this.templates.pagination = Tempo.prepare(this.$pagi, this.opt.tempoOptions);
			this.templates.appliedFilters = Tempo.prepare(this.$filters, this.opt.tempoOptions);

		},

		_events: function(){

			var self = this;

			//Sorting
			this.$body.on('click', '[data-sort]'+this.key, function(e){
				self._setSorting($(this).data('sort'));
				self.templates.results.clear();
				self._fetch();
			});

			//Filters
			this.$body.on('click', '[data-filter]'+this.key, function(e){
				self._setFilters($(this).data('filter'));
				self.templates.results.clear();
				self._fetch();
			});

			//Search
			var timeout;
			this.$body.find('[data-search]'+this.key).on('submit keyup', function(e){

				var $input = $(this).find('input'),
					$column = $(this).find('select'),
					values = $(this).serializeArray();

				if(e.type === 'submit'){

					self.isActive = true;

					clearTimeout(timeout);

					if(values[0].value === 'all'){
						self._setFilters(values[1].value);
					}else{
						self._setFilters(values[1].value+':'+values[0].value);
					}

					self.templates.results.clear();
					self._fetch();

					$input.val('');
					$column.prop('selectedIndex',0);

					return false;

				}

				if(e.type === 'keyup'){

					if(self.isActive){ return; }

					clearTimeout(timeout);

					timeout = setTimeout(function(){

						if($column.val() === 'all'){
							self._setFilters($input.val());
						}else{
							self._setFilters($input.val()+':'+$column.val());
						}

						self.templates.results.clear();
						self._fetch();

						$input.val('');
						$column.prop('selectedIndex',0);

					}, 800);

				}

			});

			//Remove Filter
			this.$filters.on('click', 'li', function(e){
				self._removeFilter($(this).index());
				self.templates.appliedFilters.render(self.appliedFilters);
				self._fetch();
			});

			this.$body.on('click', '[data-reset]'+this.key, function(e){
				self._reset();
			});

			//Pagination
			this.$pagi.on('click', '[data-page]', function(e){
				var pageId;

				e.preventDefault();

				if(self.opt.type === 'pages'){

					pageId = $(this).data('page');

					self.templates.pagination.clear();
					self.templates.results.clear();

				}

				if(self.opt.type === 'infiniteload'){

					pageId = $(this).data('page');
					$(this).data('page', ++pageId);
				}

				self._goToPage(pageId);
				self._fetch();

			});

			//Update Throttle
			this.$pagi.on('click', '[data-throttle]', function(e){

				self.opt.throttle += self.orgThrottle;
				self.templates.pagination.clear();
				self.templates.results.clear();
				self._fetch();

			});

		},

		_setFilters: function(filter){

			var self = this;


			//lets make sure its a word
			// and not just spaces
			if(!$.trim(filter).length){ return; }


			//Apply Filter and make sure its not already set
			$.each(filter.split(', '), function(i, val){

				var filteredItems = val.split(':');

				$.each(self.appliedFilters, function(i, f){

					if(f.value === filteredItems[0]){

						filteredItems.splice($.inArray(f.value, filteredItems), 1);
						filteredItems.splice($.inArray(f.column, filteredItems), 1);

					}

				});

				if(filteredItems.length > 0){

					self.appliedFilters.push({
						value: filteredItems[0],
						column: filteredItems[1]
					});

					self.templates.appliedFilters.render(self.appliedFilters);
				}

			});


		},

		_removeFilter: function(idx){

			this.templates.results.clear();
			this.appliedFilters.splice(idx, 1);

		},

		_setSorting: function(column){

			var self = this;
			var sortable = column.split(':');
			var direction = typeof sortable[1] !== 'undefined' ? sortable[1] : 'asc';

			if(sortable[0] === this.sort.column){

				this.sort.direction = (this.sort.direction === 'asc') ? 'desc' : 'asc';

			}else{

				this.sort.column = sortable[0];
				this.sort.direction = direction;

			}

		},

		_fetch: function(){

			var self = this;

			this._loader();

				$.ajax({
					url : this.source,
					dataType: 'json',
					data: this._buildFetchData()
				})
				.done(function(response){
					self._loader();

					self.killScroll = false;
					self.isActive = false;

					self.totalPages = response.pages_count;

					if(self.opt.type === 'pages'){
						self.templates.results.render(response.results);
					}else{
						self.templates.results.append(response.results);
					}

					self.templates.pagination.render(self._buildPagination(response.pages_count, response.total_count));

				})
				.error(function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.status + ' ' + errorThrown);
				});

			this._callback();

		},

		_buildFetchData: function(){

			var self = this;

			var params = {
				page: this.pagination,
				// dividend: this.opt.dividend,
				// threshold: this.opt.threshold,
				// throttle: this.opt.throttle,
				filters: [],
				sort: '',
				direction: ''
			};

			$.map(this.appliedFilters, function(n){

				if(typeof n.column === 'undefined'){
					params.filters.push(n.value);
				}else{

					var newFilter = {};
					newFilter[n.column] = n.value;
					params.filters.push(newFilter);

				}

			});

			//if we are sorting
			if(typeof this.sort.column !== 'undefined'){
				params.sort = this.sort.column;
				params.direction = this.sort.direction;
			}

			return $.param(params);

		},

		_buildPagination: function(pages_count, total_count){

			var self = this,
				pagiNav = [],
				pagiData;

			if(this.opt.type === 'pages'){

				var newPerPage = Math.ceil(self.opt.throttle / self.opt.dividend);

				for(var i = 1; i <= (total_count > self.opt.throttle ? self.opt.dividend + 1 : self.opt.dividend); i++){

					if(i <= self.opt.dividend){

						pagiData = {
							page: i,
							pageStart: i === 1 ? 1 : (newPerPage * (i - 1) + 1),
							pageLimit: i === 1 ? newPerPage : (total_count < self.opt.throttle && i === self.opt.dividend) ? total_count : newPerPage * i,
							active: self.pagination === i ? true : false,
							throttle: false
						};

					}else{

						if(total_count > self.opt.throttle){
							pagiData = {
								throttle: true,
								label: 'More'
							};
						}

					}

					pagiNav.push(pagiData);

				}

			}


			if(this.opt.type === 'infiniteload'){

				//Do Stuff
				pagiData = {
					page: self.pagination,
					active: true,
					infiniteload: true
				};

				pagiNav.push(pagiData);

			}


			return pagiNav;

		},

		_goToPage: function(idx){

			if(isNaN(idx = parseInt(idx, 10))){
				idx = 1;
			}

			this.pagination = idx;

		},

		_loader: function(){

			if($(this.opt.loader).is(':visible')){
				$(this.opt.loader).fadeOut();
			}else{
				$(this.opt.loader).fadeIn();
			}

		},

		_trigger: function(params){
			var self = this;

			$.each(params, function(k, v){

				if(k === 'sort'){
					self._setSorting(v);
				}

				if(k === 'filter'){
					self._setFilters(v);
				}

			});

			this.templates.results.clear();
			this._fetch();
		},

		_reset: function(){
			this.appliedFilters = [];
			this.pagination = 1;
			this.sort = {
				column: this.opt.sort.column,
				direction: this.opt.sort.direction
			};
			this.templates.appliedFilters.clear();
			this.templates.results.clear();
			this._fetch();
		},

		_callback: function(){

            if(this.opt.callback !== undefined && $.isFunction(this.opt.callback)){
                this.opt.callback(this.appliedFilters, this.sort, this.pagination);
            }

		}

	};

	$.datagrid = function(key, results, pagination, filters, options){
		return new DataGrid(key, results, pagination, filters, options);
	};

})(jQuery, window, document);