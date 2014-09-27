<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/*
    Class: Child_Theme_Configurator_UI
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Handles the plugin User Interface
    Version: 1.5.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/
class Child_Theme_Configurator_UI {
    var $swatch_text;
    var $themes;
    var $extLink;
    
    function __construct() {
        $this->swatch_text  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';  
        $this->extLink      = '<a href="http://www.lilaeamedia.com/total-wordpress-customization-pagecells-responsive-theme-framework/" target="_blank" title="' . __('Total WordPress Customization with PageCells Responsive Theme Framework', 'chld_thm_cfg') . '" style="float:right"><img src="http://www.lilaeamedia.com/images/logos/pagecells-310.png" height="40" width="310" alt="PageCells Responsive Framework" /></a>';
    }
    
    function render_options() { 
        global $chld_thm_cfg; 
        $css        = $chld_thm_cfg->css;
        $themes     = $chld_thm_cfg->themes;
        $parent     = isset($_GET['ctc_parent']) ? sanitize_text_field($_GET['ctc_parent']) : $css->get_prop('parnt');
        $child      = $css->get_prop('child');
        $configtype = $css->get_prop('configtype');
        $hidechild  = (count($themes['child']) ? '' : 'style="display:none"');
        $imports    = $css->get_prop('imports');
        $id         = 0;
        $chld_thm_cfg->fs_method = get_filesystem_method();
        add_thickbox();    ?>

<div class="wrap">
  <div id="icon-tools" class="icon32"></div>
  <?php echo $this->extLink; ?>
  <h2><?php echo $chld_thm_cfg->pluginName; ?> v.<?php echo CHLD_THM_CFG_VERSION;?></h2>
  <?php if ('POST' == $_SERVER['REQUEST_METHOD'] && !$chld_thm_cfg->fs):
        echo $chld_thm_cfg->fs_prompt;
    else: ?>
  <div id="ctc_error_notice">
    <?php $this->settings_errors(); ?>
  </div>
  <?php  
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'parent_child_options';
            ?>
  <h2 class="nav-tab-wrapper"><a id="parent_child_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=parent_child_options" 
                    class="nav-tab<?php echo 'parent_child_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('Parent/Child', 'chld_thm_cfg'); ?>
    </a><!----><a id="query_selector_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=query_selector_options" 
                    class="nav-tab<?php echo 'query_selector_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('Query/Selector', 'chld_thm_cfg'); ?>
    </a><!----><a id="rule_value_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=rule_value_options" 
                    class="nav-tab<?php echo 'rule_value_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('Rule/Value', 'chld_thm_cfg'); ?>
    </a><!----><a id="import_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=import_options" 
                    class="nav-tab<?php echo 'import_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('@import', 'chld_thm_cfg'); ?>
    </a><!----><a id="view_child_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=view_child_options" 
                    class="nav-tab<?php echo 'view_child_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('Child CSS', 'chld_thm_cfg'); ?>
    </a><!----><a id="view_parnt_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=view_parnt_options" 
                    class="nav-tab<?php echo 'view_parnt_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('Parent CSS', 'chld_thm_cfg'); ?>
    </a>
    <?php 
    if ('' == $hidechild): // && (empty($configtype) || 'theme' == $configtype)): 
    ?>
    <a id="file_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options" 
                    class="nav-tab<?php echo 'file_options' == $active_tab ? ' nav-tab-active' : ''; ?>" <?php echo $hidechild; ?>>
    <?php _e('Files', 'chld_thm_cfg'); ?>
    </a>
    <?php 
    endif; 
 
    do_action('chld_thm_cfg_tabs', $chld_thm_cfg, $active_tab, $hidechild);?>
    <i id="ctc_status_preview"></i></h2>
  <div class="ctc-option-panel-container">
    <div id="parent_child_options_panel" class="ctc-option-panel<?php echo 'parent_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
      <form id="ctc_load_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>">
        <?php wp_nonce_field( 'ctc_update' ); ?>
        <div class="ctc-input-row clearfix" id="input_row_parnt">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Parent Theme', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <select class="ctc-select" id="ctc_theme_parnt" name="ctc_theme_parnt">
              <?php echo $chld_thm_cfg->render_menu('parnt', $parent); ?>
            </select>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Child Theme', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc-radio" id="ctc_child_type_new" name="ctc_child_type" type="radio" value="new" 
            <?php echo (!empty($hidechild) ? 'checked' : ''); ?>
            <?php echo $hidechild;?> />
            <label for="ctc_child_type_new">
              <?php _e('Create New Child Theme', 'chld_thm_cfg'); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <input class="ctc-radio" id="ctc_child_type_existing" name="ctc_child_type"  type="radio" value="existing" 
            <?php echo (empty($hidechild) ? 'checked' : ''); ?>
            <?php echo $hidechild; ?>/>
            &nbsp;
            <label for="ctc_child_type_existing" <?php echo $hidechild;?>>
              <?php _e('Use Existing Child Theme', 'chld_thm_cfg'); ?>
            </label>
          </div>
          <div class="ctc-input-cell" style="clear:both"> <strong>&nbsp;</strong> </div>
          <div class="ctc-input-cell" >
            <input class="ctc_text" id="ctc_child_template" name="ctc_child_template" type="text" placeholder="<?php _e('Theme Slug', 'chld_thm_cfg'); ?>" autocomplete="off"/>
          </div>
          <div class="ctc-input-cell">
            <select class="ctc-select" id="ctc_theme_child" name="ctc_theme_child" <?php echo $hidechild; ?>>
              <?php echo $chld_thm_cfg->render_menu('child', $child); ?>
            </select>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_name">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Child Theme Name', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_child_name" name="ctc_child_name"  type="text" 
                value="<?php echo esc_attr($css->get_prop('child_name')); ?>" placeholder="<?php _e('Theme Name', 'chld_thm_cfg'); ?>" autocomplete="off" />
          </div>
        </div>
        <?php if ('' == $hidechild) do_action('chld_thm_cfg_controls', $chld_thm_cfg); ?>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Author', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_child_author" name="ctc_child_author" type="text" 
                value="<?php echo esc_attr($css->get_prop('author')); ?>" placeholder="<?php _e('Author', 'chld_thm_cfg'); ?>" autocomplete="off" />
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Version', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_child_version" name="ctc_child_version" type="text" 
                value="<?php echo esc_attr($css->get_prop('version')); ?>" placeholder="<?php _e('Version', 'chld_thm_cfg'); ?>" autocomplete="off" />
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Backup Stylesheet', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc_checkbox" id="ctc_backup" name="ctc_backup" type="checkbox" 
                value="1" />
          </div>
        </div>
        <?php if (empty($configtype) || 'theme' == $configtype): 
        $stylesheets = $chld_thm_cfg->get_additional_css($parent);
        if (count($stylesheets)):?>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell" id="ctc_additional_css_label"> <strong> <span><?php _e('Parse additional stylesheets:', 'chld_thm_cfg'); ?></span> </strong>
            <p><?php _e('(click to toggle)', 'chld_thm_cfg'); ?></p>
          </div>
          <div class="ctc-input-cell-wide" id="ctc_additional_css_files" style="display:none">
            <p style="margin-top:0">
              <?php _e('Select only the stylesheets you wish to customize to reduce overhead.', 'chld_thm_cfg'); ?>
            </p>
            <?php 
            foreach ($stylesheets as $stylesheet): ?>
            <div class="ctc-input-cell">
              <label>
                <input class="ctc_checkbox" name="ctc_additional_css[]" type="checkbox" 
                value="<?php echo $stylesheet; ?>" />
                <?php echo $stylesheet; ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; endif; ?>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
          <div class="ctc-input-cell">
            <input class="ctc_submit button button-primary" id="ctc_load_styles" name="ctc_load_styles"  type="submit" 
                value="<?php _e('Generate Child Theme Files', 'chld_thm_cfg'); ?>" disabled />
          </div>
        </div>
      </form>
    </div>
    <div id="rule_value_options_panel" 
        class="ctc-option-panel<?php echo 'rule_value_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
      <form id="ctc_rule_value_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=rule_value_options">
        <?php wp_nonce_field( 'ctc_update' ); ?>
        <div class="ctc-input-row clearfix" id="ctc_input_row_rule_menu">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Rule', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell" id="ctc_rule_menu_selected">&nbsp;</div>
          <div id="ctc_status_rule_val"></div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_rule_menu"/>
              <div id="ctc_status_rule"></div>
            </div>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="ctc_input_row_rule_header" style="display:none">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Value', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Sample', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Selectors', 'chld_thm_cfg'); ?>
            </strong> </div>
        </div>
        <div class="ctc-rule-value-input-container clearfix" id="ctc_rule_value_inputs" style="display:none"> </div>
      </form>
    </div>
    <div id="query_selector_options_panel" 
        class="ctc-option-panel<?php echo 'query_selector_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
      <form id="ctc_query_selector_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=query_selector_options">
        <div class="ctc-input-row clearfix" id="input_row_query">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Query', 'chld_thm_cfg'); ?>
            </strong> </div>
          <div class="ctc-input-cell" id="ctc_sel_ovrd_query_selected">&nbsp;</div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_sel_ovrd_query" />
            </div>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_selector">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Selector', 'chld_thm_cfg'); ?>
            </strong> <a href="#" class="ctc-rewrite-toggle"></a></div>
          <div class="ctc-input-cell" id="ctc_sel_ovrd_selector_selected">&nbsp;</div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_sel_ovrd_selector" />
              <div id="ctc_status_sel_ndx"></div>
            </div>
          </div>
        </div>
        <div class="ctc-selector-row clearfix" id="ctc_sel_ovrd_rule_inputs_container" style="display:none">
          <div class="ctc-input-row clearfix">
            <div class="ctc-input-cell"><strong>
              <?php _e('Sample', 'chld_thm_cfg'); ?>
              </strong></div>
            <div class="ctc-input-cell clearfix" style="max-height:150px;overflow:hidden">
              <div class="ctc-swatch" id="ctc_child_all_0_swatch"><?php echo $this->swatch_text; ?></div>
            </div>
            <div id="ctc_status_sel_val"></div>
            <div class="ctc-input-cell ctc-button-cell" id="ctc_save_query_selector_cell">
              <input type="button" class="button button-primary ctc-save-input" id="ctc_save_query_selector" 
            name="ctc_save_query_selector" value="<?php _e('Save', 'chld_thm_cfg'); ?>" disabled />
              <input type="hidden" id="ctc_sel_ovrd_qsid" 
            name="ctc_sel_ovrd_qsid" value="" />
            </div>
          </div>
          <div class="ctc-input-row clearfix" id="ctc_sel_ovrd_rule_header" style="display:none">
            <div class="ctc-input-cell"> <strong>
              <?php _e('Rule', 'chld_thm_cfg'); ?>
              </strong> </div>
            <div class="ctc-input-cell"> <strong>
              <?php _e('Parent Value', 'chld_thm_cfg'); ?>
              </strong> </div>
            <div class="ctc-input-cell"> <strong>
              <?php _e('Child Value', 'chld_thm_cfg'); ?>
              </strong> </div>
          </div>
          <div id="ctc_sel_ovrd_rule_inputs" style="display:none"> </div>
          <div class="ctc-input-row clearfix" id="ctc_sel_ovrd_new_rule" style="display:none">
            <div class="ctc-input-cell"> <strong>
              <?php _e('New Rule', 'chld_thm_cfg'); ?>
              </strong> </div>
            <div class="ctc-input-cell">
              <div class="ui-widget">
                <input id="ctc_new_rule_menu" />
              </div>
            </div>
          </div>
          <div class="ctc-input-row clearfix" id="input_row_selector">
            <div class="ctc-input-cell"> <strong>
              <?php _e('Order', 'chld_thm_cfg'); ?>
              </strong> </div>
            <div class="ctc-input-cell" id="ctc_child_load_order_container">&nbsp;</div>
          </div>
        </div>
        <div class="ctc-selector-row clearfix" id="ctc_new_selector_row">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Raw CSS', 'chld_thm_cfg'); ?>
            </strong>
            <div class="ctc-textarea-button-cell" id="ctc_save_query_selector_cell">
              <input type="button" class="button ctc-save-input" id="ctc_save_new_selectors" 
            name="ctc_save_new_selectors" value="<?php _e('Save', 'chld_thm_cfg'); ?>"  disabled />
            </div>
          </div>
          <div class="ctc-input-cell-wide">
            <textarea id="ctc_new_selectors" name="ctc_new_selectors" wrap="off"></textarea>
          </div>
        </div>
      </form>
    </div>
    <div id="import_options_panel" 
        class="ctc-option-panel<?php echo 'import_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
      <form id="ctc_import_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=import_options">
        <?php wp_nonce_field( 'ctc_update' ); ?>
        <div class="ctc-input-row clearfix" id="ctc_child_imports_row">
          <div class="ctc-input-cell"> <strong>
            <?php _e('@import Statements', 'chld_thm_cfg'); ?>
            </strong>
            <div class="ctc-textarea-button-cell" id="ctc_save_imports_cell">
              <input type="button" class="button ctc-save-input" id="ctc_save_imports" 
            name="ctc_save_imports" value="<?php _e('Save', 'chld_thm_cfg'); ?>"  disabled />
            </div>
          </div>
          <div class="ctc-input-cell-wide">
            <textarea id="ctc_child_imports" name="ctc_child_imports" wrap="off">
<?php if (!empty($imports)):
        foreach ($imports as $import):
            echo esc_textarea($import . ';' . LF);
        endforeach; endif;?>
</textarea>
          </div>
        </div>
      </form>
    </div>
    <div id="view_child_options_panel" 
        class="ctc-option-panel<?php echo 'view_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>> </div>
    <div id="view_parnt_options_panel" 
        class="ctc-option-panel<?php echo 'view_parnt_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>> </div>
    <?php if ('' == $hidechild): // && (empty($configtype) || 'theme' == $configtype)): ?>
    <div id="file_options_panel" 
        class="ctc-option-panel<?php echo 'file_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
      <?php $this->render_file_form('parnt'); ?>
      <?php $this->render_file_form('child'); ?>
      <?php $this->render_image_form(); ?>
      <div class="ctc-input-row clearfix" id="input_row_theme_image">
        <form id="ctc_theme_image_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options" enctype="multipart/form-data">
          <?php wp_nonce_field( 'ctc_update' ); ?>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Upload New Child Theme Image', 'chld_thm_cfg'); ?>
            </strong>
            <p class="howto">
              <?php _e('Theme images reside under the <code>images</code> directory in your child theme and are meant for stylesheet use only. Use the Media Library for content images.', 'chld_thm_cfg'); ?>
            </p>
          </div>
          <div class="ctc-input-cell-wide">
            <input type="file" id="ctc_theme_image" name="ctc_theme_image" value="" />
            <input class="ctc_submit button button-primary" id="ctc_theme_image_submit" 
                name="ctc_theme_image_submit"  type="submit" 
                value="<?php _e('Upload', 'chld_thm_cfg'); ?>" />
          </div>
        </form>
      </div>
      <?php if ($screenshot = $this->get_theme_screenshot()): ?>
      <div class="ctc-input-row clearfix" id="input_row_screenshot_view">
        <div class="ctc-input-cell"> <strong>
          <?php _e('Child Theme Screenshot', 'chld_thm_cfg'); ?>
          </strong> </div>
        <div class="ctc-input-cell-wide"> <a href="<?php echo $screenshot; ?>" class="thickbox"><img src="<?php echo $screenshot; ?>" height="150" width="200" style="max-height:150px;max-width:200px;width:auto;height:auto" /></a> </div>
      </div>
      <?php endif; ?>
      <div class="ctc-input-row clearfix" id="input_row_screenshot">
        <form id="ctc_screenshot_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options" enctype="multipart/form-data">
          <?php wp_nonce_field( 'ctc_update' ); ?>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Upload New Screenshot', 'chld_thm_cfg'); ?>
            </strong>
            <p class="howto">
              <?php _e('The theme screenshot should be a 4:3 ratio (e.g., 880px x 660px) JPG, PNG or GIF. It will be renamed <code>screenshot</code>.', 'chld_thm_cfg'); ?>
            </p>
          </div>
          <div class="ctc-input-cell-wide">
            <input type="file" id="ctc_theme_screenshot" name="ctc_theme_screenshot" value="" />
            <input class="ctc_submit button button-primary" id="ctc_theme_screenshot_submit" 
                name="ctc_theme_screenshot_submit"  type="submit" 
                value="<?php _e('Upload', 'chld_thm_cfg'); ?>" />
          </div>
        </form>
      </div>
      <div class="ctc-input-row clearfix" id="input_row_screenshot">
        <form id="ctc_export_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options">
          <?php wp_nonce_field( 'ctc_update' ); ?>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Export Child Theme as Zip Archive', 'chld_thm_cfg'); ?>
            </strong>
          </div>
          <div class="ctc-input-cell-wide">
            <input class="ctc_submit button button-primary" id="ctc_export_child_zip" 
                name="ctc_export_child_zip"  type="submit" 
                value="<?php _e('Export', 'chld_thm_cfg'); ?>" />
          </div>
        </form>
      </div>
      <?php if ('direct' != $chld_thm_cfg->fs_method): ?>
      <div class="ctc-input-row clearfix" id="input_row_permissions">
        <form id="ctc_permission_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options">
          <?php wp_nonce_field( 'ctc_update' ); ?>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Secure Child Theme', 'chld_thm_cfg'); ?>
            </strong>
            <p class="howto">
              <?php _e('Attempt to reset child theme permissions to user ownership and read-only access.', 'chld_thm_cfg'); ?>
            </p>
          </div>
          <div class="ctc-input-cell-wide">
            <input class="ctc_submit button button-primary" id="ctc_reset_permission" 
                name="ctc_reset_permission"  type="submit" 
                value="<?php _e('Make read-only', 'chld_thm_cfg'); ?>" />
          </div>
        </form>
      </div><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php do_action('chld_thm_cfg_panels', $chld_thm_cfg, $active_tab, $hidechild); ?>
  </div><?php
    endif;
?></div>
<style type="text/css">
.ctc-status-icon.success {
    display: block;
    float:right;
    position: relative;
    height: 16px;
    width: 16px;
    margin:4px;
    background:url(<?php echo admin_url( 'images/yes.png' ); ?>) no-repeat;
}
.ctc-status-icon.failure {
    display: block;
    float:right;
    position: relative;
    height: 16px;
    width: 16px;
    margin:4px;
    background:url(<?php echo admin_url( 'images/no.png') ; ?>) no-repeat;
}
</style>
<?php  
    } 
    
    function render_file_form($template = 'parnt') {
        global $chld_thm_cfg, $wp_filesystem; 
        if ($theme = $chld_thm_cfg->css->get_prop($template)):
            $themeroot = get_theme_root() . '/' . $theme;
            $files = $chld_thm_cfg->css->recurse_directory(trailingslashit(get_theme_root()) . $theme, 'php');
            $counter = 0;
            sort($files);
            ob_start();
            foreach ($files as $file):
                $templatefile = preg_replace('%\.php$%', '', $chld_thm_cfg->theme_basename($theme, $file));
                if ('parnt' == $template && (preg_match('%^(inc|core|lang|css|js)%',$templatefile) || 'functions' == basename($templatefile))) continue; ?>
<label class="ctc-input-cell smaller<?php echo 'child' == $template && !$chld_thm_cfg->fs && is_writable($file) ? ' writable' : ''; ?>">
  <input class="ctc_checkbox" id="ctc_file_<?php echo $template . '_' . ++$counter; ?>" 
                    name="ctc_file_<?php echo $template; ?>[]" type="checkbox" 
                    value="<?php echo $templatefile; ?>" />
  <?php echo $templatefile; ?></label>
<?php             
            endforeach;
            $linktext = __('Click here to edit template files using the Theme Editor', 'chld_thm_cfg');
            $editorlink = '<a href="' . admin_url('theme-editor.php?file=functions.php&theme=' . $chld_thm_cfg->css->get_prop('child')) . '" title="' . $linktext . '">';
            $editorlinkend = '</a>';
            if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT):
                $linktext = __('The Theme editor has been disabled. Template files must be edited offline.', 'chld_thm_cfg');
                $editorlink = '';
                $editorlinkend = '';
            endif;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ($counter): ?>
<div class="ctc-input-row clearfix" id="input_row_<?php echo $template; ?>_templates">
  <form id="ctc_<?php echo $template; ?>_templates_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options">
    <?php wp_nonce_field( 'ctc_update' ); ?>
    <div class="ctc-input-cell"> <strong>
      <?php _e(('parnt' == $template ? 'Parent' : 'Child') . ' Templates', 'chld_thm_cfg'); ?>
      </strong>
      <p class="howto">
        <?php if ('parnt' == $template):
            _e('Copy PHP template files from the parent theme by selecting them here.', 'chld_thm_cfg');?>
      </p>
      <p><strong>
        <?php _e('CAUTION: If your child theme is active, the child theme version of the file will be used instead of the parent immediately after it is copied.', 'chld_thm_cfg');?>
        </strong></p>
      <p class="howto"> <?php echo sprintf(__('The %s file is generated separately and cannot be copied here.', 'chld_thm_cfg'), 
        $editorlink . '<code>functions.php</code>' . $editorlinkend
        );
            else:
                echo $editorlink . $linktext . $editorlinkend; ?></p>
      <p class="howto">
        <?php if ($chld_thm_cfg->fs):
                _e('Delete child theme templates by selecting them here.', 'chld_thm_cfg');
            else:
                _e('Delete child theme templates or make them writable by selecting them here. Writable files are displayed in <span style="color:red">red</span>.', 'chld_thm_cfg');
                endif;
            endif; ?>
      </p>
    </div>
    <div class="ctc-input-cell-wide"> <?php echo $inputs; ?> </div>
    <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
    <div class="ctc-input-cell-wide" style="margin-top:10px;margin-bottom:10px">
              <?php if ('child' == $template && !$chld_thm_cfg->fs): ?>
      <input class="ctc_submit button button-primary" id="ctc_templates_writable_submit" 
              name="ctc_templates_writable_submit" type="submit" 
              value="<?php _e('Make Selected Writable', 'chld_thm_cfg'); ?>" />&nbsp; &nbsp;
              <?php endif; ?>
      <input class="ctc_submit button button-primary" id="ctc_<?php echo $template; ?>_templates_submit" 
              name="ctc_<?php echo $template; ?>_templates_submit" type="submit" 
              value="<?php echo ('parnt' == $template ?  __('Copy Selected to Child Theme', 'chld_thm_cfg') : __('Delete Selected', 'chld_thm_cfg')); ?>" />
    </div>
  </form>
</div>
<?php
            endif;
        endif;
    }
    
    function render_image_form() {
        global $chld_thm_cfg; 
        if ($theme = $chld_thm_cfg->css->get_prop('child')):
            $imgdir  = trailingslashit($theme) . 'images';
            $themeuri   = trailingslashit(get_theme_root_uri()) . trailingslashit($imgdir);
            $files = $chld_thm_cfg->css->recurse_directory(trailingslashit(get_theme_root()) . $imgdir, 'img');
            
            $counter = 0;
            sort($files);
            ob_start();
            foreach ($files as $file):
                $templatefile = $chld_thm_cfg->theme_basename($imgdir, $file);  ?>
<div class="ctc-input-cell" style="height:100px">
  <label class="smaller">
    <input class="ctc_checkbox" id="ctc_img_<?php echo ++$counter; ?>" 
                    name="ctc_img[]" type="checkbox" 
                    value="<?php echo $templatefile; ?>" />
    <?php echo $templatefile; ?></label>
  <br/>
  <a href="<?php echo $themeuri . $templatefile . '?' . time(); ?>" class="thickbox"><img src="<?php echo $themeuri . $templatefile . '?' . time(); ?>" height="72" width="72" style="max-height:72px;max-width:100%;width:auto;height:auto" /></a></div>
<?php             
            endforeach;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ($counter): ?>
<div class="ctc-input-row clearfix" id="input_row_images">
  <form id="ctc_image_form" method="post" action="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=file_options">
    <?php wp_nonce_field( 'ctc_update' ); ?>
    <div class="ctc-input-cell"> <strong>
      <?php _e('Child Theme Images', 'chld_thm_cfg'); ?>
      </strong>
      <p class="howto">
        <?php _e('Remove child theme images by selecting them here.', 'chld_thm_cfg');?>
      </p>
    </div>
    <div class="ctc-input-cell-wide"> <?php echo $inputs; ?> </div>
    <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
    <div class="ctc-input-cell-wide" style="margin-top:10px;margin-bottom:10px">
      <input class="ctc_submit button button-primary" id="ctc_image_submit" 
                name="ctc_image_submit"  type="submit" 
                value="<?php _e('Remove Selected', 'chld_thm_cfg'); ?>" disabled />
    </div>
  </form>
</div>
<?php
            endif;
        endif;
    }
    
    function get_theme_screenshot() {
        global $chld_thm_cfg;
        foreach (array_keys($chld_thm_cfg->imgmimes) as $extreg): foreach (explode('|', $extreg) as $ext):
            if ($screenshot = $chld_thm_cfg->css->is_file_ok($chld_thm_cfg->css->get_child_target('screenshot.' . $ext))):
                $screenshot = trailingslashit(get_theme_root_uri()) . $chld_thm_cfg->theme_basename('', $screenshot);
                return $screenshot . '?' . time();
            endif;
        endforeach; endforeach;
        return FALSE;
    }
    
    function settings_errors() {
        global $chld_thm_cfg;
        if (count($chld_thm_cfg->errors)):
            echo '<div class="error"><ul>' . LF;
            foreach ($chld_thm_cfg->errors as $err):
                echo '<li>' . $err . '</li>' . LF;
            endforeach;
            echo '</ul></div>' . LF;
        elseif (isset($_GET['updated'])):
            echo '<div class="updated">' . LF;
            if ( 8 == $_GET['updated']):
                echo '<p>' . __('Child Theme files modified successfully.', 'chld_thm_cfg') . '</p>' . LF;
            else:
                echo '<p>' . apply_filters('chld_thm_cfg_update_msg', sprintf(__('Child Theme <strong>%s</strong> has been generated successfully.
                ', 'chld_thm_cfg'),
                $chld_thm_cfg->css->get_prop('child_name')), $chld_thm_cfg) . LF
                //. (1 == $_GET['updated'] && 'direct' != $chld_thm_cfg->fs_method ? '<br/>' . __( 'To enable Child Theme Configurator for editing, the stylesheet has been made writable. You can change this back when you are finished editing for security by clicking "Make read-only" under the "Files" tab.', 'chld_thm_cfg') : '')
                . '<strong>IMPORTANT: <a href="http://www.lilaeamedia.com/plugins/child-theme-configurator/#preview_activate">Test your child theme</a> before activating.</strong></p>';
            endif;
            if ( 9 == $_GET['updated']) echo '<p>' . __('Please verify the imports below and remove any imports that are not needed by the front end, such as admin or configuration stylesheets.', 'chld_thm_cfg') . '</p>' . LF;
            echo '</div>' . LF;
        endif;
    }
    
    function render_help_tabs() {
	    global $wp_version, $chld_thm_cfg;
	    if ( version_compare( $wp_version, '3.3') >= 0 ) {
	
		    $screen = get_current_screen();

            if ( $screen->id != $chld_thm_cfg->hook )
			    return;
    		// Add help tabs

	    	$screen->add_help_tab( array(
		    	'id'	=> 'ctc_tutorial',
			    'title'	=> __( 'Tutorial Video', 'chld_thm_cfg' ),
			    'content'	=> __('<iframe width="480" height="270" src="//www.youtube.com/embed/xL2HkWQxgOA?rel=0&modestbranding=1" frameborder="0" allowfullscreen></iframe>', 'chld_thm_cfg'), 
		    ) );


	    	$screen->add_help_tab( array(
		    	'id'	=> 'ctc_getting_started',
			    'title'	=> __( 'Start Here', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>The first step is to create a child theme and import your parent theme styles into the configurator.</p>
<ol><li>Select an existing parent theme from the menu.</li>
<li>Select "New" or "Existing" child theme.
<ul><li>If creating a new theme, enter a "slug" (lower case, no spaces). This is used to name the theme directory and identify the theme to WordPress.</li>
<li>If using an existing theme, select a child theme from the menu.</li></ul></li>
<li>Enter a Name for the child theme.</li>
<li>Enter an author for the child theme.</li>
<li>Enter the child theme version number.</li>
<li>If you check "Backup Stylesheet", The Child Theme Configurator will create a backup in the theme directory.</li>
<li>If your theme uses additional stylesheets they will appear as checkbox options when you open the toggle arrow. Select only the stylesheets you wish to customize to reduce overhead. Remember to select them again if you reload your configuration.</li>
<li>Click "Generate Child Theme."</li></ol>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_query_selector',
			    'title'	=> __( 'Query/Selector', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Query/Selector tab lets you find specific selectors and edit them. First, find the query that contains the selector you wish to edit by typing in the <strong>Query</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. Selectors are in the <strong>base</strong> query by default.</p>
<p>Next, find the selector by typing in the <strong>Selector</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the rules for that selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the <strong>border</strong> and <strong>background-image</strong> get special treatment.</p>
<p>The "Order" field contains the original sequence of the selector in the parent theme stylesheet. You can change the selector order sequence by entering a lower or higher number in the "Order" field. You can also force style overrides (so called "!important" flag) by checking the "!" box next to each input. Please use judiciously.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_rule_value',
			    'title'	=> __( 'Rule/Value', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Rule/Value tab lets you find specific values for a given rule and then edit that value for individual selectors that use that rule/value combination. First, find the rule you wish to override by typing in the <strong>Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the unique values that exist for that rule in the parent stylesheet with a Sample preview for that value. If there are values that exist in the child stylesheet that do not exist in the parent stylesheet, they will be displayed as well.</p>
<p>For each unique value, click the "Selectors" link to view a list of selectors that use that rule/value combination, grouped by query with a Sample preview of the value and inputs for the child value. Any existing child values will be automatically populated.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_new_styles',
			    'title'	=> __( 'Add New Styles', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>If you wish to add additional rules to a given selector, first load the selector using the Query/Selector tab. Then find the rule you wish to override by typing in the <strong>New Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. This will add a new input row to the selector inputs.</p>
<p>If you wish to add completely new selectors, or even new @media queries, you can enter free-form CSS in the "New Selector" textarea. Be aware that your syntax must be correct (i.e., balanced curly braces, etc.) for the parser to load the new styles. You will know it is invalid because a red "X" will appear next to the save button.</p>
<p>If you prefer to use shorthand syntax for rules and values instead of the inputs provided by the Child Theme Configurator, you can enter them here as well. The parser will convert your input into normalized CSS code automatically.</p>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_imports',
			    'title'	=> __( '@imports and Web Fonts', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>You can add additional stylesheets and web fonts by typing @import rules into the textarea on the @import tab. <strong>Important: The Child Theme Configurator adds the @import rule that loads the Parent Theme\'s stylesheet automatically. Do not need to add it here.</strong></p>
<p>Below is an example that loads a local custom stylesheet (you would have to add the "fonts" directory and stylesheet) as well as the web font "Open Sans" from Google Web Fonts:</p>
<blockquote><pre><code>
@import url(fonts/stylesheet.css);
@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic);
</code></pre></blockquote>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_files',
			    'title'	=> __( 'Files', 'chld_thm_cfg' ),
			    'content'	=> __( '
<h5>Parent Templates</h5><p>Copy PHP template files from the parent theme by checking the boxes and clicking "Copy Selected to Child Theme" and the templates will be added to the child theme directory.</p>
<p><strong>CAUTION: If your child theme is active, the child theme version of the file will be used instead of the parent immediately after it is copied.</strong></p>
<p>The <code>functions.php</code> file is generated separately and cannot be copied here.</p>
<h5>Child Templates</h5><p>Templates copied from the parent are listed here. These can be edited using the Theme Editor in the Appearance Menu.</p>
<p>Remove child theme images by checking the boxes and clicking "Remove Selected from Child Theme."</p>
<h5>Child Theme Images</h5><p>Theme images reside under the <code>images</code> directory in your child theme and are meant for stylesheet use only. Use the media gallery for content images.</p>
<p>You can upload new images using the image upload form.</p>
<h5>Child Theme Screenshot</h5><p>You can upload a custom screenshot for the child theme here.</p>
<p>The theme screenshot should be a 4:3 ratio (eg., 880px x 660px) JPG, PNG or GIF. It will be renamed <code>screenshot</code>.</p>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_preview',
			    'title'	=> __( 'Preview and Activate', 'chld_thm_cfg' ),
			    'content'	=> __( '<p><strong>IMPORTANT: <a target="_blank" href="http://www.lilaeamedia.com/plugins/child-theme-configurator/#preview_activate" title="Test your child theme before activating!">Test your child theme before activating!</a></strong> Some themes (particularly commercial themes) do not adhere to the Theme Development guidelines set forth by WordPress.org, and do not correctly load parent template files or automatically load child theme stylesheets or php files. <strong>In the worst cases they will break your website when you activate the child theme.</strong></p>
<ol><li>Navigate to Appearance > Themes in the WordPress Admin. You will now see the new Child Theme as one of the installed Themes.</li>
<li>Click "Live Preview" below the new Child Theme to see it in action.</li>
<li>When you are ready to take the Child Theme live, click "Activate."</li></ol>
<p>You can also click the Child or Parent CSS tab to reference the stylesheet code.</p>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_permissions',
			    'title'	=> __( 'File Permissions', 'chld_thm_cfg' ),
			    'content'	=> __( '
<p>WordPress was designed to work on a number of server configurations. Child Theme Configurator uses the WordPress Filesystem API to allow changes to sites that require user permission to edit files.</p><p>However, because most of the functionality occurs via AJAX (background) requests, the child theme stylesheet must be writable by the web server.</p><p>The plugin will automatically detect your configuration and provide a number of options to resolve this requirement. Use the links provided to find out more about the options available, including:</p>
<ol><li>Temporarily making the stylesheet writable through the plugin.</li>
<li>Adding your FTP/SSH credentials to the WordPress config file.</li>
<li>Setting the stylesheet write permissions on the server manually</li>
<li>Configuring your web server to allow write access in certain situations.</li>
</ol>
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_faq',
			    'title'	=> __( 'FAQs', 'chld_thm_cfg' ),
			    'content'	=> __( '
<h5>Does it work with Plugins?</h5>
<p>We offer a premium extension to let you easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. <a href="http://www.lilaeamedia.com/plugins/child-theme-plugin-styles" title="Child Theme Configurator Extension">Learn more</a></p>
<h5 id="doesnt_work">Why doesn’t this work with my (insert theme vendor here) theme?</h5>
<p>Some themes (particularly commercial themes) do not adhere to the Theme Development guidelines set forth by WordPress.org, and do not correctly load parent template files or automatically load child theme stylesheets or php files.</p>
<p>This is unfortunate, because in the best case they effectively prohibit the webmaster from adding any customizations (other than those made through the admin theme options) that will survive past an upgrade. <strong>In the worst case they will break your website when you activate the child theme.</strong></p>
<p>Contact the vendor directly to ask for this core functionality. It is our opinion that ALL themes (especially commercial ones) must pass the Theme Unit Tests outlined by WordPress.org.</p>
<h5>Can I edit the Child Theme stylesheet manually offline or by using the Editor or do I have to use the Configurator?</h5>
<p>You can make any manual changes you wish to the stylesheet. Just make sure you import the revised stylesheet using the Parent/Child panel or the Configurator will overwrite your changes the next time you use it. Just follow the steps as usual but select the "Use Existing Child Theme" radio button as the "Child Theme" option. The Configurator will automatically update its internal data from the new stylesheet.</p>
<h5>Why doesn\'t the Parent Theme have any styles when I "View Parent CSS"?</h5>
<p>Your Parent theme is probably using a separate location for the stylesheets. Select individual stylesheets from the "Parse Additional Stylesheets" section of the Parent/Child tab and click "Generate Child Theme Files" again.</p>
<h5 id="menus-broken">Why are my menus displaying incorrectly when I activate the new child theme?</h5>
<p>The child theme creates a new instance in the WordPress options data and the menus have to be assigned. Go to Appearance &gt; Menus and assign locations to each of the menus for the new Child Theme.</p>
<h5 "preview-not-loading">Why do the preview tabs return "Stylesheet could not be displayed"?</h5>
<p>You have to configure at least one child theme from the Parent/Child tab for the preview to display.</p>
<h5 id="specific_color">How do I change a specific color/font style/background?</h5>
<p>You can override a specific value globally using the Rule/Value tab. See Rule/Value, above.</p>
<h5 id="add_styles">How do I add styles that aren\'t in the Parent Theme?</h5>
<p>You can add queries and selectors using the "New Selector(s)" textarea on the Query/Selector tab. See Query/Selector, above.</p>
<h5 id="add_styles">How do I remove a style from the Parent Theme?</h5>
<p>You shouldn\'t really "remove" a style from the Parent. You can, however, set the rule to "inherit," "none," or zero (depending on the rule). This will negate the Parent value. Some experimentation may be necessary.</p>
<h5 id="remove_styles">How do I remove a style from the Child Theme?</h5>
<p>Delete the value from the input for the rule you wish to remove. The Child Theme Configurator only adds overrides for rules that contain values.</p>
<h5 id="important_flag">How do I set the !important flag?</h5>
<p>We always recommend relying on good cascading design over global overrides. To that end, you have ability to change the load order of child theme styles by entering a value in the "Order" field. And yes, you can now set rules as important by checking the "!" box next to each input. Please use judiciously.</p>
<h5 id="gradients">How do I create cross-browser gradients?</h5>
<p>The Child Theme Configurator automatically generates the vendor prefixes and filters to display gradients across most browsers. It uses a normalized syntax and only supports two colors without intermediate stops. The inputs consist of origin (e.g., top, left, 135deg, etc.), start color and end color. The browser-specific syntax is generated automatically when you save these values. <strong>Note:</strong> For Internet Explorer, a filter rule approximates the gradient but can only be horizontal (origin top) or vertical (origin left). The legacy webkit-gradient syntax is not supported.</p>
<h5 id="responsive">How do I make my Theme responsive?</h5>
<p>The short answer is to use a responsive Parent Theme. Some common characteristics of responsive design are:</p>
<ul><li>Avoiding fixed width and height values. Using max- and min-height values and percentages are ways to make your designs respond to the viewer\'s browser size.</li>
<li>Combining floats and clears with inline and relative positions allow the elements to adjust gracefully to their container\'s width.</li>
<li>Showing and hiding content with Javascript.</li></ul>
<iframe width="480" height="270" src="//www.youtube.com/embed/iBiiAgsK4G4?rel=0&modestbranding=1" frameborder="0" allowfullscreen></iframe> 
<h5 id="web_fonts">How do I add Web Fonts?</h5>
<p>The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the <strong>font-family</strong> rule. Be sure you understand the license for any embedded fonts.</p>
<p>You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab. <strong>Note:</strong> Currently the Child Theme Configurator does not generate previews of imported web fonts, but will in a later release.</p>
<h5 id="functions_file">Where are the PHP files?</h5>
<p>The configurator automatically adds a blank functions.php file to the child theme directory. Other parent theme files can be copied using the "Files" tab. Theme images and a custom screenshot can be uploaded there as well.</p>
                    ', 'chld_thm_cfg'
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_glossary',
			    'title'	=> __( 'Glossary', 'chld_thm_cfg' ),
			    'content'	=> __( '
<h3 id="terms">Glossary</h3>
<ul><li id="parent_theme"><strong>Parent Theme</strong> The WordPress Theme you wish to edit. WordPress first loads the Child Theme, then loads the Parent Theme. If a style exists in the Child Theme, it overrides the Parent Theme.</li>
 <li id="child_theme"><strong>Child Theme</strong> New Theme based on Parent Theme. You can create any number of Child Themes from a single Parent Theme.</li>
 <li id="class"><strong>Class</strong> A term used to organize objects. For example, a &lt;div&gt; might be assigned the "blue-text" class. The stylesheet might then assign the "color: blue;" rule to members of the "blue-text" class. Thus, the &lt;div&gt; would display text as blue in the browser.</li>
 <li id="selector"><strong>Selector</strong> One or more html elements, classes, ids or other terms used to identify groups of objects.</li>
 <li id="rule"><strong>Rule</strong> One of many standardized attributes used to tell the browser how to display objects matching a given selector. Examples are <strong>color</strong>, <strong>background-image</strong> and <strong>font-size</strong>.</li>
<li id="at-rule"><strong>At-rule</strong> CSS browser instruction to extend default functionality. The Child Theme Configurator supports two At-rules:
<ul> <li id="import"><strong>@import</strong> Instructs the browser to load additional CSS information from an external source.</li>
 <li id="query"><strong>@media (Media Query)</strong> Identifies blocks of styles that are used only when certain browser characteristics are true. Examples are max-width, screen and print.</li>
</ul></li>
 <li id="override"><strong>Override</strong> When a selector exists in both the Child Theme and the Parent Theme, the Child Theme takes priority over the Parent theme. This is where the Child Theme Configurator stands out: it helps you create <strong>exact overrides</strong> of selectors from the Parent Theme, eliminating hours of trial and error.</li>
 </ul> 
				    ', 'chld_thm_cfg'
			    ),
		    ) );

		    // Set help sidebar
		    $screen->set_help_sidebar(
			    '
                <h4>Now it works with plugins, too!</h4>
                <p style="font-size:smaller">Easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. <a href="http://www.lilaeamedia.com/plugins/child-theme-plugin-styles" title="Child Theme Configurator Extension">Learn more</a></p>
			    <ul>
                    <li><a href="http://www.lilaeamedia.com/about/contact/">' . __( 'Contact us', 'chld_thm_cfg' ) . '</a></li>
				    <li><a href="http://www.lilaeamedia.com/plugins/child-theme-configurator">' . __( 'Plugin Website', 'chld_thm_cfg' ) . '</a></li>
				    <li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ">' . __( 'Donate', 'chld_thm_cfg' ) . '</a></li>
				    <li><a href="http://wordpress.org/support/view/plugin-reviews/child-theme-configurator?rate=5#postform">' . __( 'Give Us 5 Stars', 'chld_thm_cfg' ) . '</a></li>
				    <li><a href="http://codex.wordpress.org/Child_Themes">' . __( 'WordPress Codex', 'chld_thm_cfg' ) . '</a></li>
				    <li><a href="http://wordpress.stackexchange.com/">' . __( 'WordPress Answers', 'chld_thm_cfg' ) . '</a></li>
			    </ul>
			    '
		    );
        }
    }
}
?>
