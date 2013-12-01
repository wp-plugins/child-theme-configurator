<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.0.2
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2013 Lilaea Media
*/
require_once('class-ctc-ui.php');
require_once('class-ctc-css.php');
class Child_Theme_Configurator {

    var $version = '1.0.2';
    var $css;
    var $optionsName;
    var $menuName;
    var $langName;
    var $pluginName;
    var $shortName;
    var $ns;
    var $ui;
    var $errors;
    var $hook;
    var $is_ajax;
    var $updated;

    function __construct($file) {
        $this->dir = dirname( $file );
        $this->ns = CHLD_THM_CFG_NS;
        $this->optionsName = $this->ns . '_options';
        $this->menuName = $this->ns . '_menu';
        
        $lang_dir             = $this->dir . '/lang';
        load_plugin_textdomain($this->ns, false, $lang_dir, $lang_dir);
        
        $this->pluginName = __('Child Theme Configurator', $this->ns);
        $this->shortName = __('Child Themes', $this->ns);
        $this->pluginPath    = $this->dir . '/';
        $this->pluginURL     = plugin_dir_url($file);

        // setup plugin hooks
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        add_action('wp_ajax_ctc_update',    array(&$this, 'ajax_save_postdata' ));
        add_action('update_option_' . $this->optionsName, array(&$this, 'update_redirect'), 10);
    }

    function admin_menu() {
        $this->hook = add_management_page($this->pluginName, $this->shortName, 'edit_theme_options', $this->menuName, array(&$this, 'options_panel') );
        // only load plugin-specific data 
        // when ctc page is loaded
        add_action( 'load-' . $this->hook, array(&$this, 'ctc_page_init') );
    }
    
    function enqueue_scripts($hook) {
        if ($this->hook == $hook):
            wp_enqueue_style('chld-thm-cfg-admin', $this->pluginURL . 'css/chld-thm-cfg.css');
            wp_enqueue_script('iris');
            wp_enqueue_script('ctc-thm-cfg-ctcgrad', $this->pluginURL . 'js/ctcgrad.min.js', array('iris'), '1.0');
            wp_enqueue_script('chld-thm-cfg-admin', $this->pluginURL . 'js/chld-thm-cfg.js', //'js/chld-thm-cfg.min.js',
                array('jquery-ui-autocomplete'), '1.0', true);
            wp_localize_script( 'chld-thm-cfg-admin', 'ctcAjax', 
                apply_filters('ctc_localize_script', array(
                    'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                    'theme_uri'     => get_theme_root_uri(),
                    'load_msg'      => __('Are you sure? This will replace your current settings.', $this->ns),
                    'parent_theme'  => $this->css->get_property('parent_theme'),
                    'child_theme'   => $this->css->get_property('child_theme'),
                    'data'          => $this->css->get_property('data'),
                    'imports'       => $this->css->get_property('imports'),
                    'sel_ndx'       => $this->css->get_property('sel_ndx'),
                    'val_ndx'       => $this->css->get_property('val_ndx'),
                    'labels'        => array(
                        '_background_url'       => __('URL/None', $this->ns),
                        '_background_origin'    => __('Origin', $this->ns),
                        '_background_color1'    => __('Color 1', $this->ns),
                        '_background_color2'    => __('Color 2', $this->ns),
                        '_border_width'         => __('Width', $this->ns),
                        '_border_style'         => __('Style', $this->ns),
                        '_border_color'         => __('Color', $this->ns),
                    ),
                    'swatch_text'   => $this->ui->swatch_text,
                    'swatch_label'  => __('Sample', $this->ns),
                    'selector_text' => __('Selectors', $this->ns),
                    'close_text'    => __('Close', $this->ns),
                    'new_query'     => __('New Query', $this->ns),
                    'new_selector'  => __('New Selector', $this->ns),
                    'css_fail'      => __('The stylesheet cannot be displayed.', $this->ns),
                    'child_only'    => __('(Child Only)', $this->ns),
                )));
        endif;
    }
            
    function options_panel() {
        $this->ui->render_options();
        //print_r($this->css->get_property('data'));
    }
    
    function ctc_page_init () {
        $this->load_css();
        $this->ui = new Child_Theme_Configurator_UI();
        $this->ui->render_help_tabs();
        $this->handle_inputs();
        if ($this->updated):
            $this->css->reset_updates();
            update_option($this->optionsName, $this->css);
        endif;
	}
    function load_css() {
        if (!($this->css = get_option($this->optionsName)))
            $this->css = new Child_Theme_Configurator_CSS();
        $this->updated = false;
        $upgrade = $this->css->get_property('version');
        if (empty($upgrade)):
            // upgrade val_ndx to 1.0.2 data structure
            $this->css->upgrade();
            $this->css->set_property('version', $this->version);
            $this->updated = true;
        endif;
    }
    
    function validate_post() {
        return ('POST' == $_SERVER['REQUEST_METHOD'] 
            && current_user_can('edit_theme_options')
            && ($this->is_ajax ? check_ajax_referer( 'ctc_update', '_wpnonce', false ) : check_admin_referer( 'ctc_update', '_wpnonce', false )));
    }
    
    function ajax_save_postdata() {
        $this->is_ajax = true;
        if ($this->validate_post()):
            $this->load_css();
            $this->css->parse_post_data();
            $this->css->write_css();
            $result = $this->css->get_property('updates');
            $this->css->reset_updates();
            update_option($this->optionsName, $this->css);
            die(json_encode($result));
        else:
            die(0);
        endif;
    }
    
    function handle_inputs() {
        if (isset($_POST['ctc_load_styles']) && current_user_can('install_themes') && $this->validate_post()):
            $author = '';
            if (isset($_POST['ctc_theme_parent'])):
                $theme = sanitize_text_field($_POST['ctc_theme_parent']);
                if (! $this->check_theme_exists($theme)):
                    $this->errors[] = sprintf(__('%s does not exist. Please select a valid Parent Theme', $this->ns), $theme);
                    return false;
                else:
                    $this->css->set_property('parent_theme', $theme);
                endif;
            endif;
            if (isset($_POST['ctc_theme_child'])):
                $theme = sanitize_text_field($_POST['ctc_theme_child']);
                $theme_name = empty($_POST['ctc_child_name']) ? '' : sanitize_text_field($_POST['ctc_child_name']);
                if ('new' == $theme):
                    $theme = empty($_POST['ctc_child_template']) ? '' : 
                        strtolower(preg_replace("%\W%", '', sanitize_text_field($_POST['ctc_child_template'])));
                if (empty($theme)):
                    $this->errors[] = __('Please enter a valid Child Theme template name', $this->ns);
                    return false;
                elseif ($this->check_theme_exists($theme)):
                    $this->errors[] = sprintf(__('%s exists. Please enter a different Child Theme template name', $this->ns), $theme);
                    return false;
                elseif (empty($theme_name)):
                    $this->errors[] = sprintf(__('Please enter a valid Child Theme name', $this->ns), $theme_name);
                    return false;
                endif;
                elseif (!$this->check_theme_exists($theme)):
                    $this->errors[] = sprintf(__('%s does not exist. Please select a valid Child Theme', $this->ns), $theme);
                    return false;
                endif;
                $this->css->set_property('child_theme', $theme);
                $this->css->set_property('child_theme_name', $theme_name);
                if (isset($_POST['ctc_theme_author'])):
                    $this->css->set_property('author', sanitize_text_field($_POST['ctc_theme_author']));
                endif;
            endif;
            $this->css->parse_css_file('parent_theme');
            $this->css->parse_css_file('child_theme');
            $this->css->write_css();
            $this->updated = true;
        endif;        
    }
    
    function check_theme_exists($theme) {
        return in_array(strtolower($theme), array_keys(wp_get_themes()));
    }
    
    function sanitize_options($input) {
        return $input;
    }
    
    function update_redirect() {
        if (empty($this->is_ajax)):
            wp_safe_redirect(admin_url('tools.php?page=' . $this->menuName . '&updated=true'));
            die();
        endif;
    }

}