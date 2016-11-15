<?php

namespace EntityManager\Editors\Config;

use EntityManager\EntityEditorConfig;

class EntityEditorStringConfig extends EntityEditorConfig {
    /** @var bool */
    public $autocomplete = false;
    /** @var int|null */
    public $files = null;
    /** @var int|null */
    public $images = null;
    /** @var string */
    public $aspectRatio = null;
    /** @var bool */
    public $required = false;
    /** @var bool */
    public $textarea = false;
    /** @var bool */
    public $tags = false;
    /** @var bool */
    public $wysiwyg = false;
    /** @var int|null */
    public $maxThumbnailWidth = null;
    /** @var int|null */
    public $maxThumbnailHeight = null;

    public function cast() {
        if (!is_null($this->default)) $this->default = (string)$this->default;
        if (!is_null($this->files)) $this->files = (int)$this->files;
        if (!is_null($this->images)) $this->images = (int)$this->images;
        if (!is_null($this->maxThumbnailHeight)) $this->maxThumbnailHeight = (int)$this->maxThumbnailHeight;
        if (!is_null($this->maxThumbnailWidth)) $this->maxThumbnailWidth = (int)$this->maxThumbnailWidth;
        $this->autocomplete = cms_to_bool($this->autocomplete);
        $this->required = cms_to_bool($this->required);
        $this->tags = cms_to_bool($this->tags);
        $this->textarea = cms_to_bool($this->textarea);
        $this->wysiwyg = cms_to_bool($this->wysiwyg);
    }

    public function GetAspectRatio() {
        if (empty($this->aspectRatio)) return null;
        list($width, $height) = explode(':', $this->aspectRatio);
        return $width / $height;
    }


}