### Notable Changes

We have removed tempojs as our templating engine in favour of underscorejs. With
this chanage, we made updates to the javascript plugin and the way the front-end
templates are development and included.

----------

### data-grid.js

The only notable change within the jQuery Plugin is the setting for `templateOptions`
has now be converted to `templateSettings` to match better with underscore's `_.templateSettings`.
Just like before you can change the brace syntax to anything you want, we ship using
the default underscore braces of `<% ... %>`.

----------

### The HTML

Data Grid's templates are now required to be in a `script` tag. As this allows, you
the most flexiblity to edit the templates from text files and use `@include` to include
them. The script tags for the templates require a few attributes to be set, and must
stick to our strict name convention. To Start your `script` tags must be set to
`type="text/template"`, along with a `data-grid` attribute that matches the key you
set in the plugin options. Templates must have an `id` that matches the passed element
within your plugin instantiation, appened by `-tmpl`.

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
please refer to their [documentation](http://underscorejs.org/)

Please note that if you pass `undefined` values in an object to a template you will get an error,
please refer to this [github issues](https://github.com/jashkenas/underscore/issues/237)
