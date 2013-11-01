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
        source: null,
        dividend: 1,
        threshold: 100,
        throttle: 100,
        type: 'multiple',
        loader: undefined,
        ascClass: 'asc',
        descClass: 'desc',
        sort: {},
        searchThreshold: 800,
        callback: undefined,
        templateSettings : {
            evaluate    : /<%([\s\S]+?)%>/g,
            interpolate : /<%=([\s\S]+?)%>/g,
            escape      : /<%-([\s\S]+?)%>/g
        }
    };
    var helpers = {
        tmpl: {},
        appliedFilters: [],
        pageIdx: 1,
        nextIdx: null,
        prevIdx: null,
        totalPages: null,
        isActive: false,
        pagiThrottle: null,
        totalCount: null,
        filterCount: null
    };

    function DataGrid(grid, results, pagination, filters, options) {

        if( typeof _ === 'undefined' ) {
            this._log('Underscore is not defined. DataGrid Requires UnderscoreJS v 1.5.2 or later to run!');
            return false;
        }

        this.opt = $.extend({}, defaults, options, helpers);

        // Set _ templates interpolate
        _.templateSettings = {
            evaluate    : this.opt.templateSettings.evaluate,
            interpolate : this.opt.templateSettings.interpolate,
            escape      : this.opt.templateSettings.escape
        };

        // Binding Key
        this.grid = '[data-grid=' + grid + ']';

        // Cache Our Selectors
        this.$results = $(results + this.grid);
        this.$pagination = $(pagination + this.grid);
        this.$filters = $(filters + this.grid);
        this.$body = $(document.body);

        // Get Our Source
        this.opt.source = this.$results.data('source') || this.opt.source;

        // Default Throttle
        this.opt.source = this.$results.data('source') || this.opt.source;

        // Prep and Cache Our Templates
        this._prepTemplates(results, pagination, filters);

        // If Our Results Element is a table lets get the <tbody>
        if ( this.$results.get(0).tagName.toLowerCase() === 'table') {
            this.$results = $(results + this.grid).find('tbody');
        }

        this._init();

    }

    DataGrid.prototype  = {

        _init: function() {

            this._addListeners();

            this._ajaxFetch();

        },

        _prepTemplates: function(results, pagination, filters) {

            results = $('#'+results.substr(1)+'-tmpl' + this.grid);
            pagination = $('#'+pagination.substr(1)+'-tmpl' + this.grid);
            filters = $('#'+filters.substr(1)+'-tmpl' + this.grid);

            if( results.length ) {
                this.opt.tmpl['results'] = _.template( results.html() );
            }else{
                this._log('Missing a results container, make sure you have data-grid set!');
            }

            if( pagination.length ) {
                this.opt.tmpl['pagination'] = _.template( pagination.html() );
            }else{
                this._log('Missing a pagination container, make sure you have data-grid set!');
            }

            if( filters.length ) {
                this.opt.tmpl['filters'] = _.template( filters.html() );
            }else{
                this._log('Missing a applied filters container, make sure you have data-grid set!');
            }


        },

        _addListeners: function() {

            var _this = this;

            this.$body.on('click', '[data-sort]'+this.grid, function(e){
                _this._setSortDirection($(this));
                _this._setSort($(this).data('sort'));
                _this._clearResults();
                _this._ajaxFetch();
            });

            this.$body.on('click', '[data-filter]'+this.grid, function(e) {
                _this._setFilter($(this).data('filter'), $(this).data('label'));
                _this._clearResults();
                _this._goToPage(1);
                _this._ajaxFetch();
            });

            this.$pagination.on('click', '[data-page]', function(e) {

                e.preventDefault();

                var pageIdx;

                if( _this.opt.type === 'single' || _this.opt.type === 'multiple') {

                    pageIdx = $(this).data('page');
                    _this.$pagination.empty();
                    _this._clearResults();

                }

                if( _this.opt.type === 'infinite' ) {

                    pageIdx = $(this).data('page');
                    $(this).data('page', ++pageIdx);

                }

                _this._goToPage(pageIdx);
                _this._ajaxFetch();

            });

            this.$pagination.on('click', '[data-throttle]', function(e) {

                _this.opt.throttle += _this.opt.pagiThrottle;
                _this.$pagination.empty();
                _this._clearResults();
                _this._ajaxFetch();

            });

            this.$filters.on('click', '> *', function(e) {

                _this._removeFilters($(this).index());
                _this._ajaxFetch();

            });

            var timeout;
            this.$body.on('submit keyup', '[data-search]'+this.grid, function(e){

                e.preventDefault();

                var $input = $(this).find('input');
                var $select = $(this).find('select');

                if(e.type === 'submit'){

                    if(!$.trim($input.val()).length){
                        return;
                    }

                    _this.isActive = true;

                    clearTimeout(timeout);

                    _this._setFilter($select.val()+':'+$input.val());
                    _this._clearResults();
                    _this._goToPage(1);
                    _this._ajaxFetch();

                    $input.val('').data('old', '');
                    $select.prop('selectedIndex',0);

                    return false;

                }

                if(e.type === 'keyup' && e.keyCode !== 13){

                    if(_this.opt.isActive){
                        return;
                    }

                    clearTimeout(timeout);

                    timeout = setTimeout(function(){

                        _this._liveSearch($input.val(), $input.data('old'), $select.val());
                        $input.data('old', $input.val());

                        _this._goToPage(1);
                        _this._ajaxFetch();

                    }, _this.opt.searchThreshold);

                }

            });

            this.$body.on('click', '[data-reset]'+this.grid, function(e) {
                e.preventDefault();
                _this._reset();
                _this._ajaxFetch();
            });

        },

        _liveSearch: function(curr, old, column) {

            this.opt.throttle = this.opt.pagiThrottle;

            if(curr !== old){

                for(var i = 0; i < this.opt.appliedFilters.length; i++){

                    if(this.opt.appliedFilters[i].value === old){

                        this.opt.appliedFilters.splice(i, 1);

                    }

                }

                if(curr.length){

                    this.opt.appliedFilters.push({
                        column: column === 'all' ? 'all' : column,
                        columnLabel: column === 'all' ? 'all' : column,
                        value: curr,
                        valueLabel: curr,
                        type: 'live'
                    });

                }

                this._clearResults();

            }

        },

        _setFilter: function(filter, label) {

            this.opt.throttle = this.opt.pagiThrottle;

            var arr = filter.split(', ');

            for(var i = 0; i < arr.length; i++){

                var values = arr[i].split(':');

                //Check to See if its appled
                for(var j = 0; j < this.opt.appliedFilters.length; j++){

                    if(this.opt.appliedFilters[j].value === values[1]){

                        if(this.opt.appliedFilters[j].type === 'live'){
                            this.opt.appliedFilters.splice(j, 1);
                        }else{
                            values.splice(j, 2);
                        }

                    }

                }

                if(typeof label !== 'undefined'){

                    var larr = label.split(', ');

                    for(var k = 0; k < larr.length; k++){

                        var labels = larr[k].split(':');

                        if(values[0] === labels[0]){
                            values[2] = labels[1];
                        }

                        if(values[1] === labels[0]){
                            values[3] = labels[1];
                        }

                    }

                }


                if(values.length){

                    this.opt.appliedFilters.push({
                        column: values[0] === 'all' ? 'all' : values[0],
                        columnLabel: typeof values[2] === 'undefined' ? values[0] : values[2],
                        value: values[1],
                        valueLabel: typeof values[3] === 'undefined' ? values[1] : values[3],
                        type: 'normal'
                    });

                    this.$filters.html( this.opt.tmpl['filters']( { filters: this.opt.appliedFilters } ) );

                }

            }

        },

        _setSortDirection: function(el) {

            $('[data-sort]'+this.grid).not(el).removeClass(this.opt.ascClass);
            $('[data-sort]'+this.grid).not(el).removeClass(this.opt.descClass);

            if( el.hasClass(this.opt.ascClass) ) {
                el.removeClass(this.opt.ascClass).addClass(this.opt.descCLass);
            }else{
                el.removeClass(this.opt.descClass).addClass(this.opt.ascClass);
            }

        },

        _setSort: function(sort) {

            var arr = sort.split(':'),
                direction = typeof arr[1] !== 'undefined' ? arr[1] : 'asc';

            if( arr[0] === this.opt.sort.column ) {

                this.opt.sort.direction = (this.opt.sort.direction === 'asc') ? 'desc' : 'asc';

            }else{

                this.opt.sort.column = arr[0];
                this.opt.sort.direction = direction;

            }

        },

        _ajaxFetch: function() {

            var _this = this;

            this._loading();

            $.ajax({
                url: this.opt.source,
                dataType: 'json',
                data: this._buildFetchData()
            })
            .done(function(json) {

                _this.opt.isActive = false;

                _this.opt.totalCount = json.total_count;
                _this.opt.filterCount = json.filtered_count;
                _this.opt.nextIdx = json.next_page;
                _this.opt.prevIdx = json.previous_page;
                _this.opt.totalPages = json.pages_count;

                if( _this.opt.type !== 'infinite' ) {
                    _this.$results.empty();
                }

                if( _this.opt.type === 'single' || _this.opt.type === 'multiple' ) {
                    _this.$results.html( _this.opt.tmpl['results'](json) );
                }else{
                    _this.$results.append( _this.opt.tmpl['results'](json) );
                }

                _this.$pagination.html( _this.opt.tmpl['pagination']( { pagination: _this._buildPagination(json) } ));

                if(json.pages_count <= 1 && _this.opt.type === 'infinite') {
                    _this.$results.empty();
                }

                _this._loading();
                _this._callback()

            })
            .error(function(jqXHR, textStatus, errorThrown) {
                _this._log('ajaxFetch ' + jqXHR.status, errorThrown);
            });

        },

        _buildFetchData: function() {

            var params = {};

            params.page = this.opt.pageIdx;
            params.dividend = this.opt.dividend;
            params.threshold = this.opt.threshold;
            params.throttle = this.opt.throttle;
            params.filters = [];

            for( var i = 0; i < this.opt.appliedFilters.length; i++) {

                if( this.opt.appliedFilters[i].column === 'all' ) {

                    params.filters.push(this.opt.appliedFilters[i].value);

                }else{

                    var filter = {};
                    filter[this.opt.appliedFilters[i].column] = this.opt.appliedFilters[i].value;
                    params.filters.push(filter);

                }

            }

            if( typeof this.opt.sort.column !== 'undefined' && typeof this.opt.sort.direction !== 'undefined') {
                params.sort = this.opt.sort.column;
                params.direction = this.opt.sort.direction;
            }

            return $.param(params);

        },

        _buildPagination: function(json) {

            var page = json.page,
                next = json.next_page,
                prev = json.previous_page,
                total = json.pages_count;

            var paginationNav = [];

            if( this.opt.type === 'single' ) {

                return this._buildSinglePagination(page, next, prev, total);

            }

            if( this.opt.type === 'multiple' ) {

                return this._buildMultiplePagination(page, next, prev, total);

            }

            if( this.opt.type === 'infinite' ) {

                return this._buildInfinitePagination(page, next, prev, total);

            }

        },

        _buildSinglePagination: function(page, next, prev, total) {

            var params, perPage, paginationNav = [];

            if( this.opt.filterCount !== this.opt.totalCount ) {
                perPage = this._resultsPerPage(this.opt.filterCount, total);
            }else{
                perPage = this._resultsPerPage(this.opt.totalCount, total);
            }


            params = {
                pageStart: perPage === 0 ? 0 : (this.opt.pageIdx === 1 ? 1 : (perPage * (this.opt.pageIdx - 1) + 1)),
                pageLimit: this.opt.pageIdx === 1 ? perPage : (this.opt.totalCount < (perPage * this.opt.pageIdx)) ? this.opt.totalCount : perPage * this.opt.pageIdx,
                prevPage: prev,
                nextPage: next,
                page: page,
                active: true,
                totalPages: total,
                single: true
            };

            paginationNav.push(params);

            return paginationNav;

        },

        _buildMultiplePagination: function(page, next, prev, total) {

            var params, perPage, paginationNav = [];

            if( (this.opt.totalCount > this.opt.throttle) && (this.opt.filterCount > this.opt.throttle) ) {

                perPage = this._resultsPerPage( this.opt.throttle, this.opt.dividend );

                for(var i = 1; i <= this.opt.dividend; i++) {

                    params = {
                        pageStart: perPage === 0 ? 0 : ( i === 1 ? 1 : (perPage * (i - 1) + 1)),
                        pageLimit: i === 1 ? perPage : (this.opt.totalCount < this.opt.throttle && i === this.opt.dividend) ? this.opt.totalCount : perPage * i,
                        prevPage: prev,
                        nextPage: next,
                        page: i,
                        active: this.opt.pageIdx === i ? true : false,
                        throttle: false
                    };

                    paginationNav.push(params);

                }

                if( this.opt.totalCount > this.opt.throttle ) {
                    params = {
                        throttle: true
                    };

                    paginationNav.push(params);
                }

            }else{

                if( this.opt.filterCount !== this.opt.totalCount ) {
                    perPage = this._resultsPerPage( this.opt.filterCount, total);
                }else{
                    perPage = this._resultsPerPage( this.opt.totalCount, total);
                }

                for(var i = 1; i <= total; i++) {

                    params = {
                        pageStart: perPage === 0 ? 0 : ( i === 1 ? 1 : (perPage * (i - 1) + 1)),
                        pageLimit: i === 1 ? perPage : (this.opt.totalCount < (perPage * i)) ? this.opt.totalCount : perPage * i,
                        prevPage: prev,
                        nextPage: next,
                        page: i,
                        active: this.opt.pageIdx === i ? true : false
                    };

                    paginationNav.push(params);

                }

            }

            return paginationNav;

        },

        _buildInfinitePagination: function(page, next, prev, total) {

            var params, paginationNav = [];

            params = {
                page: page,
                infinite: true
            };

            paginationNav.push(params);

            return paginationNav;

        },

        _clearResults: function() {

            if(this.opt.type === 'infinite') {
                this.$results.empty();
            }

        },

        _goToPage: function(idx) {

            if(isNaN(idx = parseInt(idx, 10))){
                idx = 1;
            }

            this.opt.pageIdx = idx;

        },

        _removeFilters: function(idx) {

            var newFilters = [];

            this.$filters.empty();
            this._clearResults();
            this.opt.appliedFilters.splice(idx, 1);

            for(var i = 0; i < this.opt.appliedFilters.length; i++){

                if(this.opt.appliedFilters[i].type === 'normal'){

                    newFilters.push(this.opt.appliedFilters[i]);

                }

            }

            this.$filters.append( this.opt.tmpl['filters']({ filters: newFilters }));

        },

        _reset: function() {

            this.$body.find('[data-sort]').removeClass('asc desc');
            this.$body.find('[data-search]').find('input').val('');
            this.$body.find('[data-search]').find('select').prop('selectedIndex', 0);

            this.opt.appliedFilters = [];
            this.opt.sort = {};
            this.opt.pageIdx = 1;

            this.$filters.empty();
            this.$results.empty();

        },

        _resultsPerPage: function(dividend, divisor) {
            return Math.ceil(dividend / divisor);
        },

        _loading: function() {

            if( $(this.opt.loader).is(':visible') ) {
                $(this.opt.loader).fadeOut();
            }else{
                $(this.opt.loader).fadeIn();
            }

        },

        _log: function() {
            var values;

            values = 1 <= arguments.length ? [].slice.call(arguments, 0) : [];

            if(typeof console !== 'undefined' && console !== null) {
                console.log.apply(console, ['DataGrid »'].concat([].slice.call(values)));
            }
        },

        _callback: function() {

            if( this.opt.callback !== undefined && $.isFunction(this.opt.callback)) {

                this.opt.callback(this.opt);

            }

        }

    };

    $.datagrid = function(grid, results, pagination, filters, options) {
        return new DataGrid(grid, results, pagination, filters, options);
    };

})(jQuery, window, document);
