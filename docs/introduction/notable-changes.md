### Notable Changes

We have removed TempoJs as our templating engine in favour of Underscorejs. With
this change, we made updates to the javascript plugin and the way the front-end
templates are developed and included.

----------

### data-grid.js

The Data-Grid plugin has be refactored from the top down, along with adding in new features.
Data-Grid, now pushes its current state to the url, for linkable and sharable content. We
use the `history` api when its supported and fallback to `window.location.hash` for unsupported
browsers.

A few of the `options` that you set on instantiation of the plugin have been renamed.
Below is the current list of options that have been changed from version 1;

~~type~~ : is now : paginationType <br>
~~ascClass~~ and ~~descClass~~ : is now : sortClass <br>
~~sort~~ : is now : defaultSort <br>
~~tempoOptions~~ : is now : templateSettings <br>
~~searchThreshold~~ : is now : searchTimeout <br>

**Complete Option List**

	var defaults = {
			source: null,
			dividend: 1,
			threshold: 100,
			throttle: 100,
			paginationType: 'single',
			sortClasses: {
				asc: 'asc',
				desc: 'desc'
			},
			defaultSort: {},
			templateSettings : {
				evaluate    : /<%([\s\S]+?)%>/g,
				interpolate : /<%=([\s\S]+?)%>/g,
				escape      : /<%-([\s\S]+?)%>/g
			},
			searchTimeout: 800,
			loader: undefined,
			callback: undefined
		};

We have also made changes to the pagination object that we pass to the respective template.
Now the pagination object will containt `totalCount` and `filteredCount` so you can access
them directly without using the `callback`.

----------

### The HTML

Data Grid's templates are now required to be inside a `script` tag, as this allows you
the most flexiblity to edit the templates from text files and use `@include` to include
them.

The script tags for the templates require a few attributes to be set, and must
stick to our strict name convention. That said, your `script` tags must be set to
`type="text/template"`, along with a `data-grid` attribute that matches the key you
set in the plugin options.

Templates must have an `id` that matches the passed element
within your plugin instantiation, appended by `-tmpl`.

** Example Template Setup **

	<script type="text/template" data-grid="main" id="results-tmpl">
		...
	</script>

We suggest putting templates into their own files and using `@include` to load
the templates into the views you want.

Please note that there is no need for `data-template` anymore.

----------

### Underscore

For more information on [underscorejs](http://underscorejs.org/) and their [templates](http://underscorejs.org/#template),
please refer to their [documentation](http://underscorejs.org/).

Please note that if you pass `undefined` values in an object to a template you will get an error,
please refer to this [github issues](https://github.com/jashkenas/underscore/issues/237)
