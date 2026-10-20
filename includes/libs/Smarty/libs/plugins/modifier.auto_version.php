<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifier
 */

function smarty_modifier_auto_version($file) {
    // Falls der Pfad mit einem / beginnt, ggf. trimmen
    $realPath = ltrim($file, './');

    if (file_exists($realPath)) {
        return $file . '?v=' . filemtime($realPath);
    }
    
    return $file;
}
?>
