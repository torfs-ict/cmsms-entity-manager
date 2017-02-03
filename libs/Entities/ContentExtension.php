<?php

namespace EntityManager\Entities;

use cms_admin_utils;
use CmsException;
use CmsFormUtils;
use CmsLayoutCollection;
use CmsLayoutTemplate;
use CmsLayoutTemplateType;

class ContentExtension extends BuiltIn {
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
        return 'Normale pagina';
    }

    public function IsDefaultPossible()
    {
        return true;
    }

    public function IsViewable()
    {
        return true;
    }

    public function IsSearchable()
    {
        if( !parent::IsSearchable() ) return false;
        return $this->GetPropertyValue('searchable') == 0;
    }

    public function HasPreview()
    {
        return true;
    }

    public function HasTemplate()
    {
        return true;
    }

    final public static function IsExtentedContent()
    {
        return true;
    }

    /**
     * Set up base property attributes for this content type
     *
     * This property type adds these properties: design_id, template, searchable, disable_wysiwyg, pagemetadata, pagedata
     */
    function SetProperties()
    {
        parent::SetProperties();
        $this->AddProperty('design_id',0,self::TAB_OPTIONS);
        $this->AddProperty('template',0,self::TAB_OPTIONS);
        $this->AddProperty('searchable',20,self::TAB_OPTIONS);
        $this->AddProperty('disable_wysiwyg',60,self::TAB_OPTIONS);
        $this->AddProperty('pagemetadata',1,self::TAB_LOGIC);
        $this->AddProperty('pagedata',2,self::TAB_LOGIC);
    }

    /**
     * Gets the main content
     *
     * @param string $param which attribute to return (content_en is assumed)
     * @return string the specified content
     */
    function Show($param = 'content_en')
    {
        $param = trim($param);
        if( !$param ) $param = 'content_en';
        $param = str_replace(' ','_',$param);
        return $this->GetPropertyValue($param);
    }

    /**
     * Given information about a single property this method returns that property
     *
     * @param string $one The property name
     * @param string $adding A flag indicating whether or not we are in add or edit mode
     * @return array consisting of two elements: A label, and the input element HTML and javascript.
     * @internal
     */
    protected function display_single_element($one,$adding)
    {
        static $_designs;
        static $_types;
        static $_designtree;
        static $_designlist;
        static $_templates;
        if( $_designlist == null ) {
            $_tpl = CmsLayoutTemplate::template_query(array('as_list'=>1));
            if( is_array($_tpl) && count($_tpl) > 0 ) {
                $_templates = array();
                foreach( $_tpl as $tpl_id => $tpl_name ) {
                    $_templates[] = array('value'=>$tpl_id,'label'=>$tpl_name);
                }
            }
            $_designlist = CmsLayoutCollection::get_list();
        }

        switch($one) {
            case 'design_id':
                // get the dflt/current design id.
                try {
                    $dflt_design = CmsLayoutCollection::load_default();
                    $dflt_design_id = $dflt_design->get_id();
                    $design_id = $this->GetPropertyValue('design_id');
                    if( $design_id < 1 ) $design_id = $dflt_design_id;

                    $out = '';
                    if( is_array($_designlist) && count($_designlist) ) {
                        $out = CmsFormUtils::create_dropdown('design_id',$_designlist,$this->GetPropertyValue('design_id'),
                            array('id'=>'design_id'));
                        $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','info_editcontent_design',lang('help_title_editcontent_design'));
                        return array('<label for="design_id">*'.lang('design').':</label>'.$help,$out);
                    }
                }
                catch( CmsException $e ) {
                    // nothing here yet.
                }
                break;

            case 'template':
                try {
                    $dflt_tpl = CmsLayoutTemplate::load_dflt_by_type(CmsLayoutTemplateType::CORE.'::page');
                    $template_id = $this->TemplateId();
                    if( $template_id < 1 ) $template_id = $dflt_tpl->get_id();
                    $out = CmsFormUtils::create_dropdown('template_id',$_templates,$template_id,array('id'=>'template_id'));
                    $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','info_editcontent_template',lang('help_title_editcontent_template'));
                    return array('<label for="template_id">*'.lang('template').':</label>'.$help,$out);
                }
                catch( CmsException $e ) {
                    // nothing here yet.
                }
                break;

            case 'pagemetadata':
                $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','help_content_pagemeta',lang('help_title_content_pagemeta'));
                return array('<label for="id_pagemetadata">'.lang('page_metadata').':</label>'.$help,
                    CmsFormUtils::create_textarea(array('name'=>'metadata','value'=>$this->Metadata(),
                        'classname'=>'pagesmalltextarea',
                        'width'=>80,'height'=>3,
                        'id'=>'metadata')));

            case 'pagedata':
                $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','help_content_pagedata',lang('help_title_content_pagedata'));
                return array('<label for="id_pagedata">'.lang('pagedata_codeblock').':</label>'.$help,
                    CmsFormUtils::create_textarea(array('name'=>'pagedata','value'=>$this->GetPropertyValue('pagedata'),
                        'width'=>80,'height'=>3,
                        'classname'=>'pagesmalltextarea','id'=>'id_pagedata')));

            case 'searchable':
                $searchable = $this->GetPropertyValue('searchable');
                if( $searchable == '' ) $searchable = 1;
                $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','help_page_searchable',lang('help_title_page_searchable'));
                return array('<label for="id_searchable">'.lang('searchable').':</label>'.$help,
                    '<input type="hidden" name="searchable" value="0"/>
                          <input id="id_searchable" type="checkbox" name="searchable" value="1" '.($searchable==1?'checked="checked"':'').'/>');

            case 'disable_wysiwyg':
                $disable_wysiwyg = $this->GetPropertyValue('disable_wysiwyg');
                if( $disable_wysiwyg == '' ) $disable_wysiwyg = 0;
                $help = '&nbsp;'.cms_admin_utils::get_help_tag('core','help_page_disablewysiwyg',lang('help_title_page_disablewysiwyg'));
                return array('<label for="id_disablewysiwyg">'.lang('disable_wysiwyg').':</label>'.$help,
                    '<input type="hidden" name="disable_wysiwyg" value="0" />
             <input id="id_disablewysiwyg" type="checkbox" name="disable_wysiwyg" value="1"  '.($disable_wysiwyg==1?'checked="checked"':'').'/>');
                break;

            default:
                // call the parent class
                return parent::display_single_element($one,$adding);
        }
    }

    function FillParams($params,$editing = false)
    {
        if (isset($params)) {
            $parameters = array('pagedata','searchable','disable_wysiwyg','design_id');

            //pick up the template id before we do parameters
            if (isset($params['template_id'])) {
                if ($this->mTemplateId != $params['template_id']) $this->_contentBlocks = null;
                $this->mTemplateId = $params['template_id'];
            }

            // metadata
            if (isset($params['metadata'])) $this->mMetadata = $params['metadata'];
        }
        parent::FillParams($params,$editing);
    }

}