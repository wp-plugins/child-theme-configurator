<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.4.3
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
//            wp_enqueue_script('thickbox');
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
            || ! $this->check_theme_exists($this->css->get_prop('child'))
            || ! $this->check_theme_exists($this->css->get_prop('parnt'))            
            // upgrade to v.1.1.1 
            || !($version = $this->css->get_prop('version'))
            )

            $this->css = new Child_Theme_Configurator_CSS();
    }
    
    function write_config() {
        if (!isset($_POST['ctc_load_styles']) 
            && !isset($_POST['ctc_parnt_templates_submit'])
            && !isset($_POST['ctc_child_templates_submit'])
            && !isset($_POST['ctc_image_submit'])
            && !isset($_POST['ctc_theme_image_submit'])
            && !isset($_POST['ctc_theme_screenshot_submit'])) return false;
        $this->errors = array();
        //die(print_r($_POST, true));
        if (current_user_can('install_themes')): // && $this->validate_post()):
            if (isset($_POST['ctc_load_styles'])):
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
                    $msg = 1; //isset($_POST['ctc_scan_subdirs']) ? '9&tab=import_options' : 1;
                endif;
            elseif (isset($_POST['ctc_parnt_templates_submit']) && isset($_POST['ctc_file_parnt'])):
                foreach ($_POST['ctc_file_parnt'] as $file):
                    $this->copy_parent_file(sanitize_text_field($file));
                endforeach;
                $msg = '8&tab=file_options';
            elseif (isset($_POST['ctc_child_templates_submit']) && isset($_POST['ctc_file_child'])):
                foreach ($_POST['ctc_file_child'] as $file):
                    $this->delete_child_file(sanitize_text_field($file));
                endforeach;
                $msg = '8&tab=file_options';
            elseif (isset($_POST['ctc_image_submit']) && isset($_POST['ctc_img'])):
                foreach ($_POST['ctc_img'] as $file):
                    
                    $this->delete_child_file('images/' . sanitize_text_field($file), 'img');
                endforeach;
                $msg = '8&tab=file_options';
            elseif (isset($_POST['ctc_theme_image_submit']) && isset($_FILES['ctc_theme_image'])):
                $this->handle_file_upload('ctc_theme_image', 'images');
                $msg = '8&tab=file_options';
            elseif (isset($_POST['ctc_theme_screenshot_submit']) && isset($_FILES['ctc_theme_screenshot'])):
                // remove old screenshot
                foreach($this->image_formats as $ext):
                    $this->delete_child_file('screenshot', $ext);
                endforeach;
                $this->handle_file_upload('ctc_theme_screenshot');
                $msg = '8&tab=file_options';
            else:
                $msg = '8&tab=file_options';
            endif;
            if (empty($this->errors)):
                $this->update_redirect($msg);
            endif;
        else:
            $this->errors[] = __('You do not have permission to configure child themes.', 'chld_thm_cfg');
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
        $file = $obj->css->is_file_ok($obj->css->get_child_target('functions.php'), 'write');
        if ($file && !file_exists($file)):
            if (false === file_put_contents($file, 
                "<?php\n// Exit if accessed directly\nif ( !defined('ABSPATH')) exit;\n\n/* Add custom functions below */")) return false;
        endif;
    }
    
    function copy_screenshot($obj) {
        // always copy screenshot
        $this->copy_parent_file('screenshot'); 
    }
    
    function copy_parent_file($file, $ext = 'php') {
        $parent_file = NULL;
        if ('screenshot' == $file):
            foreach ($this->image_formats as $ext):
                if ($parent_file = $this->css->is_file_ok($this->css->get_parent_source('screenshot.' . $ext))) break;
            endforeach;
        else:
            $parent_file = $this->css->is_file_ok($this->css->get_parent_source($file . '.' . $ext));
        endif;
        $child_file  = $this->css->get_child_target($file . '.' . $ext);
        
        if ($parent_file && $child_file && !file_exists($child_file)):
            $childdir = dirname($child_file);
            if (! is_dir($childdir)):
                if (! @mkdir($childdir, 0755, true)):
                    return false;
                endif;
            elseif (! is_writable($childdir)):
                return false;
            endif;
            if (false === file_put_contents($child_file, 
                @file_get_contents($parent_file))) return false;
        endif;
    }
    
    function delete_child_file($file, $ext = 'php') {
        // verify file is in child theme and exists before removing.
        $file = ('img' == $ext ? $file : $file . '.' . $ext);
        $child_file  = $this->css->get_child_target($file);
        if ($this->css->is_file_ok($child_file, 'write') && file_exists($child_file)):
            if (false === @unlink($child_file)) return false;
        endif;
        
        
    }
    
    function handle_file_upload($field, $childdir = NULL){
        /* adapted from http://www.php.net/manual/en/features.file-upload.php#114004 */
        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if ( !isset($_FILES[$field]['error']) || is_array($_FILES[$field]['error']) ):
                throw new RuntimeException(__('Invalid parameters.', 'chld_thm_cfg'));
            endif;

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES[$field]['error']):
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException(__('Please select a file to upload.', 'chld_thm_cfg'));
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException(__('File is too large.', 'chld_thm_cfg'));
                default:
                    throw new RuntimeException(__('There was a problem uploading the file.', 'chld_thm_cfg'));
            endswitch;

            if ($_FILES[$field]['size'] > 1024 * 1024):
                throw new RuntimeException(__('Theme images cannot be over 1MB.', 'chld_thm_cfg'));
            endif;
            
            if (false === ($ext = array_search(
                exif_imagetype($_FILES[$field]['tmp_name']),
                array(
                    'jpg' => IMAGETYPE_JPEG,
                    'png' => IMAGETYPE_PNG,
                    'gif' => IMAGETYPE_GIF,
                ),
                true
            ))):
                throw new RuntimeException(__('Theme images must be JPG, PNG or GIF.', 'chld_thm_cfg'));
            endif;
            // strip extension
            $filename = preg_replace('%\.[^\.]+$%', '', $_FILES[$field]['name']);
            // strip non alphas and replace with dash
            $filename = preg_replace('%[^\w]+%', '-', $filename);
            // Ensure target is in child theme
            $target = $this->css->get_child_target(isset($childdir) ? $childdir . '/' . $filename . '.' . $ext : 'screenshot.' . $ext);
            $targetdir = dirname($target);
            if (! is_dir($targetdir)):
                if (! @mkdir($targetdir, 0755, true)):
                    throw new RuntimeException(__('Unable to create directory.', 'chld_thm_cfg'));
                endif;
            elseif (! is_writable($targetdir)):
                throw new RuntimeException(__('Child theme directory is not writable.', 'chld_thm_cfg'));
            endif;
            if (!$target || !move_uploaded_file(
                $_FILES[$field]['tmp_name'],
                $target
            )):
                throw new RuntimeException(__('There was a problem uploading the file.', 'chld_thm_cfg'));
            endif;

        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
        }
    }
}