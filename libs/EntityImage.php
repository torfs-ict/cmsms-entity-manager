<?php

namespace EntityManager;

abstract class EntityImage extends CMSModuleContentType {
    /** @var EntityImageConfig */
    private $config;
    /** @var string */
    public $filename;
    /** @var int */
    public $cropX;
    /** @var int */
    public $cropY;
    /** @var int */
    public $cropWidth;
    /** @var int */
    public $cropHeight;
    /** @var float */
    public $aspectRatio;

    function __get($name) {
        if (!in_array($name, ['aspectRatio', 'filename', 'cropX', 'cropY', 'cropWidth', 'cropHeight'])) return null;
        switch ($name) {
            case 'aspectRatio':
                $ret = explode(':', $this->config->aspectRatio);
                return (int)$ret[0] / (int)$ret[1];
            default:
                return $this->GetPropertyValue($name);
        }
    }

    /**
     * A method for returning the module that the content type belongs to.
     *
     * @return string
     */
    public function ModuleName() {
        return 'EntityManager';
    }

    /**
     * An abstract method that extended content types can use to indicate whether or not they want children.
     * Some content types, such as a separator do not want to have any children.
     *
     * @since 0.11
     * @return bool Default TRUE
     */
    public function WantsChildren() {
        return false;
    }

    /**
     * Indicates whether ths page type uses a template.
     * i.e: some content types like sectionheader and separator do not.
     *
     * @since 2.0
     * @return bool default FALSE
     */
    public function HasTemplate() {
        return false;
    }

    /**
     * An abstract method indicating whether the content type is copyable.
     *
     * @return bool default FALSE
     */
    public function IsCopyable() {
        return false;
    }

    /**
     * Indicates whether this content type is viewable (i.e: can be rendered).
     * some content types (like redirection links) are not viewable.
     *
     * @return bool Default is True
     */
    public function IsViewable() {
        return false;
    }

    /**
     * Subclasses should override this to set their property types using a lot
     * of mProperties.Add statements
     *
     */
    protected function SetProperties() {
        parent::SetProperties();

        $ref = new ReflectionClass(get_called_class());
        $this->config = new EntityImageConfig();
        $doc = new \phpDocumentor\Reflection\DocBlock($ref);
        foreach($this->config as $key => $value) {
            if (!$doc->hasTag($key)) continue;
            /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
            $tags = $doc->getTagsByName($key);
            $tag = array_shift($tags);
            $this->config->$key = $tag->getContent();
        }

        $this->RemoveProperty('template','-1');
        $this->RemoveProperty('target','');
        $this->RemoveProperty('accesskey','');
        $this->RemoveProperty('titleattribute','');
        $this->RemoveProperty('cachable',true);
        $this->RemoveProperty('tabindex','');
        $this->RemoveProperty('thumbnail','');
        $this->RemoveProperty('image', '');
        $this->RemoveProperty('extra1', '');
        $this->RemoveProperty('extra2', '');
        $this->RemoveProperty('extra3', '');
        $this->RemoveProperty('showinmenu', '');
        $this->RemoveProperty('menutext', '');
        $this->RemoveProperty('alias', '');
        $this->RemoveProperty('title', '');

        $this->AddProperty('filename', -1);

        unset($this->aspectRatio);
        unset($this->filename);
        unset($this->cropX);
        unset($this->cropY);
        unset($this->cropWidth);
        unset($this->cropHeight);

        $co = ContentOperations::get_instance();
        if (!is_null($this->config->parent)) {
            $parent = $co->LoadContentFromAlias($this->config->parent);
            if ($parent instanceof ContentBase) $this->RemoveProperty('parent', $parent->Id());
        }
    }

    /**
     * A method to display a single input element for an object basic, or extended property.
     *
     * @param string $one The property name
     * @param bool $adding Whether or not we are in add or edit mode.
     * @return array consisting of two elements.  A label, and the input element.
     */
    protected function display_single_element($one, $adding) {
        switch ($one) {
            case 'filename':
                $em = EntityManager::GetInstance();
                $em->smarty->assign('entity', $this);
                $em->smarty->assign('modUrl', $em->GetModuleURLPath());
                return ['Afbeelding beheren', $em->smarty->fetch($em->GetTemplateResource('cropper.tpl'))];
            default:
                return parent::display_single_element($one, $adding);
        }
    }

    /**
     * Function for the subclass to parse out data for it's parameters.
     * This method is typically called from an editor form to allow modifying the content object from
     * form input fields (usually $_POST)
     *
     * @param array $params The input array (usually from $_POST)
     * @param bool $editing Indicates wether this is an edit or add operation.
     */
    public function FillParams($params, $editing = false) {
        $co = ContentOperations::get_instance();
        // Annotations
        if (!is_null($this->config->parent)) {
            $parent = $co->LoadContentFromAlias($this->config->parent);
            if ($parent instanceof ContentBase) $this->SetParentId($parent->Id());
        }
        $this->SetShowInMenu(false);
        $params['title'] = $params['filename'];
        $params['menutext'] = $params['title'];
        if (!empty($this->config->alias)) {
            $params['alias'] = $this->config->alias;
        }
        // Autogenerate alias
        if (empty($params['alias']) && !empty($params['title'])) {
            $alias = munge_string_to_url($params['title'], true);
            $contentops = ContentOperations::get_instance();
            $error = $contentops->CheckAliasError($alias, $this->Id());
            if ($error !== FALSE) {
                if (FALSE == empty($alias)) {
                    $alias_num_add = 2;
                    while ($contentops->CheckAliasError($alias.'-'.$alias_num_add) !== FALSE) {
                        if( $alias_num_add > 100 ) {
                            $tmp = $contentops->CheckAliasError($alias.'-'.$alias_num_add);
                        }
                        $alias_num_add++;
                    }
                    $alias .= '-'.$alias_num_add;
                }
                else {
                    $alias = '';
                }
            }
            $accents = '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/';
            $alias = htmlentities($alias, ENT_NOQUOTES, 'UTF-8');
            $alias = preg_replace($accents, '$1', $alias);
            $params['alias'] = $alias;
        }
        // Template
        $this->SetTemplateId(CmsLayoutTemplate::load('Entity page')->get_id());
        // Others
        parent::FillParams($params, $editing);
        // Upload image
        if (!empty($params['blob'])) {
            if ($this->Id() < 1) $this->Save();
            $this->UploadImage($params['filename'], $params['blob']);
        }
        // Crop image
        if (!empty($params['filename'])) {
            $this->CropImage($params);
        }
        #var_dump($params, $this->Id()); exit;
    }

    public function CropImage($rect) {
        $src = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), 'original', $this->GetPropertyValue('filename'));
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), 'thumbnail', $this->GetPropertyValue('filename'));
        @mkdir(dirname($dest), 0755, true);
        $im = imagecreatefromstring(file_get_contents($src));
        $cropped = imagecrop($im, $rect);
        imagejpeg($cropped, $dest, 100);
        $this->SetPropertyValue('cropX', $rect['x']);
        $this->SetPropertyValue('cropY', $rect['y']);
        $this->SetPropertyValue('cropWidth', $rect['width']);
        $this->SetPropertyValue('cropHeight', $rect['height']);
    }

    /**
     * @param string $filename
     * @param string $source
     */
    public function UploadImage($filename, $source) {
        // TODO: remove old files
        $this->SetPropertyValue('filename', $filename);
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), 'original', $filename);
        @mkdir(dirname($dest), 0755, true);
        copy($source, $dest);
        $this->Save();
        return $this;
    }

    public function GetImageUrl() {
        return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $this->Id(), 'thumbnail', $this->filename);
    }

    public function GetThumbnailUrl() {
        return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $this->Id(), 'thumbnail', $this->filename);
    }

    final public function Save() {
        parent::Save();
        ContentOperations::get_instance()->SetAllHierarchyPositions();
    }
}