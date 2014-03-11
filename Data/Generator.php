<?php

namespace Malwarebytes\GeneratorBundle\Data;

use Faker\Generator as Faker;
use Malwarebytes\GeneratorBundle\Exception\InvalidArgumentException;
use Malwarebytes\GeneratorBundle\Exception\InvalidConfigurationException;

class Generator
{
    protected $faker;
    protected $reader;
    protected $scenarios = array();

    public function __construct(Faker $faker, $reader, $factory, $config)
    {
        $this->faker = $faker;
        $this->reader = $reader;
        foreach($config as $name => $scenario) {
            $this->scenarios[$name] = $factory->getNewScenario($scenario);
        }
    }

    public function addScenario($name, Scenario $scenario)
    {
        $this->scenarios[$name] = $scenario;
    }

    public function runScenario($name)
    {
        if(!isset($this->scenarios[$name])) {
            throw new InvalidArgumentException("No scenario defined with the name '$name'");
        }

        $s = $this->scenarios[$name];

        $items = array();
        foreach($s->getItems() as $item) {
            if(!class_exists($item->getEntity())) {
                throw new InvalidConfigurationException("The entity " . $item->getEntity() . " does not exist.");
            }

            for($i = 0; $i < $item->getQuantity(); $i++) {
                $items[] = $this->doGenerate($item);
            }
        }

        return $items;
    }

    protected function doGenerate($item)
    {
        $class = $item->getEntity();
        $obj = new $class();

        $fields = $this->reader->readClass($class);
        foreach($fields as $name => $field) {
            $method = "set$name";
            $formatter = $field->getFormatter();
            $obj->$method($this->faker->$formatter);
        }

        return $obj;
    }
}