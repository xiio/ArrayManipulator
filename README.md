# ArrayManipulator
Convenient way to manipulate arrays.

#Installation
`composer require xiio/array-manipulator dev-master`

#Features
	* Factory method init(array $array)
	* compact($recursive = TRUE)
	* concatWS(array $fields, $glue, $newFieldName, $leaveConcatFields = TRUE)
	* filter($field, $value)
	* get()
	* groupBy($field)
	* isEmpty()
	* leaveFields(array $fields)
	* removeFields(array $fields)
	* reset()
	* setArray(array $array)