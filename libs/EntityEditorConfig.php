<?php

namespace EntityManager;

abstract class EntityEditorConfig {
    public $default = null;
    public $fieldset = null;
    public $name;
    public $label = 'Unnamed property';
    public $var = 'string';
    final public function __construct($propertyName) {
        $this->name = $propertyName;
    }
    abstract public function cast();
}