<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.5.2.1
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
    var $cache_updates;
    var $uploadsubdir;
    var $fs;
    var $fs_prompt;
    var $fs_method;
    var $postarrays;
    var $imgmimes;
    var $updates;
    
    function __construct($file) {
        $this->dir = dirname( $file );
        $this->optionsName      = 'chld_thm_cfg_options';
        $this->menuName         = 'chld_thm_cfg_menu';
        $lang_dir               = $this->dir . '/lang';
        load_plugin_textdomain('chld_thm_cfg', FALSE, $lang_dir, $lang_dir);
        
        $this->pluginName       = __('Child Theme Configurator', 'chld_thm_cfg');
        $this->shortName        = __('Child Themes', 'chld_thm_cfg');
        $this->pluginPath       = $this->dir . '/';
        $this->pluginURL        = plugin_dir_url($file);
        $this->imgmimes         = array(
        	'jpg|jpeg|jpe'  => 'image/jpeg',
	        'gif'           => 'image/gif',
	        'png'           => 'image/png',
        );
        $this->postarrays = array(
            'ctc_img',
            'ctc_file_parnt',
            'ctc_file_child',
        );
        $this->updates          = array();
        $this->cache_updates    = TRUE;
        // setup plugin hooks
        add_action('admin_menu',                array(&$this, 'admin_menu'));
        add_action('admin_enqueue_scripts',     array(&$this, 'enqueue_scripts'));
        add_action('wp_ajax_ctc_update',        array(&$this, 'ajax_save_postdata' ));
        add_action('wp_ajax_ctc_query',         array(&$this, 'ajax_query_css' ));
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
            wp_enqueue_script('chld-thm-cfg-admin', $this->pluginURL . 'js/chld-thm-cfg.js',
                array('jquery-ui-autocomplete'), '1.0', TRUE);
            wp_localize_script( 'chld-thm-cfg-admin', 'ctcAjax', 
                apply_filters('chld_thm_cfg_localize_script', array(
                    'homeurl'           => get_home_url(),
                    'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                    'theme_uri'         => get_theme_root_uri(),
                    'themes'            => $this->themes,
                    'source'            => apply_filters('chld_thm_cfg_source_uri', get_theme_root_uri() . '/' 
                                            . $this->css->get_prop('parnt') . '/style.css', $this->css),
                    'target'            => apply_filters('chld_thm_cfg_target_uri', get_theme_root_uri() . '/' 
                                            . $this->css->get_prop('child') . '/style.css', $this->css),
                    'parnt'             => $this->css->get_prop('parnt'),
                    'child'             => $this->css->get_prop('child'),
                    'addl_css'          => $this->css->get_prop('parntss'),
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
            && ($this->is_ajax ? check_ajax_referer( $action, $noncefield, FALSE ) : check_admin_referer($action, $noncefield, FALSE )));
    }
    
    function ajax_save_postdata() {
        $this->is_ajax = TRUE;
        if ($this->validate_post()):
            $this->verify_creds();
            $this->load_config();
            $this->css->parse_post_data();
            $this->css->write_css();
            $result = $this->css->obj_to_utf8($this->updates);
            update_option($this->optionsName, $this->css);
            // send all updates back to browser to update cache
            die(json_encode($result));
        else:
            die(0);
        endif;
    }
    
    function ajax_query_css() {
        $this->is_ajax = TRUE;
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
            || !($version = $this->css->get_prop('version'))
            ):
            $parent = get_template();
            $this->css = new Child_Theme_Configurator_CSS($parent);
        endif;
        if ('POST' != $_SERVER['REQUEST_METHOD']):
            if ($this->css->get_prop('child')):
                $this->verify_creds();
                $stylesheet = $this->css->get_child_target('style.css');
                if (!is_writable($stylesheet) && !$this->fs):
	                add_action('admin_notices', array($this, 'writable_notice')); 	
                endif;
            endif;
            if (fileowner($this->css->get_child_target('')) != fileowner(ABSPATH)):
	            add_action('admin_notices', array($this, 'owner_notice')); 
            endif;
        endif;	
    }
    
    function write_config() {
        if (!isset($_POST['ctc_load_styles']) 
            && !isset($_POST['ctc_parnt_templates_submit'])
            && !isset($_POST['ctc_child_templates_submit'])
            && !isset($_POST['ctc_image_submit'])
            && !isset($_POST['ctc_theme_image_submit'])
            && !isset($_POST['ctc_theme_screenshot_submit'])
            && !isset($_POST['ctc_export_child_zip'])
            && !isset($_POST['ctc_reset_permission'])
            && !isset($_POST['ctc_templates_writable_submit'])
            && !isset($_POST['ctc_set_writable'])) return FALSE;
        $this->errors = array();
        if ($this->validate_post()):
            if (isset($_POST['ctc_export_child_zip'])):
                $this->export_zip();
                $this->errors[] = __('Zip file creation failed.', 'chld_thm_cfg');
            else:
                if (isset($_POST['ctc_theme_image_submit']) && isset($_FILES['ctc_theme_image'])):
                    $this->handle_file_upload('ctc_theme_image', $this->imgmimes);            
                elseif (isset($_POST['ctc_theme_screenshot_submit']) && isset($_FILES['ctc_theme_screenshot'])):
                    $this->handle_file_upload('ctc_theme_screenshot', $this->imgmimes);
                endif;
                $args = preg_grep("/nonce/", array_keys($_POST), PREG_GREP_INVERT);
                $this->verify_creds($args);
                
                if ($this->fs):
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
                            if (empty($template) && empty($name)):
                                $this->errors[] = __('Please enter a valid Child Theme template name', 'chld_thm_cfg');
                            else:
                                $configtype = 'theme'; // no custom stylesheets until style.css exists!
                                add_action('chld_thm_cfg_addl_files',   array(&$this, 'add_base_files'), 10, 2);
                                add_action('chld_thm_cfg_addl_files',   array(&$this, 'copy_screenshot'), 10, 2);
                                $child = strtolower(preg_replace("%[^\w\-]%", '', empty($template) ? $name : $template));
                                if ($this->check_theme_exists($child)):
                                    $this->errors[] = sprintf(__('<strong>%s</strong> exists. Please enter a different Child Theme template name', 'chld_thm_cfg'), $child);
                                endif;
                            endif;
                        elseif (empty($configtype) || 'theme' == $configtype):
                            add_action('chld_thm_cfg_addl_files',   array(&$this, 'add_base_files'), 10, 2);
                            add_action('chld_thm_cfg_addl_files',   array(&$this, 'copy_screenshot'), 10, 2);
                        elseif(has_action('chld_thm_cfg_addl_files')):
                            remove_all_actions('chld_thm_cfg_addl_files');
                            // back compat for plugins extension
                            add_action('chld_thm_cfg_addl_files', array(&$this, 'write_addl_files'), 10, 2);
                        endif;
                        if (empty($name)):
                            $name = ucfirst($child);
                        endif;
                        if (empty($child)):
                            $this->errors[] = __('Please enter a valid Child Theme name', 'chld_thm_cfg');
                        endif;
                        if (FALSE === $this->verify_child_dir($child)):
                            $this->errors[] = __('Your theme directories are not writable.', 'chld_thm_cfg');
	                        add_action('admin_notices', array($this, 'writable_notice')); 	
                        endif;
                        if (empty($this->errors)):
                            $this->css = new Child_Theme_Configurator_CSS();
                            $this->css->set_prop('parnt', $parnt);
                            $this->css->set_prop('child', $child);
                            $this->css->set_prop('child_name', $name);
                            $this->css->set_prop('child_author', $author);
                            $this->css->set_prop('child_version', strlen($version) ? $version : '1.0');
                            $this->css->set_prop('configtype', $configtype);
                            do_action('chld_thm_cfg_addl_files', $this);   // hook for add'l plugin files and subdirectories
                            $this->css->parse_css_file('parnt');
                            $this->css->parse_css_file('child');
                            if (isset($_POST['ctc_additional_css']) && is_array($_POST['ctc_additional_css'])):
                                $this->css->parentss = array();
                                foreach ($_POST['ctc_additional_css'] as $file):
                                    $this->css->parse_css_file('parnt', $file);
                                    $this->css->parntss[] = $file;
                                endforeach;
                            endif;
                            if (FALSE === $this->css->write_css(isset($_POST['ctc_backup']))):
                                $this->errors[] = __('Your stylesheet is not writable.', 'chld_thm_cfg');
	                            add_action('admin_notices', array($this, 'writable_notice')); 	
                                return FALSE;
                            endif; 
                            
                            // copy parent theme mods
                            if (isset($_POST['ctc_parent_mods']) && ($parent_mods = get_option('theme_mods_' . $parnt))):
                                update_option('theme_mods_' . $child, $parent_mods);
                            endif;
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
                    elseif (isset($_POST['ctc_templates_writable_submit']) && isset($_POST['ctc_file_child'])):
                        foreach ($_POST['ctc_file_child'] as $file):
                            $this->set_writable(sanitize_text_field($file));
                        endforeach;
                        $msg = '8&tab=file_options';
                    elseif (isset($_POST['ctc_set_writable'])):
                        $this->set_writable();
                        $msg = '8&tab=file_options';
                    elseif (isset($_POST['ctc_reset_permission'])):
                        $this->unset_writable();
                        $msg = '8&tab=file_options';
                    elseif (isset($_POST['ctc_theme_image_submit']) && isset($_POST['movefile'])):
                        $this->move_file_upload('images');
                        $msg = '8&tab=file_options';
                    elseif (isset($_POST['ctc_theme_screenshot_submit']) && isset($_POST['movefile'])):
                        // remove old screenshot
                        foreach(array_keys($this->imgmimes) as $extreg): 
                            foreach (explode('|', $extreg) as $ext):
                                $this->delete_child_file('screenshot', $ext);
                            endforeach; 
                        endforeach;
                        $this->move_file_upload('');
                        $msg = '8&tab=file_options';
                    else:
                        $msg = '8&tab=file_options';
                    endif;
                endif;
            endif;
            if (empty($this->errors) && empty($this->fs_prompt)):
                $this->update_redirect($msg);
            endif;
            return FALSE;
        endif;
        $this->errors[] = __('You do not have permission to configure child themes.', 'chld_thm_cfg');
        return FALSE;
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
    
    function verify_child_dir($path) {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $themedir = $wp_filesystem->find_folder(get_theme_root());
        if (! $wp_filesystem->is_writable($themedir)) return FALSE;
        $childparts = explode('/', wp_normalize_path($path));
        while (count($childparts)):
            $subdir = array_shift($childparts);
            if (empty($subdir)) continue;
            $themedir = trailingslashit($themedir) . $subdir;
            if (! $wp_filesystem->is_dir($themedir)):
                if (! $wp_filesystem->mkdir($themedir, FS_CHMOD_DIR)):
                    return FALSE;
                endif;
            elseif (! $wp_filesystem->is_writable($themedir)):
                return FALSE;
            endif;
        endwhile;
        return TRUE;
    }
    
    function add_base_files($obj){
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        // add functions.php file
        $contents = "<?php\n// Exit if accessed directly\nif ( !defined('ABSPATH')) exit;\n\n/* Add custom functions below */";
        if (FALSE === $this->write_child_file('functions.php', $contents) || FALSE === $this->write_child_file('style.css', $this->css->get_css_header())) return FALSE;
    }
    
    function write_child_file($file, $contents) {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $file = $this->fspath($this->css->is_file_ok($this->css->get_child_target($file), 'write'));
        if ($file && !$wp_filesystem->exists($file)):
                if (FALSE === $wp_filesystem->put_contents($file, $contents)) return FALSE; 
        endif;
    }
    
    function copy_screenshot($obj) {
        // always copy screenshot
        $this->copy_parent_file('screenshot'); 
    }
    
    function copy_parent_file($file, $ext = 'php') {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $parent_file = NULL;
        if ('screenshot' == $file):
            foreach (array_keys($this->imgmimes) as $extreg): 
                foreach(explode('|', $extreg) as $ext):
                    if ($parent_file = $this->css->is_file_ok($this->css->get_parent_source('screenshot.' . $ext))) break;
                endforeach; 
                if ($parent_file):
                    $parent_file = $this->fspath($parent_file);
                    break;
                endif;
            endforeach;
        else:
            $parent_file = $this->fspath($this->css->is_file_ok($this->css->get_parent_source($file . '.' . $ext)));
        endif;
        // get child theme + file + ext (passing empty string and full child path to theme_basename )
        $child_file = $this->css->get_child_target($file . '.' . $ext);
        // return true if file already exists
        if ($wp_filesystem->exists($this->fspath($child_file))) return TRUE;
        $child_dir = dirname($this->theme_basename('', $child_file));
        if ($parent_file // sanity check
            && $child_file // sanity check
            && $this->verify_child_dir($child_dir) //create child subdir if necessary
            && $wp_filesystem->copy($parent_file, $this->fspath($child_file), FS_CHMOD_FILE)) return TRUE;
        $this->errors[] = __('Could not copy file.', 'chld_thm_cfg');
    }
    
    function delete_child_file($file, $ext = 'php') {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        // verify file is in child theme and exists before removing.
        $file = ('img' == $ext ? $file : $file . '.' . $ext);
        $child_file  = $this->fspath($this->css->is_file_ok($this->css->get_child_target($file), 'write'));
        if ($wp_filesystem->exists($child_file)):
            if (!$wp_filesystem->delete($child_file)) return FALSE;
        endif;
    }
    
    function get_additional_css($parnt) {
        $files = array();
        foreach ($this->css->recurse_directory(trailingslashit(get_theme_root()) . $parnt) as $file):
            $file = $this->theme_basename($parnt, $file);
            if ('style.css' != $file) $files[] = $file;
        endforeach;
        return $files;
    }
    
    function theme_basename($theme, $file) {
        // if no theme passed, returns theme + file
        $themedir = trailingslashit(get_theme_root()) . ('' == $theme ? '' : trailingslashit($theme));
        return preg_replace('%^' . preg_quote($themedir) . '%', '', $file);
    }
    
    function uploads_basename($file) {
        $file = wp_normalize_path($file);
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit($uplarr['basedir']);
        return preg_replace('%^' . preg_quote($upldir) . '%', '', $file);
    }
    
    function uploads_fullpath($file) {
        $file = wp_normalize_path($file);
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit($uplarr['basedir']);
        return $upldir . $file;
    }
    
    function serialize_postarrays() {
        foreach ($this->postarrays as $field)
            if (isset($_POST[$field]) && is_array($_POST[$field]))
                $_POST[$field] = implode("%%", $_POST[$field]);
    }
    
    function unserialize_postarrays() {
        foreach ($this->postarrays as $field)
            if (isset($_POST[$field]) && !is_array($_POST[$field]))
                $_POST[$field] = explode("%%", $_POST[$field]);
    }
    
    function set_writable($file = NULL) {

        $file = isset($file) ? $this->css->get_child_target($file . '.php') : apply_filters('chld_thm_cfg_target', $this->css->get_child_target(), $this->css);
        if ($this->fs): // filesystem access
            global $wp_filesystem;
            if ($file && $wp_filesystem->chmod($this->fspath($file), 0666)) return;
        endif;
        $this->errors[] = __('Could not set write permissions.', 'chld_thm_cfg');
        add_action('admin_notices', array($this, 'writable_notice')); 	
        return FALSE;
    }
    
    function unset_writable() {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $dir        = untrailingslashit($this->css->get_child_target(''));
        $child      = $this->theme_basename('', $dir);
        $newchild   = untrailingslashit($child) . '-new';
        $themedir   = trailingslashit(get_theme_root());
        $fsthemedir = $this->fspath($themedir);
        // is child theme owned by user? 
        if (fileowner($dir) == fileowner(ABSPATH)):
            $copy   = FALSE;
            $wp_filesystem->chmod($dir);
            // recursive chmod (as user)
            // WP_Filesystem RECURSIVE CHMOD IS FLAWED! IT SETS ALL CHILDREN TO PERM OF OUTERMOST DIR
            //if ($wp_filesystem->chmod($this->fspath($dir), FALSE, TRUE)):
            //endif;
        else:
            $copy   = TRUE;
        endif;
        // n -> copy entire folder (as user)
        $files = $this->css->recurse_directory($dir, NULL, TRUE);
        $errors = array();
        foreach ($files as $file):
            $childfile  = $this->theme_basename($child, wp_normalize_path($file));
            $newfile    = trailingslashit($newchild) . $childfile;
            $childpath  = $fsthemedir . trailingslashit($child) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            if ($copy):
                if ($this->verify_child_dir(is_dir($file)?$newfile:dirname($newfile))):
                    if (is_file($file) && !$wp_filesystem->copy($childpath, $newpath)):
                        $errors[] = 'could not copy ' . $newpath;
                    endif;
                else:
                    $errors[] = 'invalid dir: ' . $newfile;
                endif;
            else:
                $wp_filesystem->chmod($this->fspath($file));
            endif;
        endforeach;
        if ($copy):
            // verify copy (as webserver)
            $newfiles = $this->css->recurse_directory(trailingslashit($themedir) . $newchild, NULL, TRUE);
            $deleteddirs = $deletedfiles = 0;
            if (count($newfiles) == count($files)):
                // rename old (as webserver)
                if (!$wp_filesystem->exists(trailingslashit($fsthemedir) . $child . '-old'))
                    $wp_filesystem->move(trailingslashit($fsthemedir) . $child, trailingslashit($fsthemedir) . $child . '-old');
                // rename new (as user)
                if (!$wp_filesystem->exists(trailingslashit($fsthemedir) . $child))
                    $wp_filesystem->move(trailingslashit($fsthemedir) . $newchild, trailingslashit($fsthemedir) . $child);
                // remove old files (as webserver)
                $oldfiles = $this->css->recurse_directory(trailingslashit($themedir) . $child . '-old', NULL, TRUE);
                array_unshift($oldfiles, trailingslashit($themedir) . $child . '-old');
                foreach (array_reverse($oldfiles) as $file):
                    if ($wp_filesystem->delete($this->fspath($file)) || (is_dir($file) && @rmdir($file)) || (is_file($file) && @unlink($file))):
                        $deletedfiles++;
                    endif;
                endforeach;
                if ($deletedfiles != count($oldfiles)):
                    $errors[] = 'deleted: ' . $deletedfiles . ' != ' . count($oldfiles) . ' files';
                endif;
            else:
                $errors[] = 'newfiles != files';
            endif;
        endif;
        if (count($errors)):
            $this->errors[] = __('There were errors while resetting permissions.', 'chld_thm_cfg') ;
            add_action('admin_notices', array($this, 'writable_notice')); 	
        endif;
    }
    
    function handle_file_upload($field, $childdir = NULL, $mimes = NULL){
        $uploadedfile = $_FILES[$field];
        $upload_overrides = array( 
            'test_form' => FALSE,
            'mimes' => (is_array($mimes) ? $mimes : NULL)
        );
        if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        if (isset($movefile['error'])):
            $this->errors[] = $movefile['error'];
            return FALSE;
        endif;
        $_POST['movefile'] = $this->uploads_basename($movefile['file']);        
    }
    
    function move_file_upload($subdir = 'images') {
        if (!$this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $source_file = sanitize_text_field($_POST['movefile']);
        $target_file = ('' == $subdir ? preg_replace("%^.+(\.\w+)$%", "screenshot$1", basename($source_file)) : trailingslashit($subdir) . basename($source_file));
        if (FALSE !== $this->verify_child_dir(trailingslashit($this->css->get_prop('child')) . $subdir)):
            $source_path = $this->fspath($this->uploads_fullpath($source_file));
            if ($target_path = $this->css->is_file_ok($this->css->get_child_target($target_file), 'write')):
                $target_path = $this->fspath($target_path);
                if ($wp_filesystem->exists($source_path)):
                    if ($wp_filesystem->move($source_path, $target_path)) return TRUE;
                endif;
            endif;
        endif;
        
        $this->errors[] = __('Could not upload file.', 'chld_thm_cfg');        
    }
    function export_zip() {
        if (($child = $this->css->get_prop('child')) 
            && ($dir = $this->css->is_file_ok(dirname($this->css->get_child_target()), 'search'))
            && ($version = preg_replace("%[^\w\.\-]%", '', $this->css->get_prop('version')))):
            // use php system upload dir to store temp files so that we can use pclzip
            $tmpdir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
            $file = trailingslashit($tmpdir) . $child . '-' . $version . '.zip';
            mbstring_binary_safe_encoding();

            require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

            $archive = new PclZip($file);
            if ($archive->create($dir, PCLZIP_OPT_REMOVE_PATH, dirname($dir)) == 0) return FALSE;
        	reset_mbstring_encoding();
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Length: ' . filesize($file) );
            header( 'Content-Disposition: attachment; filename=' . basename($file) );
            header( 'Expires: 0');
            header( 'Cache-Control: must-revalidate');
            header( 'Pragma: public');
            readfile($file);
            unlink($file);
            die();
        endif;
    }
        
    /*
     *
     */
    function verify_creds($args = array()) {
        $this->fs_prompt = $this->fs = FALSE;
        //fs prompt does not support arrays as post data - serialize arrays
        $this->serialize_postarrays();
        // generate callback url
        $url = wp_nonce_url('tools.php?page=' . $this->menuName, 'ctc_update', '_wpnonce');
        // buffer output so we can process prior to http header
        ob_start();
        if ($creds = request_filesystem_credentials($url, '', FALSE, FALSE, $args)):
            // check filesystem permission if direct or ftp creds exist
            if ( WP_Filesystem($creds) )
                // login ok
                $this->fs = TRUE;
            else
                // incorrect credentials, get form with error flag
                $creds = request_filesystem_credentials($url, '', TRUE, FALSE, $args);
        else:
            // no credentials, initialize unpriveledged filesystem object
            WP_Filesystem();
        endif;
        // if form was generated, store it
        $this->fs_prompt = ob_get_contents();
        // now we can read/write if fs is TRUE otherwise fs_prompt will contain form
        ob_end_clean();
         //fs prompt does not support arrays as post data - unserialize arrays
        $this->unserialize_postarrays();
   }
    
    /*
     * convert 'direct' filepath into wp_filesystem filepath
     */
    function fspath($file){
        if (! $this->fs) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        if (is_dir($file)):
            $dir = $file;
            $base = '';
        else:
            $dir = dirname($file);
            $base = basename($file);
        endif;
        $fsdir = $wp_filesystem->find_folder($dir);
        return trailingslashit($fsdir) . $base;
    }
    
    function writable_notice() {
?>    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator is unable to write to the stylesheet. This can be resolved using one of the following options:<ol>', 'chld_thm_cfg');
        if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match('%unix%i',$_SERVER['SERVER_SOFTWARE'])):
            _e('<li>Temporarily make the stylesheet writable by clicking the button below. You should change this back when you are finished editing for security by clicking "Make read-only" under the "Files" tab.</li>', 'chld_thm_cfg');
?><form action="" method="post"><?php wp_nonce_field( 'ctc_update' ); ?>
<input name="ctc_set_writable" class="button" type="submit" value="<?php _e('Temporarily make stylesheet writable', 'chld_thm_cfg'); ?>"/></form><?php   endif;
        _e('<li><a target="_blank"  href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants" title="Editin wp-config.php">Add your FTP/SSH credentials to the WordPress config file</a>.</li>', 'chld_thm_cfg');
        if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match('%iis%i',$_SERVER['SERVER_SOFTWARE']))
            _e('<li><a target="_blank" href="http://technet.microsoft.com/en-us/library/cc771170" title="Setting Application Pool Identity">Assign WordPress to an application pool that has write permissions</a> (Windows IIS systems).</li>', 'chld_thm_cfg');
        _e('<li><a target="_blank" href="http://codex.wordpress.org/Changing_File_Permissions" title="Changing File Permissions">Set the stylesheet write permissions on the server manually</a> (not recommended).</li>', 'chld_thm_cfg');
        if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match('%unix%i',$_SERVER['SERVER_SOFTWARE']))
            _e('<li>Run PHP under Apache with suEXEC (contact your web host).</li>', 'chld_thm_cfg') ?>
        </ol></p>
</div>
    <?php
    }
    function owner_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( 'This Child Theme is not owned by your website account. It may have been created by a prior version of this plugin or by another program. Moving forward, it must be owned by your website account to make changes. Child Theme Configurator will attempt to correct this when you click the button below.', 'chld_thm_cfg') ?></p>
<form action="" method="post"><?php wp_nonce_field( 'ctc_update' ); ?>
<input name="ctc_reset_permission" class="button" type="submit" value="<?php _e('Correct Child Theme Permissions', 'chld_thm_cfg'); ?>"/></form>    </div>
    <?php
    }

    // back compatibility function for plugins extension
    function write_addl_files($chld_thm_cfg) {
        global $chld_thm_cfg_plugins;
        if (!is_object($chld_thm_cfg_plugins) || !$chld_thm_cfg->fs) return FALSE;
        $configtype = $chld_thm_cfg->css->get_prop('configtype');
        if ('theme' == $configtype || !($def = $chld_thm_cfg_plugins->defs->get_def($configtype))) return FALSE;
        $child = trailingslashit($chld_thm_cfg->css->get_prop('child'));
        if (isset($def['addl']) && is_array($def['addl']) && count($def['addl'])):
            foreach ($def['addl'] as $path => $type):
            
                // sanitize the crap out of the target data -- it will be used to create paths
                $path = wp_normalize_path(preg_replace("%[^\w\\//\-]%", '', sanitize_text_field($child . $path)));
                if (('dir' == $type && FALSE === $chld_thm_cfg->verify_child_dir($path))
                    || ('dir' != $type && FALSE === $chld_thm_cfg->write_child_file($path, ''))):
                    $chld_thm_cfg->errors[] = 
                        __('Your theme directories are not writable.', 'chld_thm_cfg_plugins');
                endif;
            endforeach;
        endif;
        // write main def file
        if (isset($def['target'])):
            $path = wp_normalize_path(preg_replace("%[^\w\\//\-\.]%", '', sanitize_text_field($def['target']))); //$child . 
            if (FALSE === $chld_thm_cfg->write_child_file($path, '')):
                $chld_thm_cfg->errors[] = 
                    __('Your stylesheet is not writable.', 'chld_thm_cfg_plugins');
                return FALSE;
            endif;
        endif;        
    }
}