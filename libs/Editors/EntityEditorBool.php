<?php

namespace EntityManager\Editors;

use EntityManager;
use EntityManager\EntityEditor;

class EntityEditorBool extends EntityEditor  {
    public function ShowEditor($value) {
        $em = EntityManager::GetInstance();
        if (!is_null($value)) $value = cms_to_bool($value);
        return parent::ShowEditor($value);
    }
}