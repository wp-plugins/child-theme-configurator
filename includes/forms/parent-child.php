<?php  
if ( !defined( 'ABSPATH' ) ) exit;
// Parent/Child Panel
?>

<div id="parent_child_options_panel" class="ctc-option-panel<?php echo 'parent_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
  <form id="ctc_load_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>">
    <?php 
    wp_nonce_field( 'ctc_update' ); 
    //if ( '' == $hidechild ) 
    do_action( 'chld_thm_cfg_controls', $this->ctc() );
    $disabled = $this->ctc()->is_theme() ? '' : ' disabled ';
    $disabledclass = $this->ctc()->is_theme() ? '' : ' ctc-disabled';
?>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_parnt">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Parent Theme', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <?php $this->render_theme_menu( 'parnt', $this->ctc()->get_current_parent() ); ?>
      </div>
    </div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Child Theme', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc-radio ctc-themeonly" id="ctc_child_type_new" name="ctc_child_type" type="radio" value="new" 
            <?php echo ( !empty( $hidechild ) ? 'checked' : '' ); ?>
            <?php echo $hidechild . ' ' . $disabled;?> />
        <label for="ctc_child_type_new">
          <?php _e( 'Create New Child Theme', 'chld_thm_cfg' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell">
        <input class="ctc-radio ctc-themeonly" id="ctc_child_type_existing" name="ctc_child_type"  type="radio" value="existing" 
            <?php echo ( empty( $hidechild ) ? 'checked' : '' ); ?>
            <?php echo $hidechild . ' ' . $disabled; ?> />
        &nbsp;
        <label for="ctc_child_type_existing" <?php echo $hidechild;?>>
          <?php _e( 'Use Existing Child Theme', 'chld_thm_cfg' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell" style="clear:both"> <strong>&nbsp;</strong> </div>
      <div class="ctc-input-cell" >
        <input class="ctc_text ctc-themeonly" id="ctc_child_template" name="ctc_child_template" type="text" placeholder="<?php _e( 'Theme Slug', 'chld_thm_cfg' ); ?>" autocomplete="off" <?php echo $disabled; ?> />
      </div>
      <?php if ( '' == $hidechild ): ?>
      <div class="ctc-input-cell">
        <?php $this->render_theme_menu( 'child', $child ); ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>" id="input_row_child_name">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Child Theme Name', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_text ctc-themeonly" id="ctc_child_name" name="ctc_child_name"  type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'child_name' ) ); ?>" placeholder="<?php _e( 'Theme Name', 'chld_thm_cfg' ); ?>" autocomplete="off" <?php echo $disabled; ?> />
      </div>
    </div>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Author', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_text" id="ctc_child_author" name="ctc_child_author" type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'author' ) ); ?>" placeholder="<?php _e( 'Author', 'chld_thm_cfg' ); ?>" autocomplete="off" />
      </div>
    </div>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Version', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_text" id="ctc_child_version" name="ctc_child_version" type="text" 
                value="<?php echo esc_attr( $css->get_prop( 'version' ) ); ?>" placeholder="<?php _e( 'Version', 'chld_thm_cfg' ); ?>" autocomplete="off" />
      </div>
    </div>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Copy Parent Theme Menus, Widgets and other Options', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox ctc-themeonly" id="ctc_parent_mods" name="ctc_parent_mods" type="checkbox" 
                value="1" <?php echo $disabled; ?> />
      </div>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'NOTE:', 'chld_thm_cfg' ); ?>
        </strong>
        <?php _e( 'This will overwrite child theme options you may have already set.', 'chld_thm_cfg' ); ?>
      </div>
    </div>
    <?php $parent_handling = ( isset( $css->enqueue ) ? $css->enqueue : ( $mustimport ? 'import' : 'enqueue' ) ); ?>
    <div class="ctc-input-row clearfix ctc-themeonly-container<?php echo $disabledclass; ?>">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Parent stylesheet handling:', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_enqueue" name="ctc_parent_enqueue" type="radio" 
                value="enqueue" <?php checked( 'enqueue', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( '&lt;link&gt; (default)', 'chld_thm_cfg' ); ?>
        </label>
        <br/>
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_import" name="ctc_parent_enqueue" type="radio" 
                value="import" <?php checked( 'import', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( '@import', 'chld_thm_cfg' ); ?>
        </label>
        <br/>
        <label>
          <input class="ctc_radio ctc-themeonly" id="ctc_parent_enqueue_none" name="ctc_parent_enqueue" type="radio" 
                value="none" <?php checked( 'none', $parent_handling ); ?> <?php echo $disabled; ?> />
          <?php _e( 'none (handled by theme)', 'chld_thm_cfg' ); ?>
        </label>
      </div>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'NOTE:', 'chld_thm_cfg' ); ?>
        <?php if ( $mustimport ) _e( 'This theme does not currently enqueue the stylesheet and must use the @import option to render correctly.', 'chld_thm_cfg' ); ?>
        </strong>
        <?php _e( "Select @import for older themes that do not enqueue the stylesheet. Select 'none' if core styles are automatically loaded for child themes. Select '&lt;link&gt;' if unsure.", 'chld_thm_cfg' ); ?>
      </div>
    </div>
    <?php if ( '' == $hidechild ): ?>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'Backup current stylesheet', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_checkbox" id="ctc_backup" name="ctc_backup" type="checkbox" 
                value="1" />
      </div>
      <div class="ctc-input-cell"> <strong>
        <?php _e( 'NOTE:', 'chld_thm_cfg' ); ?>
        </strong>
        <?php _e( 'This creates a copy of the current stylesheet before applying changes. You can remove old backup files using the Files tab.', 'chld_thm_cfg' ); ?>
      </div>
    </div>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell ctc-section-toggle" id="ctc_revert_css"> <strong>
        <?php _e( 'Reset/Restore from backup:', 'chld_thm_cfg' ); ?>
        </strong> </div>
      <div class="ctc-input-cell-wide ctc-section-toggle-content" id="ctc_revert_css_content">
        <label>
          <input class="ctc_checkbox" id="ctc_revert_none" name="ctc_revert" type="radio" 
                value="" checked="" />
          <?php _e( 'Leave unchanged', 'chld_thm_cfg' );?>
        </label>
        <br/>
        <label>
          <input class="ctc_checkbox" id="ctc_revert_all" name="ctc_revert" type="radio" 
                value="all" />
          <?php _e( 'Reset all', 'chld_thm_cfg' );?>
        </label>
        <div id="ctc_backup_files">
          <?php do_action( 'chld_thm_cfg_get_backups' ); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="ctc-input-row clearfix" id="ctc_stylesheet_files">
      <?php
            do_action( 'chld_thm_cfg_get_stylesheets' );
      ?>
    </div>
    <div class="ctc-input-row clearfix">
      <div class="ctc-input-cell"> <strong>&nbsp;</strong> </div>
      <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctc_load_styles" name="ctc_load_styles"  type="submit" 
                value="<?php _e( 'Generate/Rebuild Child Theme Files', 'chld_thm_cfg' ); ?>" disabled />
      </div>
    </div>
  </form>
</div>
