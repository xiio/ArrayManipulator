# ArrayManipulator
Convenient way to manipulate arrays.

#Installation
`composer require xiio/array-manipulator dev-master`

#Features
	* Factory method init(array $array)
	* apply($callback)
	* call($method_name, $arguments = [], $set_return_as_value = TRUE)
	* compact($recursive = TRUE)
	* concatWS(array $fields, $glue, $newFieldName, $leaveConcatFields = TRUE)
	* filter($field, $value)
	* flat($key, $value)
	* get()
	* groupBy($field)
	* isEmpty()
	* leaveFields(array $fields)
	* removeFields(array $fields)
	* reset()
	* setArray(array $array)
	* toArray($recursive = TRUE)