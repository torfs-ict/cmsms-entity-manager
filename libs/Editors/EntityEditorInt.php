<?php

namespace EntityManager\Editors;

use ContentOperations;
use EntityManager\Editors\Config\EntityEditorIntConfig;
use EntityManager\EntityEditor;

class EntityEditorInt extends EntityEditor {
    /** @var EntityEditorIntConfig */
    protected $config;
    public function ShowEditor($value) {
        if ($this->config->link === true) {
            $contentops = ContentOperations::get_instance();
            if (empty($value)) $value = $this->config->default;
            return $contentops->CreateHierarchyDropdown($this->entity->Id(), $value, $this->config->name, false, true, false, false, true);
        } else {
            return parent::ShowEditor($value);
        }
    }

    public function Adjust($value) {
        if ($this->config->date && !empty($value)) return (int)$value / 1000;
        return parent::Adjust($value);
    }

}