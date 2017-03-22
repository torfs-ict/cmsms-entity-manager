<?php

namespace EntityManager\Entities\Stellar;

use EntityManager\Entities\BuiltIn;

abstract class Node extends BuiltIn {
    /**
     * @var string
     * @images fn:StellarCount
     * @label Achtergrondafbeeldingen
     */
    public $stellar;

    public function StellarCount() {
        if ($this->Id() < 1) return 0;
        $children = $this->ChildCount();
        return $children == 0 ? 0 : floor($children / 2) + 1;
    }

    protected function display_single_element($one, $adding)
    {
        if ($one == 'stellar') {
            if ($this->StellarCount() == 0) return '';
            $this->SetProperties();
        }
        return parent::display_single_element($one, $adding);
    }
}

