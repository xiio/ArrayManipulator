<?php

namespace spec\xiio;

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
		$this->group_by('type');
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
		$this->group_by('meta.creator');
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
		$this->group_by('name');
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
		$this->excludeFields($excluded_fields);
		$result = $this->get()->shouldHaveCount(4);
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
            }
        ];
    }

}
