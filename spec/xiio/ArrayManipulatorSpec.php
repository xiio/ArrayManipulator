<?php

namespace spec\xiio;

use Jasny\DotKey;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArrayManipulatorSpec extends ObjectBehavior
{

	function it_is_initializable()
	{
		$this->shouldHaveType('xiio\ArrayManipulator');
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

	function it_can_set_array()
	{
		$this->setArray(['test']);
		$this->get()->shouldHaveCount(1);
	}

	function it_can_check_empty()
	{
		$this->isEmpty()->shouldReturn(TRUE);

		$input = ['ss'];
		$this->setArray($input);
		$this->isEmpty()->shouldReturn(FALSE);
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

	function it_compact(){
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

	function it_filter(){
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->filter('meta.creator', 'David');
		$result = $this->get()->shouldHaveCount(2);
	}

	function it_exclude_fields(){
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

	function it_leave_fields(){
		$leave_fields = ['name', 'meta.creator'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->leaveFields($leave_fields);
		$this->get()->shouldDoesNotHaveElementWithKey(2, "type");
		$this->get()->shouldHaveElementWithKey(2, "name");
		$this->get()->shouldHaveElementWithKey(2, "meta.creator");
	}

	function it_implode_fields(){
		$implode_fields = ['name', 'meta.creator'];
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->concatWS($implode_fields, " - ", "name", false);
		$this->get()->shouldHaveElementKeyWithValue(0, 'name', 'first - David');
	}

	function it_can_help_get_select_options(){
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

	function it_has_factory_method(){
		$input = $this->getExampleArray();
		$this::init($input)->shouldHaveType('xiio\ArrayManipulator');
	}

	function it_convert_objects_to_array(){
		$input = $this->getExampleArray();
		$this->setArray($input);
		$this->toArray();
		$this->get()->shouldHaveKeyWithValueType(1, 'array');
		$this->get()->shouldHaveKeyWithValueType(3, 'array');
	}

	function let($object)
	{
		$this->beConstructedWith([]);
	}

	private function getExampleArray(){
		$stdClass = new \stdClass();
		$stdClass->name = 'stdClass';
		$stdClass->type = 'object';
		$stdClass->meta = [
			"creator" => 'David'
		];
		$input = [
			0 => [
				'name' => "first",
				'type' => "array",
				'meta' => [
					"creator" => 'David'
				]
			],
			1 => call_user_func_array(function ($class) {
				$stubClass = clone $class;
				$stubClass->name .= " 1";
				$stubClass->meta = [
					"creator" => 'Thomas'
				];
				return $stubClass;
			}, [$stdClass]),
			2 => [
				'name' => "second",
				'type' => "array",
				'meta' => [
					"creator" => 'Jasmine'
				]
			],
			3 => call_user_func_array(function ($class) {
				$stubClass = clone $class;
				$stubClass->name .= " 2";
				return $stubClass;
			}, [$stdClass]),
		];
		return $input;
	}

	public function getMatchers()
    {
        return [
            'haveKeyWithValueType' => function ($subject, $key, $type) {
                $exist = array_key_exists($key, $subject);
                $result = false;
                if ($exist && gettype($subject[$key]) === $type){
					$result = true;
                }
                return $result;
            },
            'haveElementKeyWithValue' => function ($subject, $element_index, $key, $value) {
                if (!isset($subject[$element_index])){
                    return false;
                }
                return DotKey::on($subject[$element_index])->get($key)===$value;
            },
            'doesNotHaveElementWithKey' => function ($subject, $element_index, $key) {
                if (!isset($subject[$element_index])){
                    return true;
                }
                return !DotKey::on($subject[$element_index])->exists($key);
            },
            'haveElementWithKey' => function ($subject, $element_index, $key) {
                if (!isset($subject[$element_index])){
                    return false;
                }
                return DotKey::on($subject[$element_index])->exists($key);
            },
        ];
    }

}
