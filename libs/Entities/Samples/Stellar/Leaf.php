<?php

namespace EntityManager\Entities\Samples\Stellar;

use EntityManager\Entities\BuiltIn;

/**
 * @active false
 * @menu false
 */
class Leaf extends BuiltIn {
    /**
     * @var string
     * @images 1
     * @label Afbeelding
     */
    public $img;
    /**
     * @var string
     * @textarea true
     * @wysiwyg true
     * @label Tekst
     */
    public $text;

    /**
     * Returns a friendly name for this content type
     *
     * Normally the content type returns a string representing the name of the content type translated into the users current language
     *
     * @return string
     */
    public function FriendlyName() {
        return 'Stellar leaf';
    }


    /**
     * @inheritDoc
     */
    public function Type()
    {
        return 'stellarleaf';
    }
}