<?php

namespace spec\xiio;

use Jasny\DotKey;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArrayManipulatorSpec extends ObjectBehavior
{

//methods
	public function getMatchers()
	{
		return [
			'haveKeyWithValueType'      => function ($subject, $key, $type) {
				$exist = array_key_exists($key, $subject);
				$result = FALSE;
				if ($exist && gettype($subject[ $key ]) === $type) {
					$result = TRUE;
				}

				return $result;
			},
			'haveElementKeyWithValue'   => function ($subject, $element_index, $key, $value) {
				if (!isset($subject[ $element_index ])) {
					return FALSE;
				}

				return DotKey::on($subject[ $element_index ])->get($key) === $value;
			},
			'doesNotHaveElementWithKey' => function ($subject, $element_index, $key) {
				if (!isset($subject[ $element_index ])) {
					return TRUE;
				}

				return !DotKey::on($subject[ $element_index ])->exists($key);
			},
			'haveElementWithKey'        => function ($subject, $element_index, $key) {
				if (!isset($subject[ $element_index ])) {
					return FALSE;
				}

				return DotKey::on($subject[ $element_index ])->exists($key);
			},
		];
	}

	function it_can_apply()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->apply(function ($element) {
			return json_decode(json_encode($element),true);
		});
		$this->get()->shouldHaveKeyWithValueType(1, 'array');
		$this->get()->shouldHaveElementWithKey(1, "meta.creator");
		$this->get()->shouldHaveKeyWithValueType(3, 'array');
		$this->get()->shouldHaveElementWithKey(3, "meta.creator");
	}

	function it_can_call()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->call('get_name_and_type', [], TRUE);
		$this->get()->shouldHaveKeyWithValueType(1, 'string');
		$this->get()->shouldHaveKeyWithValueType(3, 'string');
		$this->get()->shouldHaveKeyWithValue(1, 'stdClass 1 object');
		$this->get()->shouldHaveKeyWithValue(3, 'stdClass 2 object');
	}

	function it_can_check_empty()
	{
		$this->isEmpty()->shouldReturn(TRUE);

		$input = ['ss'];
		$this->setArray($input);
		$this->isEmpty()->shouldReturn(FALSE);
	}

	function it_can_group_by_field()
	{
		$input = $this->getExampleArray();

		$this->setArray($input);
		$this->groupBy('type');
		$this->get()
			->shouldHaveCount(2);
		$this->get()
			->shouldHaveKey('array');
		$this->get()
			->shouldHaveKey('object');
	}

	function it_can_group_by_field_depth()
	{
		$input = $this->getExampleArray();

		$this->setArray($input);
		$this->groupBy('meta.creator');
		$this->get()
			->shouldHaveCount(3);
		$this->get()
			->shouldHaveKey('Thomas');
		$this->get()
			->shouldHaveKey('David');
		$this->get()
			->shouldHaveKey('Jasmine');
	}

	function it_can_help_get_select_options()
	{
		$leave_fields = ['name'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->leaveFields($leave_fields);
		$this->groupBy('name');
		$this->compact();
		$this->get()->shouldHaveKeyWithValueType("first", 'string');
		$this->get()->shouldHaveKeyWithValueType("stdClass 1", 'string');
		$this->get()->shouldHaveKeyWithValueType("second", 'string');
		$this->get()->shouldHaveKeyWithValueType("stdClass 2", 'string');
	}

	function it_can_set_array()
	{
		$this->setArray(['test']);
		$this->get()->shouldHaveCount(1);
	}

	function it_compact()
	{
		$array = [
			0 => ['name' => 'Gabriel'],
			1 => ['name' => 'Uriel', 'second'],
			2 => ['name' => 'Gabriel'],
		];
		$this->setArray($array);
		$this->compact();
		$this->get()->shouldHaveKeyWithValue(0, "Gabriel");
		$this->get()->shouldHaveKeyWithValueType(1, 'array');
		$this->get()->shouldHaveKeyWithValue(2, "Gabriel");
	}

	function it_convert_objects_to_array()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->toArray();
		$this->get()->shouldHaveCount(4);
		$this->get()->shouldHaveKeyWithValueType(1, 'array');
		$this->get()->shouldHaveKeyWithValueType(3, 'array');
	}

	function it_exclude_fields()
	{
		$excluded_fields = ['meta.creator', 'type'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->removeFields($excluded_fields);
		$this->get()->shouldHaveCount(4);
		$this->get()->shouldDoesNotHaveElementWithKey(0, "type");
		$this->get()->shouldDoesNotHaveElementWithKey(0, "meta.creator");
		$this->get()->shouldDoesNotHaveElementWithKey(1, "type");
		$this->get()->shouldDoesNotHaveElementWithKey(1, "meta.creator");
		$this->get()->shouldDoesNotHaveElementWithKey(2, "type");
		$this->get()->shouldDoesNotHaveElementWithKey(2, "meta.creator");
		$this->get()->shouldDoesNotHaveElementWithKey(3, "type");
		$this->get()->shouldDoesNotHaveElementWithKey(3, "meta.creator");
	}

	function it_filter()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->filter('meta.creator', 'David');
		$result = $this->get()->shouldHaveCount(2);
	}

	function it_has_factory_method()
	{
		$input = $this->getExampleArray();
		$this::init($input)->shouldHaveType('xiio\ArrayManipulator');
	}

	function it_implode_fields()
	{
		$implode_fields = ['name', 'meta.creator'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->concatWS($implode_fields, " - ", "name", FALSE);
		$this->get()->shouldHaveElementKeyWithValue(0, 'name', 'first - David');
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('xiio\ArrayManipulator');
	}

	function it_leave_fields()
	{
		$leave_fields = ['name', 'meta.creator'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->leaveFields($leave_fields);
		$this->get()->shouldDoesNotHaveElementWithKey(2, "type");
		$this->get()->shouldHaveElementWithKey(2, "name");
		$this->get()->shouldHaveElementWithKey(2, "meta.creator");
	}

	function it_reset_changes()
	{
		$array = [
			0 => ['name' => 'Gabriel'],
			1 => ['name' => 'Uriel'],
			2 => ['name' => 'Gabriel'],
		];
		$this->setArray($array);
		$this->groupBy('name');
		$this->get()->shouldHaveCount(2);
		$this->reset();
		$this->get()->shouldHaveCount(3);
	}

	function it_flat_array_simple()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->flat('id', 'name');
		$this->get()->shouldHaveCount(4);
		$this->get()->shouldHaveKeyWithValue(0, "first");
		$this->get()->shouldHaveKeyWithValue(1, "stdClass 1");
		$this->get()->shouldHaveKeyWithValue(2, "second");
		$this->get()->shouldHaveKeyWithValue(3, "stdClass 2");
	}

	function it_flat_array_complex()
	{
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->flat(['id', '_', 'type'], ['name', ' - ', 'meta.creator']);
		$this->get()->shouldHaveCount(4);
		$this->get()->shouldHaveKeyWithValue("0_array", "first - David");
		$this->get()->shouldHaveKeyWithValue("1_object", "stdClass 1 - Thomas");
		$this->get()->shouldHaveKeyWithValue("2_array", "second - Jasmine");
		$this->get()->shouldHaveKeyWithValue("3_object", "stdClass 2 - David");
	}

	function let($object)
	{
		$this->beConstructedWith([]);
	}

	private function getExampleArray()
	{
		$stdClass = new DummyClass;
		$input = [
			0 => [
				'id'=> 0,
				'name' => "first",
				'type' => "array",
				'meta' => [
					"creator" => 'David'
				]
			],
			1 => call_user_func_array(function ($class) {
				$stubClass = clone $class;
				$stubClass->name .= " 1";
				$stubClass->id = 1;
				$stubClass->meta = [
					"creator" => 'Thomas'
				];

				return $stubClass;
			}, [$stdClass]),
			2 => [
				'id'=> 2,
				'name' => "second",
				'type' => "array",
				'meta' => [
					"creator" => 'Jasmine'
				]
			],
			3 => call_user_func_array(function ($class) {
				$stubClass = clone $class;
				$stubClass->name .= " 2";
				$stubClass->id = 3;
				return $stubClass;
			}, [$stdClass]),
		];

		return $input;
	}

}


class DummyClass
{
	public $id;
	public $name = 'stdClass';
	public $type = 'object';
	public $meta = [
		"creator" => 'David'
	];

	public function get_name_and_type(){
		return $this->name." ".$this->type;
	}
}