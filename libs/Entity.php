<?php

namespace EntityManager;

use CmsLayoutTemplate;
use ContentBase;
use ContentOperations;
use EntityManager\Editors\Config\EntityEditorStringConfig;
use EntityManager\Entities\BuiltIn;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManagerStatic;
use ReflectionClass;
use ReflectionProperty;

abstract class Entity extends \CMSModuleContentType {
    /** @var EntityConfig */
    public $config;
    /** @var EntityEditor[] */
    private $editors = [];
    private $properties = [];
    /** @var EntityEditorConfig[] */
    private $propertyConfig = [];

    function __get($name) {
        if (!array_key_exists($name, $this->properties)) return null;
        return $this->GetPropertyValue($name);
    }

    /**
     * @return static
     */
    public static function Factory($alias = null, $class = null) {
        $ops = ContentOperations::get_instance();
        $instance = $ops->CreateNewContent(is_null($class) ? get_called_class() : $class);
        $instance->SetParentId(-1);
        $instance->SetOwner(get_userid());
        $instance->SetActive(true);
        if (!is_null($alias)) $instance->SetAlias($alias);
        return $instance;
    }

    public static function GetTemplateResourcePath($entity = null) {
        $module = \EntityManager::GetInstance();
        $class = is_null($entity) ? get_called_class() : $entity;
        if (is_subclass_of($class, BuiltIn::class)) {
            $template = sprintf('BuiltInEntity:%s.tpl', str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 14)));
            return $template;
        } else {
            $template = sprintf('%s.tpl', $class);
            return $module->SmartyModuleResource($template);
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
        return true;
    }

    /**
     * An abstract method indicating whether the content type is copyable.
     *
     * @return bool default FALSE
     */
    public function IsCopyable() {
        return true;
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
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function Set($name, $value)
    {
        $this->SetPropertyValue($name, $value);
        return $this;
    }


    /**
     * Subclasses should override this to set their property types using a lot
     * of mProperties.Add statements
     *
     */
    protected function SetProperties() {
        parent::SetProperties();

        $ref = new ReflectionClass(get_called_class());
        $this->config = new EntityConfig();
        $doc = new \phpDocumentor\Reflection\DocBlock($ref);
        foreach($this->config as $key => $value) {
            if (!$doc->hasTag($key)) continue;
            /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
            $tags = $doc->getTagsByName($key);
            $tag = array_shift($tags);
            $this->config->$key = $tag->getContent();
            if (fnmatch('fn:*', $this->config->$key)) {
                $fn = substr($this->config->$key, 3);
                if (is_callable([$this, $fn])) {
                    $this->config->$key = call_user_func([$this, $fn]);
                }
            }
            switch($key) {
                case 'active':
                case 'menu':
                case 'title':
                    $this->config->$key = cms_to_bool($this->config->$key);
                    break;
            }
        }

        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $doc = new \phpDocumentor\Reflection\DocBlock($property);
            $property = $property->getName();
            if (!$doc->hasTag('var')) continue;
            $tags = $doc->getTagsByName('var');
            if (empty($tags)) continue;
            /** @var \phpDocumentor\Reflection\DocBlock\Tag\VarTag $tag */
            $tag = array_shift($tags);
            /** @var EntityEditor $class */
            $type = $tag->getType();
            $array = false;
            if (fnmatch('*[]', $type)) {
                $array = true;
                $type = substr($type, 0, -2);
            }
            $class = sprintf('\\EntityManager\\Editors\\EntityEditor%s', ucfirst($type));
            if (!is_a($class, '\\EntityManager\\EntityEditor', true)) continue;
            $config = sprintf('\\EntityManager\\Editors\\Config\\EntityEditor%sConfig', ucfirst($type));
            /** @var EntityEditorConfig $config */
            $config = new $config($property);
            foreach($config as $key => $value) {
                if (!$doc->hasTag($key)) continue;
                /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
                $tags = $doc->getTagsByName($key);
                $tag = array_shift($tags);
                $config->$key = $tag->getContent();
                if (fnmatch('fn:*', $config->$key)) {
                    $fn = substr($config->$key, 3);
                    if (is_callable([$this, $fn])) {
                        $config->$key = call_user_func([$this, $fn]);
                    }
                }
            }
            $config->cast();
            $this->AddEntityProperty($property, $class, $config, isset($config->required) ? cms_to_bool($config->required) : false);
            unset($this->$property);
        }

        if (static::IsExtentedContent()) return;
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
        if ($this->config->menu !== true) {
            $this->RemoveProperty('showinmenu', '');
            $this->RemoveProperty('menutext', '');
        }
        if ($this->config->title !== true) {
            $this->RemoveProperty('title', '');
        }
        if (!empty($this->config->alias)) {
            $this->RemoveProperty('alias', '');
        }
        $co = ContentOperations::get_instance();
        if (!is_null($this->config->parent)) {
            if (is_numeric($this->config->parent)) {
                $this->RemoveProperty('parent', $this->config->parent);
            } else {
                $parent = $co->LoadContentFromAlias($this->config->parent);
                if ($parent instanceof ContentBase) $this->RemoveProperty('parent', $parent->Id());
            }
        }

    }

    public function IsImageProperty($property) {
        if (!array_key_exists($property, $this->propertyConfig)) return false;
        $config = $this->propertyConfig[$property];
        if (!($config instanceof EntityEditorStringConfig)) return false;
        if (is_null($config->images)) return false;
        if ($config->images < 1) return false;
        return true;
    }

    /**
     * @param string $name
     * @param string $type
     * @param EntityEditorConfig $config
     * @param bool $required
     */
    protected function AddEntityProperty($name, $type, EntityEditorConfig $config, $required = false) {
        $this->editors[$name] = new $type($this, $config);
        $this->properties[$name] = $type;
        $this->propertyConfig[$name] = $config;
        parent::AddProperty($name, 101 + count($this->properties), self::TAB_MAIN, $required);
    }

    /**
     * A method to display a single input element for an object basic, or extended property.
     *
     * @param string $one The property name
     * @param bool $adding Whether or not we are in add or edit mode.
     * @return array consisting of two elements.  A label, and the input element.
     */
    protected function display_single_element($one, $adding) {
        if (!array_key_exists($one, $this->properties)) return parent::display_single_element($one, $adding);
        /** @var EntityEditor $property */
        $config = $this->propertyConfig[$one];
        if (empty($config->fieldset)) {
            $label = $config->label;
        } else {
            $label = sprintf('</p><div class="information">%s</div><p class="pagetext">%s', htmlentities($config->fieldset), htmlentities($config->label));
        }
        return [$label, $this->editors[$one]->ShowEditor($this->GetPropertyValue($one))];
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
        if (static::IsExtentedContent()) {
            parent::FillParams($params, $editing);
            return;
        }
        $co = ContentOperations::get_instance();
        if (empty($params)) {
            $params = [
                'alias' => $this->Alias(),
                'parent' => $this->ParentId(),
                'active' => $this->Active()
            ];
        }
        if (!array_key_exists('parent', $params)) {
            $params['parent'] = -1;
            $this->SetParentId($params['parent']);
        }
        // Annotations
        if ($this->Id() < 1) {
            $params['active'] = $this->config->active;
        }
        if (!is_null($this->config->parent)) {
            if (is_numeric($this->config->parent)) {
                $this->SetParentId($this->config->parent);
            } else {
                $parent = $co->LoadContentFromAlias($this->config->parent);
                if ($parent instanceof ContentBase) $this->SetParentId($parent->Id());
            }
        }
        if ($this->config->menu !== true) {
            $this->SetShowInMenu(false);
            $this->SetMenuText($this->Name());
            $params['menutext'] = $this->Name();
        }
        if (!empty($this->config->alias)) {
            $this->SetAlias($this->config->alias);
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
            $this->SetAlias($alias);
        }
        // Template
        $this->SetTemplateId(CmsLayoutTemplate::load('Entity page')->get_id());
        // Properties
        foreach($this->properties as $property => $type) {
            if (!array_key_exists($property, $params)) continue;
            $editor = $this->editors[$property];
            $this->SetPropertyValue($property, $editor->Adjust($params[$property]));
        }
        // Others
        parent::FillParams($params, $editing);
    }

    public function GetAspectRatio($property) {
        if (empty($this->propertyConfig[$property]->aspectRatio)) return null;
        $ret = explode(':', $this->propertyConfig[$property]->aspectRatio);
        return (int)$ret[0] / (int)$ret[1];
    }

    /**
     * @param string $property
     * @return EntityEditor
     */
    public function GetEditor($property) {
        return $this->editors[$property];
    }

    public function GetFile($property, $index) {
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $files = unserialize($this->GetPropertyValue($property));
        $index = min(1, (int)$index);
        if (count($files) < $config->files) $files = array_fill(0, $config->files, null);
        $ret = $files[$index - 1];
        if (empty($ret)) return null;
        $size = (int)filesize(cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, $ret));
        $humanSize = $size;
        for($i = 0; ($humanSize / 1024) > 0.9; $i++, $humanSize /= 1024) {}
        $humanSize = number_format($humanSize, 2, ',', ' ') . ' ' . ['B','kB','MB','GB','TB','PB','EB','ZB','YB'][$i];
        return [
            'filename' => $ret,
            'size' => $size,
            'sizeHuman' => $humanSize,
            'url' => cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $this->Id(), $property, $index, $ret)
        ];
    }

    public function GetImage($property, $index) {
        //$this->SetProperties();
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $images = unserialize($this->GetPropertyValue($property));
        if (!is_array($images)) $images = [];
        $index = (int)$index;
        if (count($images) < $config->images) $images = array_merge($images, array_fill(0, $config->images - count($images), ['filename' => '', 'x' => 0, 'y' => 0, 'width' => 0, 'height' => 0]));
        return $images[$index - 1];
    }

    public function IsImageSet($property, $index = 1) {
        /** @var EntityEditorStringConfig $cfg */
        $cfg = $this->propertyConfig[$property];
        $img = $this->GetImage($property, $index);
        $src = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, 'original', $img['filename']);
        if (!is_file($src)) return false;
        return true;
    }

    public function CropImageAuto($property, $index) {
        /** @var EntityEditorStringConfig $cfg */
        $cfg = $this->propertyConfig[$property];
        $img = $this->GetImage($property, $index);
        $src = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, 'original', $img['filename']);
        if (!is_file($src)) return;

        $image = imagecreatefromstring(file_get_contents($src));
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = $cfg->GetAspectRatio();
        if (empty($ratio)) {
            $this->CropImage($property, $index, 0, 0, $width, $height);
            return;
        }

        if ($width > $height) {
            $newWidth = $width;
            $newHeight = $width / $ratio;
        } else {
            $newHeight = $height;
            $newWidth = $height / $ratio;
        }
        $x = ($width - $newWidth) / 2;
        $y = ($height - $newHeight) / 2;
        $this->CropImage($property, $index, $x, $y, $newWidth, $newHeight);
    }

    public function CropImage($property, $index, $x, $y, $width, $height) {
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $images = unserialize($this->GetPropertyValue($property));
        $index = (int)$index;
        if (count($images) < $config->images) $images = array_merge($images, array_fill(0, $config->images - count($images), ['filename' => '', 'x' => 0, 'y' => 0, 'width' => 0, 'height' => 0]));
        $rect = ['x' => (float)$x, 'y' => (float)$y, 'width' => (float)$width, 'height' => (float)$height];
        $images[$index - 1] = array_merge($images[$index - 1], $rect);
        $this->SetPropertyValue($property, serialize($images));
        $this->Save();

        $src = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, 'original', $images[$index - 1]['filename']);
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, 'thumbnail', $images[$index - 1]['filename']);
        @mkdir(dirname($dest), 0755, true);
        @unlink($dest);
        #error_log(sprintf('Cropping %s, file exists: %s', $src, (file_exists($src) ? 'yes' : 'no')));
        #error_log(var_export($rect, true));
        $img = ImageManagerStatic::make($src);
        $img->crop((int)$rect['width'], (int)$rect['height'], (int)$rect['x'], (int)$rect['y']);
        if (!is_null($config->maxThumbnailWidth) && $rect['width'] > $config->maxThumbnailWidth) {
            $img->resize($config->maxThumbnailWidth, null, function(Constraint $constraint) {
                $constraint->aspectRatio();
            });
        } elseif (!is_null($config->maxThumbnailHeight) && $rect['height'] > $config->maxThumbnailHeight) {
            $img->resize(null, $config->maxThumbnailHeight, function(Constraint $constraint) {
                $constraint->aspectRatio();
            });
        }
        $img->save($dest, 100);
    }

    public function CountImages($property) {
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $images = unserialize($this->GetPropertyValue($property));
        if ($config->images < count($images)) return $config->images;
        return count($images);
    }

    /**
     * @param string $property
     * @param int $index
     * @param string $filename
     * @param string $source
     */
    public function UploadImage($property, $index, $filename, $source) {
        $this->Save();
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $images = unserialize($this->GetPropertyValue($property));
        if (!is_array($images)) $images = [];
        $index = (int)$index;
        if (count($images) < $config->images) $images = array_merge($images, array_fill(0, $config->images - count($images), ['filename' => '', 'x' => 0, 'y' => 0, 'width' => 0, 'height' => 0]));        
        $images[$index - 1] = ['filename' => $filename, 'x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];
        $this->SetPropertyValue($property, serialize($images));
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, 'original', $filename);
        @mkdir(dirname($dest), 0755, true);
        @unlink($dest);
        copy($source, $dest);
        $this->Save();
        $this->CropImageAuto($property, $index);
    }

    /**
     * @param string $property
     * @param int $index
     * @param string $filename
     * @param string $source
     */
    public function UploadFile($property, $index, $filename, $source) {
        $this->Save();
        /** @var EntityEditorStringConfig $config */
        $config = $this->propertyConfig[$property];
        $files = unserialize($this->GetPropertyValue($property));
        if (!is_array($files)) $files = [];
        $index = (int)$index;
        if (count($files) < $config->files) $files = array_merge($files, array_fill(0, $config->files - count($files), null));
        $files[$index - 1] = $filename;
        $this->SetPropertyValue($property, serialize($files));
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $this->Id(), $property, $index, $filename);
        @mkdir(dirname($dest), 0755, true);
        @unlink($dest);
        copy($source, $dest);
        $this->Save();
    }

    final public function Save() {
        $this->FillParams([], ($this->Id() > 0));
        $this->OnBeforeSave();
        parent::Save();
        ContentOperations::get_instance()->SetAllHierarchyPositions();
        $this->OnAfterSave();
    }

    /**
     * @return ContentBase[]
     */
    final public function GetChildren() {
        $ret = [];
        /** @var cms_content_tree $hierarchy */
        $hierarchy = cmsms()->GetHierarchyManager();
        $me = $hierarchy->find_by_tag('id', $this->Id());
        $children = $me->get_children();
        foreach($children as $child) {
            $ret[] = $child->getContent();
        }
        return $ret;
    }

    final public function GenerateAliasFromProperty($property) {
        $this->SetAlias(munge_string_to_url($this->GetPropertyValue($property), true));
        return $this;
    }

    final public function GenerateAliasFromString($string) {
        $this->SetAlias(munge_string_to_url($string, true));
        return $this;
    }

    public function Render() {
        $module = \EntityManager::GetInstance();
        if (!headers_sent()) $module->SmartyHeaders();
        $module->assign('entity_obj', $this);
        return $module->smarty->fetch(static::GetTemplateResourcePath());
    }

    public function OnBeforeSave() {}
    public function OnAfterSave() {}
    public static function CreateStaticRoutes() {}
    public static function IsExtentedContent() {
        return false;
    }
}