<?php

namespace EntityManager;

/**
 * Smarty template resource.
 */
class BuiltInResource extends \CMS_Fixed_Resource_Custom {
    protected function fetch($name, &$source, &$mtime) {
        $source = null;
        $mtime = null;

        $file = cms_join_path(__DIR__, $name);
        if (!file_exists($file)) return;
        $source = @file_get_contents($file);
        $mtime = @filemtime($file);
    }
}