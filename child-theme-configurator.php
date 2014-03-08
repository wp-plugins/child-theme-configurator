<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: Child Theme Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Create a Child Theme from any installed Theme. Each CSS selector, rule and value can then be searched, previewed and modified.
    Version: 1.3.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/

    defined('LF') or define('LF', "\n");
    define('CHLD_THM_CFG_VERSION', '1.3.0');

    if (is_admin()):
        include_once( 'includes/class-ctc.php' );
        global $chld_thm_cfg;
        $chld_thm_cfg = new Child_Theme_Configurator( __FILE__ );
    endif;
    
    register_uninstall_hook( __FILE__ , 'child_theme_configurator_delete_plugin');
    function child_theme_configurator_delete_plugin() {
        global $chld_thm_cfg;
        delete_option($chld_thm_cfg->optionsName);
    }

    
