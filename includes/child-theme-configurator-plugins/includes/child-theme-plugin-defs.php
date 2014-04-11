<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/* 
 * This file and all accompanying files (C) 2014 Lilaea Media LLC except where noted. See license for details.
 */
 
class ChildThemePluginDefs {
    
    var $dir;
    var $options;
    var $plugins;
    var $optionName;
    var $excl;
    
    function __construct($dir) {
        $this->dir          = $dir;
        $this->optionName   = 'chld_thm_cfg_plugins_options';
        $this->options      = get_option($this->optionName);
        $this->get_active_plugins();
        include_once($this->dir . '/includes/child-theme-plugin-exclusions.php');
        $this->excl = new ChildThemePluginExclusions();
        // filter hooks
        add_filter('chld_thm_cfg_parnt',        array(&$this, 'get_parent'), 10, 2);
        add_filter('chld_thm_cfg_child',        array(&$this, 'get_child'), 10, 2);
        add_filter('chld_thm_cfg_source_uri',   array(&$this, 'get_source_uri'), 10, 2);
        add_filter('chld_thm_cfg_target',       array(&$this, 'get_target'), 10, 2);
        add_filter('chld_thm_cfg_target_uri',   array(&$this, 'get_target_uri'), 10, 2);
        add_filter('chld_thm_cfg_css_header',   array(&$this, 'get_css_header'), 10, 2);
        add_filter('chld_thm_cfg_update_msg',   array(&$this, 'get_update_msg'), 10, 2);

        // action hooks
        add_action('chld_thm_cfg_tabs',         array(&$this, 'render_definition_tab'), 10, 4);
        add_action('chld_thm_cfg_panels',       array(&$this, 'render_definition_panel'), 10, 4);
        add_action('chld_thm_cfg_forms',        array(&$this, 'process_definition_form'), 10, 2);
        add_action('chld_thm_cfg_forms',        array(&$this, 'process_delete_definition_form'), 10, 2);
        add_action('chld_thm_cfg_forms',        array(&$this, 'process_update_key_form'), 10, 2);
        add_action('chld_thm_cfg_controls',     array(&$this, 'render_configtype_controls'), 10, 2);
        add_action('chld_thm_cfg_addl_files',   array(&$this, 'write_addl_files'), 10, 2);
        add_action('chld_thm_cfg_addl_options', array(&$this, 'save_addl_options'), 10, 2);
        $this->check_plugin_update();
    }
    
    function get_active_defs($parent) {
        $active = array();
        if (isset($this->options['defs']) && is_array($this->options['defs'])):
            foreach ($this->options['defs'] as $key => $def):
                if ('plugin' == $def['type']):
                    $handle = $def['parentdir'] . '/' . $def['pluginfile'];
                    if (is_plugin_active($handle)):
                        $active[$key] = $def;
                    endif;
                elseif ('theme' == $def['type'] && $def['parentdir'] == $parent):
                    $active[$key] = $def;
                endif;
            endforeach;
        endif;
        return $active;
    }
    
    function get_def($configtype) {
        if (isset($this->options['defs'][$configtype])) return $this->options['defs'][$configtype];
        return false;
    }
    
    function get_active_plugins() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $active = array();
        foreach(get_plugins() as $handle => $data):
            if (is_plugin_active($handle)) $active[$handle] = $data['Name'];
        endforeach;
        $this->plugins = $active;
    }
    
    function get_css_header($header, $css) {
        $configtype = $css->get_prop('configtype');
        if ('theme' == $configtype || !($def = $this->get_def($configtype))) return $header;
        return '/*' . LF
            . 'Name: ' . $def['label'] . ' Custom Styles' . LF
            . 'Parent File: ' . $def['parentdir'] . '/' . $def['source'] . LF
            . 'Author: ' . $css->get_prop('author'). LF
            . 'Version: ' . $css->get_prop('version') . LF
            . 'Updated: ' . current_time('mysql') . LF
            . '*/' . LF
            . '@charset "UTF-8";' . LF;
    }
        
    function get_parent($uri, $css) {
        return $this->get_uri($uri, $css, 'source');
    }
    
    function get_child($uri, $css) {
        return $this->get_uri($uri, $css, 'target');
    }
        
    function get_source_uri($uri, $css) {
        return $this->get_uri($uri, $css, 'source_uri');
    }
        
    function get_target($uri, $css) {
        return $this->get_uri($uri, $css, 'target');
    }

    function get_target_uri($uri, $css) {
        return $this->get_uri($uri, $css, 'target_uri');
    }
        
    function get_uri($uri, $css, $type) {
        $configtype = $css->get_prop('configtype');
        if ('theme' == $configtype || !($def = $this->get_def($configtype))) return $uri;
        $child = $css->get_prop('child');
        $parnt = $css->get_prop('parnt');
        $contentdir = ('plugin' == $def['type'] ? 
            dirname($this->dir) . '/' . $def['parentdir'] : 
            get_theme_root() . '/' . $parnt);
        $contenturi = ('plugin' == $def['type'] ? 
            plugin_dir_url($this->dir) . '/' . $def['parentdir'] : 
            get_theme_root_uri() . '/' . $parnt);
        switch ($type):
            case 'source':
                return $contentdir . '/' . $def['source'];
            case 'source_uri':
                return $contenturi . '/' . $def['source'];
            case 'target':
                return get_theme_root() . '/' . $child . '/' . $def['target'];
            case 'target_uri':
                return get_theme_root_uri() . '/' . $child . '/' . $def['target'];
            default:
                return $uri;
        endswitch;
    }
        
    function get_update_msg($msg, $chld_thm_cfg) {
        if (isset($_GET['updated']) && 4 == $_GET['updated']) 
            return __('Update Key saved successfully.', 'chld_thm_cfg_plugins');
        if (isset($_GET['updated']) && 3 == $_GET['updated']) 
            return __('CSS Source Entry has been removed successfully.', 'chld_thm_cfg_plugins');
        if (isset($_GET['updated']) && 2 == $_GET['updated']) 
            return __('CSS Source Entry has been generated successfully.', 'chld_thm_cfg_plugins');
        $configtype = $chld_thm_cfg->css->get_prop('configtype');
        if ('theme' == $configtype || !($def = $this->get_def($configtype))) return $msg;
        return sprintf(__('Custom Stylesheet for %s has been generated successfully.', 'chld_thm_cfg_plugins'), 
            $def['label']);
    }

    function get_css_files($parent){
        $candidates = array();
        foreach (array_keys($this->plugins) as $handle):
            $parentdir = dirname($handle);
            $dir = WP_PLUGIN_DIR . '/' . $parentdir;
            foreach ($this->recurse_directory($dir) as $filepath):
                list($base, $file) = explode(trailingslashit($parentdir), $filepath);
                if ($fileoption = $this->munge_file_option('plugin', $handle, $file)) 
                    $candidates[$fileoption[0]] = $fileoption[1];
            endforeach;
        endforeach;
        $rootdir = get_theme_root();
        $dir =  $rootdir . '/' . $parent;
        foreach ($this->recurse_directory($dir) as $filepath):
            list($base, $file) = explode(trailingslashit($parent), $filepath);
            if ('style.css' != $file && ($fileoption = $this->munge_file_option('theme', $parent, $file))):
                $candidates[$fileoption[0]] = $fileoption[1];
            endif;
        endforeach;
        return $candidates;
    }
    
    function set_options() {
        update_option($this->optionName, $this->options);
    }
    
    function write_addl_files($chld_thm_cfg) {
        $configtype = $chld_thm_cfg->css->get_prop('configtype');
        if ('theme' == $configtype || !($def = $this->get_def($configtype))) return false;
        $child = $chld_thm_cfg->css->get_prop('child');
        if (isset($def['addl']) && is_array($def['addl']) && count($def['addl'])):
            foreach ($def['addl'] as $path => $type):
                // sanitize the crap out of the target data -- it will be used to create paths
                $path = implode('/', preg_split("%[\\\/]+%", preg_replace("%[^\w\\//\-]%", '', sanitize_text_field($path))));
                $filepath = get_theme_root() . '/' . $child . '/' . $path;
                if (!is_writable(dirname($filepath))):
                    $chld_thm_cfg->errors[] = 
                        __('Your theme directories are not writable. Please adjust permissions and try again.', 'chld_thm_cfg_plugins');
                    break;
                elseif (! file_exists($filepath)):
                    if ('dir' == $type) @mkdir($filepath, 0755);
                    else @file_put_contents($filepath, '');
                endif;
            endforeach;
        endif;
    }
        
    function save_addl_options($chld_thm_cfg) {
        $configtype = $chld_thm_cfg->css->get_prop('configtype');
        $child      = $chld_thm_cfg->css->get_prop('child');
        if ($def = $this->get_def($configtype)):
            if ($def['enqueue']) $this->options['enqueues'][$child][$def['slug']] = $def['target'];
        endif;
        $this->set_options();
    }
        
    function process_definition_form($chld_thm_cfg) {
        if (isset($_POST['ctpc_generate_definition']) && $chld_thm_cfg->validate_post('ctpc_definition')):
            $source         = sanitize_text_field($_POST['ctpc_definition_source']);
            list($type, $handle, $file) = explode('::', $source);
            
            // bail if handle is not active plugin (also acts as sanitizer)
            if ($fileinfo = $this->munge_file_info($type, $handle, $file)):
                $this->options['defs'][$fileinfo['key']] = array(
                    'label'         => $fileinfo['label'],
                    'slug'          => $fileinfo['slug'],
                    'enqueue'       => 1,
                    'type'          => $type,
                    'parentdir'     => $fileinfo['parentdir'],
                    'pluginfile'    => $fileinfo['pluginfile'],
                    'source'        => $file,
                    'target'        => 'css/' . $fileinfo['targetfile'],
                    'addl'          => array('css' => 'dir'),
                );
            endif;
            $chld_thm_cfg->css->set_prop('configtype', $fileinfo['key']);
            $this->set_options();
            $chld_thm_cfg->update_redirect('2&tab=new_css_source');
        endif;
    }
    
    function process_delete_definition_form($chld_thm_cfg) {
        $child = $chld_thm_cfg->css->get_prop('child');
        if (isset($_POST['ctpc_delete_definition']) && $chld_thm_cfg->validate_post('ctpc_delete_definition')):
            $source         = sanitize_text_field($_POST['ctpc_delete_configtype']);
            if ($def = $this->get_def($source)):
                if (is_file(get_stylesheet_directory() . '/' . $def['target'])):
                    @unlink(get_stylesheet_directory() . '/' . $def['target']);
                endif;
                unset($this->options['enqueues'][$child][$def['slug']]);
            endif;
            unset($this->options['defs'][$source]);
            $this->set_options();
            $chld_thm_cfg->update_redirect('3&tab=new_css_source');
        endif;
    }
    
    function process_update_key_form($chld_thm_cfg) {
        if (isset($_POST['ctpc_save_update_key']) && $chld_thm_cfg->validate_post('ctc_update_key')):
            $sanitized_update_key = preg_replace("/\W/", '', sanitize_text_field($_POST['ctpc_update_key']));
            //if (empty($_POST['ctpc_update_key']) || strlen($_POST['ctpc_update_key']) < 10):
            //    $chld_thm_cfg->errors[] =
            //        __('Please enter a valid Update Key.', 'chld_thm_cfg_plugins');
            //    return false;
            //else:
                $this->options['update_key'] = $sanitized_update_key;
                $this->set_options();
                $chld_thm_cfg->update_redirect('4&tab=new_css_source');
            //endif;
        endif;
    }
    
    function munge_file_info($type, $handle, $file) {
        if ('plugin' == $type):
            if (! in_array($handle, array_keys($this->plugins))) return false;
            list($parentdir,$pluginfile)    = explode('/', $handle, 2);
            $parentname = $this->plugins[$handle];
        else:
            $parentdir = $handle;
            $parenttheme = wp_get_theme($parentdir);
            $parentname = $parenttheme['Name'];
            $pluginfile = '';
        endif;
        $key            = $parentdir . '/' . $file;
        $slug           = strtolower(preg_replace("/\W/", '-', preg_replace("/\.css$/", '', $key)));
        $targetfile     = $slug . '.css';
        $targetname     = preg_replace("/\.css$/", '', basename($file));
        $label          = $parentname; // . ': ' . ucwords(preg_replace("/\W/", ' ', $targetname));
        return array(
            'parentdir'     => $parentdir,
            'pluginfile'    => $pluginfile,
            'parentname'    => $parentname,
            'targetname'    => $targetname,
            'targetfile'    => $targetfile,
            'source'        => $file,
            'key'           => $key,
            'label'         => $label,
            'slug'          => $slug,
        );
    }
    
    function munge_file_option($type, $handle, $file) {
        // create nice label from filepath
        // munge option value (type::plugin handle or parent::source path)
        // skip if already in defs
        // skip admin stylesheets
        foreach (apply_filters('chld_thm_cfg_backend', array()) as $regex) if (preg_match($regex, $file)) return false;
        $fileinfo = $this->munge_file_info($type, $handle, $file);
        if (!empty($this->options['defs']) && array_key_exists($fileinfo['key'], $this->options['defs'])) return false;
        return array($type . '::' . $handle . '::' . $fileinfo['source'], $fileinfo['label'] . ' (' . $fileinfo['source'] . ')');
        
    }
    
    function recurse_directory($rootdir, $ext = 'css') {
        $files = array();
        $dirs = array($rootdir);
        while(count($dirs)):
            $dir = array_shift($dirs);
            if ($handle = opendir($dir)):
                while (false !== ($file = readdir($handle))):
                    if (preg_match("/^\./", $file)) continue;
                    $filepath  = $dir . '/' . $file;
                    if (is_dir($filepath)):
                        array_unshift($dirs, $filepath);
                    elseif (is_file($filepath) && preg_match("/\.".$ext."$/", $filepath)):
                        $files[] = $filepath;
                    endif;
                endwhile;
                closedir($handle);
            endif;
        endwhile;
        return $files;
    }
    
    /***
     * future release
     ***
     function add_intelliwidget_definition() {
        if (empty($this->options['defs'])):
            $this->options['defs']['intelliwidget-per-page-featured-posts-and-menus/templates/intelliwidget.css'] = array(
                'label'         => 'IntelliWidget',
                'slug'          => 'intelliwidget',
                'enqueue'       => 0,
                'type'          => 'plugin',
                'parentdir'     => 'intelliwidget-per-page-featured-posts-and-menus',
                'pluginfile'    => 'intelliwidget.php',
                'source'        => 'templates/intelliwidget.css',
                'target'        => 'intelliwidget/intelliwidget.css',
                'addl'          => array('intelliwidget' => 'dir'),
            );
            $this->set_options();
        endif;
    }
    */
    
    function render_definition_tab($chld_thm_cfg, $active_tab = NULL, $hidechild = '') {      
?> <a id="new_css_source" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=new_css_source" 
                    class="nav-tab<?php echo 'new_css_source' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
<?php _e('Extended Settings', 'chld_thm_cfg_plugins'); ?>
</a>
<?php
    }

    function render_configtype_controls($chld_thm_cfg, $field = 'ctc_configtype') {
        $configtype = $chld_thm_cfg->css->get_prop('configtype');
        $parent = $chld_thm_cfg->css->get_prop('parnt');
        $list = 'ctc_configtype' == $field ? array('theme' => __('Parent Theme Stylesheet', 'chld_thm_cfg_plugins')) : array();
        foreach (apply_filters('chld_thm_cfg_plugins_defs', $this->get_active_defs($parent)) as $handle => $def):
            $list[$handle] = $def['label'] . ' (' . $def['source'] . ')';
        endforeach;
        if (count($list) > 1 || ('ctc_configtype' != $field && count($list) > 0)): ?>
<div class="ctc-input-row clearfix" id="input_row_parnt">
  <div class="ctc-input-cell">
    <label>
      <?php if ('ctpc_delete_configtype' == $field): 
                _e('Source CSS to Deactivate', 'chld_thm_cfg_plugins'); 
            else:
                _e('Source CSS to Configure', 'chld_thm_cfg_plugins'); 
            endif;?>
    </label>
  </div>
  <div class="ctc-input-cell">
    <select class="ctc-select" id="<?php echo $field; ?>" name="<?php echo $field; ?>">
      <?php foreach ($list as $value => $label): ?>
      <option value="<?php echo $value;?>" <?php selected($value, $configtype); ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php     if ('ctpc_delete_configtype' == $field): ?>
  <div class="ctc-input-cell">
    <input class="ctc_submit button button-primary" id="ctpc_delete_definition" name="ctpc_delete_definition"  type="submit" 
        value="<?php _e('Deactivate', 'chld_thm_cfg_plugins'); ?>" disabled />
  </div>
  <?php     endif; ?>
</div>
<?php
        endif;
    }
   
    function render_definition_panel($chld_thm_cfg, $active_tab = NULL, $hidechild = '') {
        $parent    = $chld_thm_cfg->css->get_prop('parnt');
        $css_files = $this->get_css_files($parent);
		?>
<div id="new_css_source_panel" class="ctc-option-panel<?php echo 'new_css_source' == $active_tab ? 
        ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
  <form id="ctc_def_form" method="post" action="">
    <?php wp_nonce_field( 'ctpc_definition' ); ?>
    <div class="ctc-input-row clearfix" id="input_row_parnt">
      <div class="ctc-input-cell">
        <label>
          <?php _e('Source CSS to Activate', 'chld_thm_cfg_plugins'); ?>
        </label>
      </div>
      <div class="ctc-input-cell">
        <select class="ctc-select" id="ctpc_definition_source" name="ctpc_definition_source">
          <?php foreach ($css_files as $value => $label): ?>
          <option value="<?php echo $value; ?>"><?php echo esc_attr($label); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctpc_generate_definition" name="ctpc_generate_definition"  type="submit" 
                value="<?php _e('Activate', 'chld_thm_cfg_plugins'); ?>" disabled />
      </div>
    </div>
  </form>
  <form id="ctc_delete_def_form" method="post" action="">
    <?php wp_nonce_field( 'ctpc_delete_definition' ); ?>
    <?php $this->render_configtype_controls($chld_thm_cfg, 'ctpc_delete_configtype'); ?>
  </form>
  <form id="ctc_update_key_form" method="post" action="">
    <?php 
	wp_nonce_field( 'ctc_update_key' ); ?>
    <div class="ctc-input-row clearfix" id="input_row_child_name">
      <div class="ctc-input-cell">
        <label>
          <?php _e('Enter Update Key', 'chld_thm_cfg_plugins'); ?>
        </label>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_text" id="ctpc_update_key" name="ctpc_update_key"  type="text" 
            value="<?php echo esc_attr(isset($this->options['update_key']) ? $this->options['update_key'] :''); ?>" 
                placeholder="<?php _e('Update Key', 'chld_thm_cfg_plugins'); ?>" autocomplete="off" />
      </div>
      <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctpc_save_update_key" name="ctpc_save_update_key"  type="submit" 
                value="<?php _e('Save', 'chld_thm_cfg_plugins'); ?>" disabled />
      </div>
    </div>
  </form>
</div>
<?php 
    }
    
    function check_plugin_update() {
        if (isset($this->options['update_key'])):
            include_once($this->dir . '/includes/plugin-update-checker.php');
            new PluginUpdateChecker(
                'http://www.lilaeamedia.com/updates/update.php?product=child-theme-configurator-plugins&key=' . $this->options['update_key'],
                __FILE__
            );
        endif;
    }
    
}