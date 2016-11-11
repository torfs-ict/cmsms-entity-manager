<?php

namespace EntityManager\Editors\Config;

use EntityManager\EntityEditorConfig;

class EntityEditorBoolConfig extends EntityEditorConfig {
    public function cast() {
        if (!is_null($this->default)) $this->default = cms_to_bool($this->default);
    }


}