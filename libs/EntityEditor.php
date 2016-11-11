<?php

namespace EntityManager;

abstract class EntityEditor {
    protected $config;
    protected $entity;
    final public function __construct(Entity $entity, EntityEditorConfig $config) {
        $this->config = $config;
        $this->entity = $entity;
    }
    final protected function HtmlEncode($value) {
        return cms_htmlentities($value, ENT_NOQUOTES, \CmsNlsOperations::get_encoding(''));
    }
    public function Adjust($value) {
        return $value;
    }

    public function ShowEditor($value) {
        $em = \EntityManager::GetInstance();
        $em->smarty->assign(['entity' => $this->entity, 'config' => $this->config, 'editor' => $this, 'value' => $value]);
        return $em->smarty->fetch($em->GetTemplateResource(sprintf('editors/%s.tpl', $this->config->var)));
    }
}