<?php

namespace xiio;

use Jasny\DotKey;

class ArrayManipulator
{
	/**
	 * Untouched input array
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

	public function group_by($field)
	{
		if (!$this->isEmpty()) {
			$result = array();
			foreach ($this->data as $key => $row) {
				$dotkey = DotKey::on($row);
				if (!$dotkey->exists($field)) continue;
				$key = $dotkey->get($field);
				if (!isset($result[$key])){
					$result[$key] = [];
				}
				$result[$key][] = $row;
			}
			$this->data = $result;
		}

		return $this;
	}

	/**
	 * Group elements aware of count. If is more then one row make array if is only one set it as value
	 * @return ArrayManipulator
	 */
	public function compact()
	{
		$result = array();
		foreach ($this->data as $key => $row) {
			if (count($row) == 1) {
				$array = array_slice($row, 0, 1);
				$result[ $key ] = array_shift($array);
			} else {
				$result[ $key ] = $row;
			}
		}
		$this->data = $result;
		return $this;
	}

	/**
	 * Filter array by field name and value
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
			if ($dotkey->exists($field) && $dotkey->get($field)===$value){
				$result[$key] = $row;
			}
		}
		$this->data = $result;
		return $this;
    }

	public function excludeFields(array $fields)
    {
        $result = array();
		foreach ($this->data as $key => $row) {
			$dotkey = DotKey::on($row);
			foreach($fields as $field){
				$result[] = $dotkey->remove($field);
			}
		}
		$this->data = $result;
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

	public function isEmpty()
	{
		return empty($this->data);
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
	 * Reset any changes done with array
	 * @return ArrayManipulator
	 */
    public function reset()
    {
       $this->data = $this->init_data;
       return $this;
    }

}