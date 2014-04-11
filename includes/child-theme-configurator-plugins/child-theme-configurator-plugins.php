<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: Child Theme Configurator Plugins Extension
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator-plugins/
    Description: Extends Child Theme Configurator for use with Plugin stylesheets and other custom styles
    Version: 1.0.3
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg_plugins
    Domain Path: /lang
  
    This file and all accompanying files (C) 2014 Lilaea Media LLC except where noted. See license for details.

*/

    defined('LF') or define('LF', "\n");
    define('CHLD_THM_CFG_PLUGINS_VERSION', '1.0.3');
    define('CHLD_THM_CFG_MIN_VERSION', '1.3.0');

    class ChildThemePluginConfigurator {
        
        var $dir;
        var $optionName;
        var $defs;
        
        function __construct() {
            add_action( 'plugins_loaded', array(&$this,'init') );
        }

        function init() {
            $this->dir  = dirname(__FILE__);
            $this->optionName = 'chld_thm_cfg_plugins_options';
            $lang_dir   = $this->dir . '/lang';
            load_plugin_textdomain('chld_thm_cfg_plugins', false, $lang_dir, $lang_dir);
            if (!defined('CHLD_THM_CFG_VERSION') || CHLD_THM_CFG_VERSION < CHLD_THM_CFG_MIN_VERSION):
                add_action('admin_notices', array(&$this, 'install_warning'));
                add_action('network_admin_notices',array(&$this, 'install_warning'));
                return false; 
            endif;
            add_action('chld_thm_cfg_plugins_admin', array( &$this, 'cfg_plugin_defs'));
            if (is_admin()):
                do_action('chld_thm_cfg_plugins_admin');
            else:
                // unfortunately, we have to force this to the very end of the queue
                add_action('wp_print_styles',        array(&$this, 'cfg_enqueue_styles'), 999);
            endif;
        }
        
        function cfg_enqueue_styles() {
            $options = get_option($this->optionName);
            $theme   = get_stylesheet();
            if (isset($options['enqueues'][$theme])):
                foreach($options['enqueues'][$theme] as $slug => $target):
                    wp_enqueue_style($slug . '-ctc', get_stylesheet_directory_uri() . '/' . $target);
                endforeach;
            endif;
        }
        
        function cfg_plugin_defs() {
            include_once('includes/child-theme-plugin-defs.php');
            $this->defs = new ChildThemePluginDefs($this->dir);
        }

        function install_warning() {
?>
<div class="error">
  <p>
<?php _e('The Child Theme Configurator Plugin Extension requires Child Theme Configurator ' . CHLD_THM_CFG_MIN_VERSION . ' or later. You can search and install it from Plugins > Add New or download it <a href="http://wordpress.org/plugins/child-theme-configurator/" target="_blank">here</a>.','chld_thm_cfg_plugins'); ?>
  </p>
</div>
<?php
        }
                
    }
    
    function chld_thm_cfg_plugins_delete_plugin() {
        global $chld_thm_cfg_plugins;
        delete_option($chld_thm_cfg_plugins->optionName);
        foreach ($chld_thm_cfg_plugins->objects as $obj):
            if (isset($obj->optionName))
                delete_option($obj->optionName);
        endforeach;
    }
    register_uninstall_hook( __FILE__ , 'chld_thm_cfg_plugins_delete_plugin');

    global $chld_thm_cfg_plugins;
    $chld_thm_cfg_plugins = new ChildThemePluginConfigurator();
