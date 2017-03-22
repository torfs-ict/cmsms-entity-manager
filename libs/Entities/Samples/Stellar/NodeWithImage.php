<?php

namespace EntityManager\Entities\Samples\Stellar;

/**
 * @active false
 * @menu true
 */
class NodeWithImage extends Node {
    /**
     * @var string
     * @images 1
     * @label Afbeelding
     */
    public $img;

    /**
     * @inheritDoc
     */
    public function FriendlyName() {
        return 'Stellar parent with image';
    }

    /**
     * @inheritDoc
     */
    public function Type()
    {
        return 'stellarnodewithimage';
    }
}