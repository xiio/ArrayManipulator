<?php

namespace xiio;

use Jasny\DotKey;

/**
 * Class ArrayManipulator
 * @package xiio
 */
class ArrayManipulator
{

	/**
	 * Readonly input array
	 * @var array
	 */
	private $init_data;

	/**
	 * Processed array
	 * @var array
	 */
	private $data;

	public function __construct(array $array)
	{
		$this->setArray($array, TRUE);
	}

//methods
	/**
	 * Apply callback function for every element
	 *
	 * @param       $callback
	 *
	 * @return $this
	 */
	public function apply($callback)
	{
		if (!is_callable($callback)) {
			throw new \InvalidArgumentException('$callable parameter should be callable.');
		}
		foreach ($this->data as $key => $element) {
			$this->data[ $key ] = call_user_func($callback, $element);
		}

		return $this;
	}

	/**
	 * Call method on all objects elements if method exist
	 *
	 * @param       $method_name
	 * @param array $arguments
	 * @param bool  $set_return_as_value
	 *
	 * @return $this
	 */
	public function call($method_name, $arguments = [], $set_return_as_value = TRUE)
	{
		foreach ($this->data as $key => $element) {
			if (is_object($element) && method_exists($element, $method_name)) {
				$result = call_user_func_array([$element, $method_name], $arguments);
				if (TRUE === $set_return_as_value) {
					$this->data[ $key ] = $result;
				}
			}
		}
		return $this;
	}

	/**
	 * Group elements aware of count and type. If is more then one row make array if is only one set it as value
	 *
	 * @param bool $recursive
	 *
	 * @return \xiio\ArrayManipulator
	 */
	public function compact($recursive = TRUE)
	{
		$result = [];
		foreach ($this->data as $key => $item) {
			$result[ $key ] = $this->do_compact($item, $recursive);
		}
		$this->data = $result;

		return $this;
	}

	/**
	 * Concat fields with separator
	 *
	 * @param array $fields            fields to concat
	 * @param       $glue              Separator
	 * @param       $newFieldName      Name of field where to store concatenated value
	 * @param bool  $leaveConcatFields Leave $fields untouched if TRUE. Remove fields if FALSE. Default: TRUE
	 *
	 * @return $this
	 */
	public function concatWS(array $fields, $glue, $newFieldName, $leaveConcatFields = TRUE)
	{
		foreach ($this->data as $key => $item) {
			$concat_values = [];
			$dotkey = DotKey::on($item);
			foreach ($fields as $concat_field_name) {
				$concat_values[] = $dotkey->get($concat_field_name);
				if (FALSE === $leaveConcatFields) {
					$dotkey->remove($concat_field_name);
				}
			}
			$this->data[ $key ] = $dotkey->set($newFieldName, implode($glue, $concat_values));
		}

		return $this;
	}

	/**
	 * Filter array by field name and value
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return ArrayManipulator
	 */
	public function filter($field, $value)
	{
		$result = array();
		foreach ($this->data as $key => $row) {
			$dotkey = DotKey::on($row);
			if ($dotkey->exists($field) && $dotkey->get($field) === $value) {
				$result[ $key ] = $row;
			}
		}
		$this->data = $result;

		return $this;
	}

	/**
	 * Gets data with modifications
	 * @return array
	 */
	public function get()
	{
		return $this->data;
	}

	/**
	 * Group by given field. You can use path ex.: meta.creator.name
	 * If field not exist, element will be exluded from result
	 *
	 * @param string $field path to field
	 *
	 * @return $this
	 */
	public function groupBy($field)
	{
		if (!$this->isEmpty()) {
			$result = array();
			foreach ($this->data as $key => $row) {
				$dotkey = DotKey::on($row);
				if (!$dotkey->exists($field)) continue;
				$key = $dotkey->get($field);
				if (!isset($result[ $key ])) {
					$result[ $key ] = [];
				}
				$result[ $key ][] = $row;
			}
			$this->data = $result;
		}

		return $this;
	}

	/**
	 * Factory method to creates instanace of ArrayManipulator
	 *
	 * @param array $array
	 *
	 * @return \xiio\ArrayManipulator
	 */
	public static function init(array $array)
	{
		$am = new ArrayManipulator($array);

		return $am;
	}

	public function isEmpty()
	{
		return empty($this->data);
	}

	/**
	 * Leave fields provided in array $fields
	 *
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function leaveFields(array $fields)
	{
		$result = [];
		foreach ($this->data as $key => $element) {
			if (is_array($element)) {
				$result[$key] = $this->leave_fields_array($element, $fields);
			} elseif (is_object($element)) {
				$result[$key] = $this->leave_fields_object($element, $fields);
			} else {
				$result[$key] = $element;
			}
		}
		$this->data = $result;

		return $this;
	}

	/**
	 * Remove fields provided in array $fields
	 *
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function removeFields(array $fields)
	{
		$result = array();
		foreach ($this->data as $key => $row) {
			$dotkey = DotKey::on($row);
			$result_row = [];
			foreach ($fields as $field) {
				$result_row = $dotkey->remove($field);
			}
			$result[] = $result_row;
		}
		$this->data = $result;

		return $this;
	}

	/**
	 * Reset any changes done with array
	 * @return ArrayManipulator
	 */
	public function reset()
	{
		$this->data = $this->init_data;

		return $this;
	}

	/**
	 * Set new array for manipulator.
	 *
	 * @param array $array
	 *
	 * @return ArrayManipulator
	 */
	public function setArray(array $array)
	{
		$this->init_data = $array;
		$this->data = $array;

		return $this;
	}

	/**
	 * Convert all object elements to array
	 *
	 * @param bool $recursive
	 *
	 * @return $this
	 */
	public function toArray($recursive = TRUE)
	{
		$this->data = array_map(function ($element) use ($recursive) {
			return is_object($element) ? json_decode(json_encode($element), $recursive) : $element;
		}, $this->data);

		return $this;
	}

	protected function compact_array($item, $recursive)
	{
		if (count($item) == 1) {
			$values = array_values($item);
			if (!is_scalar($values[0]) && TRUE === $recursive) {
				$item = $this->do_compact($values[0]);
			} else {
				$array = array_slice($item, 0, 1);
				$item = array_shift($array);
			}
		}

		return $item;
	}

	protected function compact_object($item)
	{
		$obj_fields = get_object_vars($item);
		$values = array_values($obj_fields);
		if (count($obj_fields == 1) && is_scalar($values[0])) {
			$item = $values[0];
		}

		return $item;
	}

	protected function do_compact($items, $recursive = TRUE)
	{
		if (is_array($items)) {
			return $this->compact_array($items, $recursive);
		} elseif (is_object($items)) {
			return $this->compact_object($items);
		} else {
			return $items;
		}
	}

	protected function leave_fields_array(array $array, array $fields)
	{
		$result = [];
		$result_dotkey = DotKey::on([]);
		$dotkey = DotKey::on($array);
		foreach ($fields as $field) {
			if (!$dotkey->exists($field)) continue;
			$result = $result_dotkey->put($field, $dotkey->get($field));
		}

		return $result;
	}

	protected function leave_fields_object($object, array $fields)
	{
		$obj_fields = get_object_vars($object);
		$leave_fields_values = [];
		$dotkey = DotKey::on($object);
		foreach ($fields as $field) {
			$leave_fields_values[ $field ] = $dotkey->get($field);
		}
		foreach ($obj_fields as $field_name => $field_value) {
			$object = $dotkey->remove($field_name);
		}
		foreach ($leave_fields_values as $field_name => $value) {
			$object = $dotkey->put($field_name, $value);
		}

		return $object;
	}
}



