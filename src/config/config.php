<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Data Handler Mappings
	|--------------------------------------------------------------------------
	|
	| Here you may specify any "data handlers" which handle the dataset given
	| to a data grid instance. The key is the class which handles the data
	| and the value is a closure which must return true. There may be multiple
	| classes which can handle the same data type but only one for that
	| specific data set.
	|
	| Supported: Any class which implements:
	|            Cartalyst\DataGrid\DataHandlers\DataHandlerInterface
	|
	*/
	'handlers' => array(

		'Cartalyst\DataGrid\DataHandlers\EloquentDataHandler' => function($data)
		{
			return (
				$data instanceof Illuminate\Database\Eloquent\Builder or
				$data instanceof Illuminate\Database\Query\Builder
			);
		},

		'Cartalyst\DataGrid\DataHandlers\ArrayDataHandler' => function($data)
		{
			return is_array($data);
		},

	),

);
