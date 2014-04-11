<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/* 
 * This file and all accompanying files (C) 2014 Lilaea Media LLC except where noted. See license for details.
 */
 
class ChildThemePluginExclusions {
    
    function __construct() {
        add_filter('chld_thm_cfg_backend', array(&$this, 'exclude_admin_scripts'), 10, 2);
    }
    function exclude_admin_scripts($strings = array()) {
        $strings = array_merge($strings, array(
            '/admin/',
            '/chld[\-_]thm[\-_]cfg/',
        ));
        return $strings;
    }
}