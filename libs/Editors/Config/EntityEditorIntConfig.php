<?php

namespace EntityManager\Editors\Config;

use EntityManager\EntityEditorConfig;

class EntityEditorIntConfig extends EntityEditorConfig {
    /** @var bool */
    public $date = false;
    /** @var bool */
    public $link = false;
    /** @var bool */
    public $required = false;
    /** @var bool */
    public $spinner = false;

    public function cast() {
        if (!is_null($this->default)) $this->default = (int)$this->default;
        $this->date = cms_to_bool($this->date);
        $this->link = cms_to_bool($this->link);
        $this->required = cms_to_bool($this->required);
        $this->spinner = cms_to_bool($this->spinner);
    }


}