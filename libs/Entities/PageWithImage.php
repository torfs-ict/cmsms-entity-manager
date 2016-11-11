<?php

namespace EntityManager\Entities;

/**
 * @active false
 */
class PageWithImage extends ContentExtension {
    /**
     * @var string
     * @images 1
     * @aspectRatio 16:9
     * @label Afbeelding
     */
    public $img;
    /**
     * @var string
     * @required true
     * @textarea true
     * @wysiwyg true
     * @label Inhoud
     */
    public $content_en;

    /**
     * Returns a friendly name for this content type
     *
     * Normally the content type returns a string representing the name of the content type translated into the users current language
     *
     * @return string
     */
    public function FriendlyName() {
        return 'Pagina met afbeelding';
    }
}