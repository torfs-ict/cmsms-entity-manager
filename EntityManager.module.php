<?php

use NetDesign\NetDesignModule;
use EntityManager\Entity;
use EntityManager\EntityImage;
use Webmozart\Glob\Glob;

class EntityManager extends NetDesignModule {
    private $entities = null;

    /**
     * Override of the default DoAction method. All we do here is make sure that the
     * Smarty error console is shown upon an uncaught exception in the admin interface.
     *
     * @param string $name
     * @param string $id
     * @param string $params
     * @param string $returnid
     * @return string
     */
    public function DoAction($name, $id, $params, $returnid = '')
    {
        if (in_array($name, $this->GetEntities())) {
            /** @var Entity $obj */
            $obj = ContentOperations::get_instance()->CreateNewContent($name);
            if (!is_null($obj->config->admin)) {
                $params = array_merge($params, ['type' => $name]);
                return parent::DoAction('admin', $id, $params, $returnid);
            }
        }
        return parent::DoAction($name, $id, $params, $returnid);
    }

    /**
     * @return string
     */
    public function GetFriendlyName() {
        return 'Entity Manager';
    }

    /**
     * @return bool
     */
    public function HasAdmin() {
        return false;
    }


    /**
     * Since we set our constructor as final we define this method in addition to InitializeAdmin() and
     * InitializeFrontend().
     */
    public function Initialize() {
        $this->GetEntities();
    }

    /**
     * Called from within the constructor, ONLY for admin module
     * actions.  This method should be overridden to create routes, and
     * set handled parameters, and perform other initialization tasks
     * that need to be setup for all frontend actions.
     *
     * @see CreateParameter
     */
    protected function InitializeAdmin() {
        parent::InitializeAdmin();
        $this->smarty->registerPlugin('function', 'EntityAdmin', 'EntityManager::SmartyAdminPlugin');
        $this->smarty->registerPlugin('function', 'EntityAjaxUrl', 'EntityManager::SmartyAjaxUrlPlugin');
        $this->smarty->registerPlugin('function', 'EntityDefaultImage', 'EntityManager::SmartyDefaultImagePlugin');
        $this->smarty->registerPlugin('function', 'EntityImage', 'EntityManager::SmartyImagePlugin');
        $this->smarty->registerPlugin('function', 'EntityPropertyFile', 'EntityManager::SmartyPropertyFilePlugin');
        $this->smarty->registerPlugin('function', 'EntityPropertyImage', 'EntityManager::SmartyPropertyImagePlugin');
        $this->smarty->registerPlugin('function', 'EntityPropertyImageUrl', 'EntityManager::SmartyPropertyImageUrlPlugin');
        $this->smarty->registerPlugin('function', 'EntityPropertyThumbnail', 'EntityManager::SmartyPropertyThumbnailPlugin');
        $this->smarty->registerPlugin('function', 'EntityThumbnail', 'EntityManager::SmartyThumbnailPlugin');
    }


    /**
     * Function that will get called as module is installed. This function should
     * do any initialization functions including creating database tables. It
     * should return a string message if there is a failure. Returning nothing (FALSE)
     * will allow the install procedure to proceed.
     *
     * The default behavior of this method is to include a file named method.install.php
     * in the module directory, if one can be found.  This provides a way of splitting
     * secondary functions into other files.
     *
     * @return string|false A value of FALSE indicates no error.  Any other value will be used as an error message.
     */
    public function Install() {
        try {
            $tplType = CmsLayoutTemplateType::load('EntityManager::Entity');
        } catch (CmsDataNotFoundException $e) {
            $tplType = new CmsLayoutTemplateType();
            $tplType->set_originator($this->GetName());
            $tplType->set_name('Entity');
            $tplType->set_dflt_flag(TRUE);
            $tplType->save();
        }
        try {
            $tpl = CmsLayoutTemplate::load('Entity page');
            $tpl->delete();
        } catch (CmsDataNotFoundException $e) {
            $tpl = new CmsLayoutTemplate();
            $tpl->set_type($tplType);
            $tpl->set_name('Entity page');
            $tpl->set_content('{EntityTemplate}');
            $tpl->save();
        }
        $this->RegisterSmartyPlugin('EntityAdmin', 'function', 'SmartyAdminPlugin');
        $this->RegisterSmartyPlugin('EntityAjaxUrl', 'function', 'SmartyAjaxUrlPlugin');
        $this->RegisterSmartyPlugin('EntityDefaultImage', 'function', 'SmartyDefaultImage');
        $this->RegisterSmartyPlugin('EntityImage', 'function', 'SmartyImagePlugin');
        $this->RegisterSmartyPlugin('EntityPropertyFile', 'function', 'SmartyPropertyFilePlugin');
        $this->RegisterSmartyPlugin('EntityPropertyImage', 'function', 'SmartyPropertyImagePlugin');
        $this->RegisterSmartyPlugin('EntityPropertyImageUrl', 'function', 'SmartyPropertyImageUrlPlugin');
        $this->RegisterSmartyPlugin('EntityPropertyThumbnail', 'function', 'SmartyPropertyThumbnailPlugin');
        $this->RegisterSmartyPlugin('EntityTemplate', 'function', 'SmartyTemplatePlugin');
        $this->RegisterSmartyPlugin('EntityThumbnail', 'function', 'SmartyThumbnailPlugin');
        // Copy template override
        $src = cms_join_path($this->GetModulePath(), 'templates', 'admin_editcontent.tpl');
        $dest = cms_join_path(cmsms()->GetConfig()->offsetGet('root_path'), 'module_custom', 'CMSContentManager', 'templates', 'admin_editcontent.tpl');
        @mkdir(dirname($dest), 0755, true);
        copy($src, $dest);
        return false;
    }

    /**
     * Function that will get called as module is uninstalled. This function should
     * remove any database tables that it uses and perform any other cleanup duties.
     * It should return a string message if there is a failure. Returning nothing
     * (FALSE) will allow the uninstall procedure to proceed.
     *
     * The default behaviour of this function is to include a file called method.uninstall.php
     * in your module directory to do uninstall operations.
     *
     * @return string|false A result of FALSE indicates that the module uninstalled correctly, any other value indicates an error message.
     */
    public function Uninstall() {
        unlink(cms_join_path(cmsms()->GetConfig()->offsetGet('root_path'), 'module_custom', 'CMSContentManager', 'templates', 'admin_editcontent.tpl'));
        $this->RemoveSmartyPlugin();
        /*
        try {
            $tplType = CmsLayoutTemplateType::load('EntityManager::Entity');
            $tplType->delete();
        } catch (CmsDataNotFoundException $e) {
        }
        try {
            $tpl = CmsLayoutTemplate::load('Entity page');
            $tpl->delete();
        } catch (CmsDataNotFoundException $e) {
        }
        */
        return false;
    }

    /**
     * Returns true if the modules thinks it has the capability specified
     *
     * @param string $capability an id specifying which capability to check for, could be "wysiwyg" etc.
     * @param array $params An associative array further params to get more detailed info about the capabilities. Should be syncronized with other modules of same type
     * @return bool
     */
    public function HasCapability($capability, $params = array()) {
        switch($capability) {
            case CmsCoreCapabilities::CONTENT_TYPES: return true;
        }
        return false;
    }

    public function GetClientConfig() {
        $fn = cms_join_path($this->ClientModulePath(), 'config.json');
        if (!file_exists($fn)) return [];
        $ret = json_decode(file_get_contents($fn), true);
        if (!is_array($ret)) return [];
        return $ret;
    }

    public function GetEntities() {
        if (!ModuleOperations::get_instance()->IsModuleActive($this->GetName())) return [];
        if (is_null($this->entities)) {
            $this->entities = array();
            $base = cms_join_path($this->GetModulePath(), 'libs', 'Entities');
            // Built-in entities
            $glob = Glob::glob(cms_join_path($this->GetModulePath(), 'libs', 'Entities', '**', '*.php'));
            foreach($glob as $filename) {
                $entity = sprintf('\\EntityManager\\Entities\\%s', str_replace('/', '\\', substr($filename, strlen($base) + 1)));
                $entity = substr($entity, 0, strlen($entity) - 4);
                $ref = new ReflectionClass($entity);
                if (
                    (
                        !is_a($entity, '\\EntityManager\\Entity', true)
                        && !is_a($entity, '\\EntityManager\\EntityImage', true)
                    ) || (
                        $ref->isAbstract()
                    )
                ) continue;
                /** @var Entity $ref */
                $ref = new $entity();
                $pc = new CmsContentTypePlaceholder();
                $pc->class = $entity;
                $pc->filename = $filename;
                $pc->friendlyname = $ref->FriendlyName();
                $pc->loaded = false;
                $pc->type = strtolower($entity);
                ContentOperations::get_instance()->register_content_type($pc);
                $this->entities[] = $entity;
            }
            // Client entities
            $glob = glob(cms_join_path($this->ClientModulePath(), '*.php'));
            foreach($glob as $filename) {
                $entity = pathinfo($filename, PATHINFO_FILENAME);
                require_once($filename);
                if (
                    !is_a($entity, '\\EntityManager\\Entity', true)
                    && !is_a($entity, '\\EntityManager\\EntityImage', true)
                ) continue;
                /** @var Entity $ref */
                $ref = new $entity();
                $pc = new CmsContentTypePlaceholder();
                $pc->class = $entity;
                $pc->filename = $filename;
                $pc->friendlyname = $ref->FriendlyName();
                $pc->loaded = false;
                $pc->type = strtolower($entity);
                ContentOperations::get_instance()->register_content_type($pc);
                $this->entities[] = $entity;
            }
            sort($this->entities);
        }
        return $this->entities;
    }

    public static function SmartyAdminPlugin($params, Smarty_Internal_Template $template) {
        $em = EntityManager::GetInstance();
        $cm = ModuleOperations::get_instance()->get_module_instance('CMSContentManager');
        $original = cms_join_path($cm->GetModulePath(), 'templates', 'admin_editcontent.tpl');
        $em->smarty->assign('assetUrl', cms_join_path($em->GetModuleURLPath(), 'assets'));
        $em->smarty->assign('modUrl', cms_join_path($em->GetModuleURLPath()));
        $em->smarty->assign('clientConfig', $em->GetClientConfig());
        $ret = $em->smarty->fetch($em->GetTemplateResource('admin.tpl'));
        $ret .= $cm->smarty->fetch(sprintf('file:%s', $original));
        return $ret;
    }

    public static function SmartyDefaultImagePlugin($params, Smarty_Internal_Template $template) {
        return cms_join_path(EntityManager::GetInstance()->GetModuleURLPath(), 'assets', 'default.png');
    }

    public static function SmartyImagePlugin($params, Smarty_Internal_Template $template) {
        $default = self::SmartyDefaultImagePlugin($params, $template);
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        if ($entity instanceof EntityImage) {
            $fn = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $entity->Id(), 'original', $entity->filename);
            if (!is_file($fn)) return $default;
            return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), 'original', $entity->filename);
        } else {
            return $default;
        }
    }

    public static function SmartyThumbnailPlugin($params, Smarty_Internal_Template $template) {
        $default = self::SmartyDefaultImagePlugin($params, $template);
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        if ($entity instanceof EntityImage) {
            $fn = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $entity->Id(), 'thumbnail', $entity->filename);
            if (!is_file($fn)) return $default;
            return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), 'thumbnail', $entity->filename);
        } else {
            return $default;
        }
    }

    public static function SmartyPropertyFilePlugin($params, Smarty_Internal_Template $template) {
        /** @var Entity $entity */
        if (empty($params['index'])) $params['index'] = 1;
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        $template->assign('file', $entity->GetFile($params['property'], $params['index']));
    }

    public static function SmartyPropertyImagePlugin($params, Smarty_Internal_Template $template) {
        $ret = [
            'filename' => '',
            'x' => 0,
            'y' => 0,
            'width' => 0,
            'height' => 0,
            'url' => self::SmartyDefaultImagePlugin($params, $template),
            'thumbnail_url' => self::SmartyDefaultImagePlugin($params, $template)
        ];
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        if ($entity instanceof Entity) {
            $ret = $entity->GetImage($params['property'], (int)$params['index']);
            $fn = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'original', $ret['filename']);
            if (is_file($fn)) {
                $ret['url'] = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'original', $ret['filename']);
                $ret['thumbnail_url'] = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'thumbnail', $ret['filename']);
            } else {
                $ret['url'] = self::SmartyDefaultImagePlugin($params, $template);
                $ret['thumbnail_url'] = self::SmartyDefaultImagePlugin($params, $template);
            }
        }
        $template->assign('image', $ret);
    }

    public static function SmartyPropertyImageUrlPlugin($params, Smarty_Internal_Template $template) {
        $default = self::SmartyDefaultImagePlugin($params, $template);
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        if (empty($params['index'])) $params['index'] = 1;
        if ($entity instanceof Entity) {
            $ret = $entity->GetImage($params['property'], (int)$params['index']);
            $fn = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'original', $ret['filename']);
            if (is_file($fn)) return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'original', $ret['filename']);
            else return $default;
        }
        return $default;
    }

    public static function SmartyPropertyThumbnailPlugin($params, Smarty_Internal_Template $template) {
        $default = self::SmartyDefaultImagePlugin($params, $template);
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        if (empty($params['index'])) $params['index'] = 1;
        if ($entity instanceof Entity) {
            $ret = $entity->GetImage($params['property'], (int)$params['index']);
            $fn = cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_path'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'thumbnail', $ret['filename']);
            if (is_file($fn)) return cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'thumbnail', $ret['filename']);
            else return $default;
        }
        return $default;
    }

    public static function SmartyAjaxUrlPlugin($params, Smarty_Internal_Template $template) {
        $em = EntityManager::GetInstance();
        $params = array_merge($params, [
            'action' => 'editor',
            'entity' => $template->getTemplateVars('entity')->Id(),
            'method' => $params['method'],
            'module' => 'EntityManager',
            'property' => $template->getTemplateVars('config')->name,
            'suppress' => 1,
            'type' => $template->getTemplateVars('entity')->Type()
        ]);
        $em->smarty->loadPlugin('smarty_function_cms_action_url');
        return smarty_cms_function_cms_action_url($params, $template);
    }

    public static function SmartyTemplatePlugin($params, Smarty_Internal_Template $template) {
        /** @var Entity $entity */
        $entity = cms_utils::get_current_content();
        return $entity->Render();
    }

    public function CreateStaticRoutes() {
        foreach($this->entities as $entity) {
            call_user_func([$entity, 'CreateStaticRoutes']);
        }
    }
}