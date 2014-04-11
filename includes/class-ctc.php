<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.3.3
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
    var $image_formats;
    function __construct($file) {
        $this->dir = dirname( $file );
        $this->optionsName      = 'chld_thm_cfg_options';
        $this->menuName         = 'chld_thm_cfg_menu';
        $lang_dir               = $this->dir . '/lang';
        load_plugin_textdomain('chld_thm_cfg', false, $lang_dir, $lang_dir);
        
        $this->pluginName       = __('Child Theme Configurator', 'chld_thm_cfg');
        $this->shortName        = __('Child Themes', 'chld_thm_cfg');
        $this->pluginPath       = $this->dir . '/';
        $this->pluginURL        = plugin_dir_url($file);
        $this->image_formats    = array('jpg','jpeg','gif','png','JPG','JPEG','GIF','PNG');

        // setup plugin hooks
        add_action('admin_menu',                array(&$this, 'admin_menu'));
        add_action('admin_enqueue_scripts',     array(&$this, 'enqueue_scripts'));
        add_action('wp_ajax_ctc_update',        array(&$this, 'ajax_save_postdata' ));
        add_action('wp_ajax_ctc_query',         array(&$this, 'ajax_query_css' ));
        add_action('chld_thm_cfg_addl_files',   array(&$this, 'add_functions_file'), 10, 2);
        add_action('chld_thm_cfg_addl_files',   array(&$this, 'copy_screenshot'), 10, 2);
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
                apply_filters('chld_thm_cfg_localize_script', array(
                    'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                    'theme_uri'         => get_theme_root_uri(),
                    'themes'            => $this->themes,
                    'source'            => apply_filters('chld_thm_cfg_source_uri', get_theme_root_uri() . '/' 
                                            . $this->css->get_prop('parnt') . '/style.css', $this->css),
                    'target'            => apply_filters('chld_thm_cfg_target_uri', get_theme_root_uri() . '/' 
                                            . $this->css->get_prop('child') . '/style.css', $this->css),
                    'parnt'             => $this->css->get_prop('parnt'),
                    'child'             => $this->css->get_prop('child'),
                    'imports'           => $this->css->get_prop('imports'),
                    'rule'              => $this->css->get_prop('rule'),
                    'sel_ndx'           => $this->css->get_prop('sel_ndx'),
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
        $this->load_config();
        do_action('chld_thm_cfg_forms', $this);  // hook for custom forms
        $this->write_config();
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

    function validate_post($action = 'ctc_update', $noncefield = '_wpnonce') {
        return ('POST' == $_SERVER['REQUEST_METHOD'] 
            && current_user_can('edit_theme_options')
            && ($this->is_ajax ? check_ajax_referer( $action, $noncefield, false ) : check_admin_referer($action, $noncefield, false )));
    }
    
    function ajax_save_postdata() {
        $this->is_ajax = true;
        if ($this->validate_post()):
            $this->load_config();
            $this->css->parse_post_data();
            $this->css->write_css();
            $result = $this->css->get_prop('updates');
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
            $this->load_config();
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
                        'data'  => $this->css->get_prop($param['obj'], $param),
                    ),
                );
                die(json_encode($result));
            endif;
        endif;
        die(0);
    }
    
    function load_config() {
        if (!($this->css = get_option($this->optionsName)) 
            || !is_object($this->css) 
            // upgrade to v.1.1.1 
            || !($version = $this->css->get_prop('version'))
            )

            $this->css = new Child_Theme_Configurator_CSS();
    }
    
    function write_config() {
        if (!isset($_POST['ctc_load_styles'])) return false;
        $this->errors = array();
        if (current_user_can('install_themes') && $this->validate_post()):
            foreach (array(
                'ctc_theme_parnt', 
                'ctc_child_type', 
                'ctc_theme_child', 
                'ctc_child_name',
                'ctc_configtype', 
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
                $configtype = 'theme'; // no custom stylesheets until style.css exists!
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
            if (false === $this->verify_child_theme($child)):
                $this->errors[] = __('Your theme directories are not writable. Please adjust permissions and try again.', 'chld_thm_cfg');
            endif;
        else:
            $this->errors[] = __('You do not have permission to configure child themes.', 'chld_thm_cfg');
        endif;
        if (empty($this->errors)):
            $this->css = new Child_Theme_Configurator_CSS();
            $this->css->set_prop('parnt', $parnt);
            $this->css->set_prop('child', $child);
            $this->css->set_prop('child_name', $name);
            $this->css->set_prop('child_author', $author);
            $this->css->set_prop('child_version', $version);
            $this->css->set_prop('configtype', $configtype);
            do_action('chld_thm_cfg_addl_files', $this);   // hook for add'l plugin files and subdirectories
            $this->css->parse_css_file('parnt');
            $this->css->parse_css_file('child');
            if (false === $this->css->write_css(isset($_POST['ctc_backup']))):
                $this->errors[] = __('Your stylesheet is not writable. Please adjust permissions and try again.', 'chld_thm_cfg');
                return false;
            endif; 
            $this->css->reset_updates();
            update_option($this->optionsName, $this->css);
            do_action('chld_thm_cfg_addl_options', $this); // hook for add'l plugin options
            $msg = isset($_POST['ctc_scan_subdirs']) ? '9&tab=import_options' : 1;
            $this->update_redirect($msg);
        endif;
        //$this->errors[] = sprintf(__('Child Theme %s was unchanged.', 'chld_thm_cfg'), $name, $this->optionsName);
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
    
    function update_redirect($msg = 1) {
        if (empty($this->is_ajax)):
            wp_safe_redirect(admin_url('tools.php?page=' . $this->menuName . '&updated=' . $msg));
            die();
        endif;
    }
    
    function verify_child_theme($child) {
        $themedir = get_theme_root();
        if (! is_writable($themedir)) return false;
        $childdir = $themedir . '/' . $child;

        if (! is_dir($childdir)):
            if (! mkdir($childdir, 0755)):
                return false;
            endif;
        elseif (! is_writable($childdir)):
            return false;
        endif;
    }
    
    function add_functions_file($obj){
        // add functions.php file
        $file = $obj->css->is_file_ok($obj->css->get_child_target('functions.php'));
        if ($file && !file_exists($file)):
            if (false === file_put_contents($file, 
                "<?php\n// Exit if accessed directly\nif ( !defined('ABSPATH')) exit;\n\n/* Add custom functions below */")) return false;
        endif;
    }
    
    function copy_screenshot($obj) {
        foreach ($this->image_formats as $ext):
            if ($screenshot_parent = $obj->css->is_file_ok($obj->css->get_parent_source('screenshot.' . $ext))) break;
        endforeach;
        $screenshot_child  = $obj->css->get_child_target('screenshot.' . $ext);
        if ($screenshot_parent && $screenshot_child && !file_exists($screenshot_child)):
            if (false === file_put_contents($screenshot_child, 
                @file_get_contents($screenshot_parent))) return false;
        endif;
    }
}