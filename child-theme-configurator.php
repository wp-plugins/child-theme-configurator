<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: Child Theme Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Create Child Theme from any Theme or Stylesheet
    Version: 1.0.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2013 Lilaea Media
*/

    defined('CHLD_THM_CFG_NS') or define('CHLD_THM_CFG_NS', 'chld_thm_cfg');
    defined('LF') or define('LF', "\n");

    require_once( 'includes/class-ctc.php' );
    global $chld_thm_cfg;
    $chld_thm_cfg = new Child_Theme_Configurator( __FILE__ );
    
    register_uninstall_hook( __FILE__ , 'child_theme_configurator_delete_plugin');
    function child_theme_configurator_delete_plugin() {
        global $chld_thm_cfg;
        delete_option($chld_thm_cfg->optionsName);
    }

    
