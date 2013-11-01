### Notable Changes

We have removed TempoJs as our templating engine in favour of Underscorejs. With
this change, we made updates to the javascript plugin and the way the front-end
templates are developed and included.

----------

### data-grid.js

The only notable change within the jQuery plugin is the setting for `templateOptions`
has now been converted to `templateSettings` to match with Underscore's `_.templateSettings`.
Just like before we ship default settings for the Underscore braces of `<% ... %>` but you
can change the brace syntax to anything you want.

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
