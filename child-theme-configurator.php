<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: Child Theme Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Create a Child Theme from any installed Theme. Each CSS selector, rule and value can then be searched, previewed and modified.
    Version: 1.6.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/

    defined( 'LF' ) or define( 'LF', "\n");
    defined( 'CHLD_THM_CFG_OPTIONS' ) or define( 'CHLD_THM_CFG_OPTIONS', 'chld_thm_cfg_options' );
    defined( 'CHLD_THM_CFG_VERSION' ) or define( 'CHLD_THM_CFG_VERSION', '1.6.0' );
    defined( 'CHLD_THM_CFG_MAX_SELECTORS' ) or define( 'CHLD_THM_CFG_MAX_SELECTORS', '50000' );
    defined( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS' ) or define( 'CHLD_THM_CFG_MAX_RECURSE_LOOPS', '1000' );

    if (is_admin()):
        include_once( 'includes/class-ctc.php' );
        global $chld_thm_cfg;
        $chld_thm_cfg = new Child_Theme_Configurator( __FILE__ );
    endif;
    
    register_uninstall_hook( __FILE__ , 'child_theme_configurator_delete_plugin' );
    function child_theme_configurator_delete_plugin() {
        delete_option( CHLD_THM_CFG_OPTIONS );
        delete_option( CHLD_THM_CFG_OPTIONS . '_configvars' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_qs' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_sel' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_query' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_rule' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_val' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_dict_seq' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_sel_ndx' );
        delete_option( CHLD_THM_CFG_OPTIONS . '_val_ndx' );
    }
    
