<?php

namespace EntityManager\Entities\Samples\Stellar;

use EntityManager\Entities\Stellar\Node as AbstractNode;

/**
 * @active true
 * @menu true
 */
class Node extends AbstractNode {
    /**
     * @var string
     * @textarea true
     * @wysiwyg true
     * @label Tekst
     */
    public $text;

    /**
     * @inheritDoc
     */
    public function FriendlyName() {
        return 'Stellar parent without image';
    }

    /**
     * @inheritDoc
     */
    public function Type()
    {
        return 'stellarnodewithoutimage';
    }

    public function IsViewable() {
        return true;
    }
}