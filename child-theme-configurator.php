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
    defined( 'CHLD_THM_CFG_MENU_NAME' ) or define( 'CHLD_THM_CFG_MENU_NAME', 'chld_thm_cfg' );

    class ChildThemeConfigurator {
        static function init() {
            // setup admin hooks
            $lang_dir = dirname(__FILE__) . '/lang';
            load_plugin_textdomain('chld_thm_cfg', FALSE, $lang_dir, $lang_dir);
            add_action( 'admin_menu',            'ChildThemeConfigurator::admin' );
            add_action( 'wp_ajax_ctc_update',    'ChildThemeConfigurator::save' );
            add_action( 'wp_ajax_ctc_query',     'ChildThemeConfigurator::query' );
        }
        static function ctc() {
            // create admin object
            global $chld_thm_cfg;
            if ( !isset( $chld_thm_cfg ) ):
                include_once( dirname(__FILE__) . '/includes/class-ctc.php' );
                $chld_thm_cfg = new ChildThemeConfiguratorAdmin( __FILE__ );
            endif;
            return $chld_thm_cfg;
        }
        static function save() {
            // ajax write
            self::ctc()->ajax_save_postdata();
        }
        static function query() {
            // ajax read
            self::ctc()->ajax_query_css();
        }        
        static function admin() {
            // setup admin page
            $hook = add_management_page(
                __( 'Child Theme Configurator', 'chld_thm_cfg' ), 
                __( 'Child Themes', 'chld_thm_cfg' ), 
                'edit_theme_options', 
                'chld_thm_cfg', 
                'ChildThemeConfigurator::render' 
            );
            add_action('load-' . $hook, 'ChildThemeConfigurator::page_init');        
        }
        static function page_init() {
            // start admin controller
            self::ctc()->ctc_page_init();
        }
        static function render() {
            // display admin page
            self::ctc()->render();
        }
    }
    
    if ( is_admin() ) ChildThemeConfigurator::init();
    
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
    
