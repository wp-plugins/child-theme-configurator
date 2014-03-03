<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.2.3
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/
require_once('class-ctc-ui.php');
require_once('class-ctc-css.php');
class Child_Theme_Configurator {

    var $version = '1.2.3';
    var $css;
    var $optionsName;
    var $menuName;
    var $langName;
    var $pluginName;
    var $shortName;
    var $ns;
    var $ui;
    var $themes;
    var $errors;
    var $hook;
    var $is_ajax;
    var $updated;

    function __construct($file) {
        $this->dir = dirname( $file );
        $this->optionsName  = 'chld_thm_cfg_options';
        $this->menuName     = 'chld_thm_cfg_menu';
        $lang_dir           = $this->dir . '/lang';
        load_plugin_textdomain('chld_thm_cfg', false, $lang_dir, $lang_dir);
        
        $this->pluginName   = __('Child Theme Configurator', 'chld_thm_cfg');
        $this->shortName    = __('Child Themes', 'chld_thm_cfg');
        $this->pluginPath   = $this->dir . '/';
        $this->pluginURL    = plugin_dir_url($file);

        // setup plugin hooks
        add_action('admin_menu',            array(&$this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        add_action('wp_ajax_ctc_update',    array(&$this, 'ajax_save_postdata' ));
        add_action('wp_ajax_ctc_query',     array(&$this, 'ajax_query_css' ));
        //add_action('update_option_' . $this->optionsName, array(&$this, 'update_redirect'), 10);
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
            wp_enqueue_script('chld-thm-cfg-admin', $this->pluginURL . 'js/chld-thm-cfg.min.js',
                array('jquery-ui-autocomplete'), '1.0', true);
            wp_localize_script( 'chld-thm-cfg-admin', 'ctcAjax', 
                apply_filters('ctc_localize_script', array(
                    'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                    'theme_uri'         => get_theme_root_uri(),
                    'themes'            => $this->themes,
                    'parnt'             => $this->css->get_property('parnt'),
                    'child'             => $this->css->get_property('child'),
                    'imports'           => $this->css->get_property('imports'),
                    'rule'              => $this->css->get_property('rule'),
                    'sel_ndx'           => $this->css->get_property('sel_ndx'),
                    'val_qry'           => array(),
                    'rule_val'          => array(),
                    'sel_val'           => array(),
                    'field_labels'      => array(
                        '_background_url'       => __('URL/None', 'chld_thm_cfg'),
                        '_background_origin'    => __('Origin', 'chld_thm_cfg'),
                        '_background_color1'    => __('Color 1', 'chld_thm_cfg'),
                        '_background_color2'    => __('Color 2', 'chld_thm_cfg'),
                        '_border_width'         => __('Width', 'chld_thm_cfg'),
                        '_border_style'         => __('Style', 'chld_thm_cfg'),
                        '_border_color'         => __('Color', 'chld_thm_cfg'),
                    ),
                    'load_txt'          => __('Are you sure? This will replace your current settings.', 'chld_thm_cfg'),
                    'swatch_txt'        => $this->ui->swatch_text,
                    'swatch_label'      => __('Sample', 'chld_thm_cfg'),
                    'important_label'   => __('<span style="font-size:10px">!</span>', 'chld_thm_cfg'),
                    'selector_txt'      => __('Selectors', 'chld_thm_cfg'),
                    'close_txt'         => __('Close', 'chld_thm_cfg'),
                    'edit_txt'          => __('Edit', 'chld_thm_cfg'),
                    'cancel_txt'        => __('Cancel', 'chld_thm_cfg'),
                    'rename_txt'        => __('Rename', 'chld_thm_cfg'),
                    'css_fail_txt'      => __('The stylesheet cannot be displayed.', 'chld_thm_cfg'),
                    'child_only_txt'    => __('(Child Only)', 'chld_thm_cfg'),
                    'inval_theme_txt'   => __('Please enter a valid Child Theme', 'chld_thm_cfg'),
                    'inval_name_txt'    => __('Please enter a valid Child Theme name', 'chld_thm_cfg'),
                    'theme_exists_txt'  => __('<strong>%s</strong> exists. Please enter a different Child Theme', 'chld_thm_cfg'),
                )));
        endif;
    }
            
    function options_panel() {
        $this->ui->render_options();
    }
    
    function ctc_page_init () {
        $this->get_themes();
        $this->load_css();
        $this->generate_stylesheet();
        $this->ui = new Child_Theme_Configurator_UI();
        $this->ui->render_help_tabs();
	}
    
    function get_themes() {
        $this->themes = array('child' => array(), 'parnt' => array());
        foreach (wp_get_themes() as $theme):
            $parent = $theme->parent();
            if (empty($parent)):
                $slug = $theme->get_template();
                $this->themes['parnt'][$slug] = array('Name' => $theme->get('Name'));
            else:
                $slug = $theme->get_stylesheet();
                $this->themes['child'][$slug] = array('Name' => $theme->get('Name'), 'Author' => $theme->get('Author'), 'Version' => $theme->get('Version'));
            endif;
        endforeach;
    }

    function load_css() {
        if (!($this->css = get_option($this->optionsName)) 
            || !is_object($this->css) 
            // upgrade to v.1.1.1 
            || !($version = $this->css->get_property('version'))
            )

            $this->css = new Child_Theme_Configurator_CSS();
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
            // clear updates so they aren't saved in options object
            $this->css->reset_updates();
            update_option($this->optionsName, $this->css);
            // send all updates back to browser to update cache
            die(json_encode($result));
        else:
            die(0);
        endif;
    }
    
    function ajax_query_css() {
        $this->is_ajax = true;
        if ($this->validate_post()):
            $this->load_css();
            $regex = "/^ctc_query_/";
            foreach(preg_grep($regex, array_keys($_POST)) as $key):
                $name = preg_replace($regex, '', $key);
                $param[$name] = sanitize_text_field($_POST[$key]);
            endforeach;
            if (!empty($param['obj'])):
                $result = array(
                    array(
                        'key'   => isset($param['key'])?$param['key']:'',
                        'obj'   => $param['obj'],
                        'data'  => $this->css->get_property($param['obj'], $param),
                    ),
                );
                die(json_encode($result));
            endif;
        endif;
        die(0);
    }
    
    function generate_stylesheet() {
        if (empty($_POST['ctc_load_styles'])) return;
        $this->errors = array();
        if (current_user_can('install_themes') && $this->validate_post()):
            foreach (array(
                'ctc_theme_parnt', 
                'ctc_child_type', 
                'ctc_theme_child', 
                'ctc_child_name', 
                'ctc_child_template', 
                'ctc_child_author',
                'ctc_child_version') as $postfield):
                $varparts = explode('_', $postfield);
                $varname = end($varparts);
                ${$varname} = empty($_POST[$postfield])?'':sanitize_text_field($_POST[$postfield]);
            endforeach;
            if ($parnt):
                if (! $this->check_theme_exists($parnt)):
                    $this->errors[] = sprintf(__('%s does not exist. Please select a valid Parent Theme', 'chld_thm_cfg'), $parnt);
                endif;
            else:
                $this->errors[] = __('Please select a valid Parent Theme', 'chld_thm_cfg');
            endif;
            if ('new' == $type):
                $child = strtolower(preg_replace("%[^\w\-]%", '', $template));
                if ($this->check_theme_exists($child)):
                    $this->errors[] = sprintf(__('<strong>%s</strong> exists. Please enter a different Child Theme template name', 'chld_thm_cfg'), $child);
                endif;
            endif;
            if (empty($child)):
                $this->errors[] = __('Please enter a valid Child Theme template name', 'chld_thm_cfg');
            endif;
            if (empty($name)):
                $this->errors[] = __('Please enter a valid Child Theme name', 'chld_thm_cfg');
            endif;
        else:
            $this->errors[] = __('You do not have permission to configure child themes.', 'chld_thm_cfg');
        endif;
        if (empty($this->errors)):
            $this->css = new Child_Theme_Configurator_CSS();
            $this->css->set_property('parnt', $parnt);
            $this->css->set_property('child', $child);
            $this->css->set_property('child_name', $name);
            $this->css->set_property('child_author', $author);
            $this->css->set_property('child_version', $version);
            $this->css->parse_css_file('parnt');
            $this->css->parse_css_file('child');
            if (!$this->css->write_css(isset($_POST['ctc_backup']))): // true backs up current stylesheet
                $this->errors[] = __('Your theme directory is not writable. Please adjust permissions and try again.', 'chld_thm_cfg');
                return false;
            endif;
            $this->css->reset_updates();
            if (update_option($this->optionsName, $this->css)):
                $this->update_redirect();
            else:
                $this->errors[] = sprintf(__('Child Theme %s was unchanged.', 'chld_thm_cfg'), $name, $this->optionsName);
            endif;
        endif;
    }
    
    function render_menu($template = 'child', $selected = null) {
        $menu = '<option value="">Select</option>' . LF;
        foreach ($this->themes[$template] as $slug => $theme):
            $menu .= '<option value="' . $slug . '"' . ($slug == $selected ? ' selected' : '') . '>' 
                . $slug . ' - "' . $theme['Name'] . '"' . '</option>' . LF;
        endforeach;
        return $menu;
    }
    
    function check_theme_exists($theme) {
        return in_array($theme, array_keys(wp_get_themes()));
    }
    
    /*
     * TODO: this is a stub for future use
     */
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