<?php  
if (!defined('ABSPATH')) exit;
// Files Panel
?>

<div id="file_options_panel" 
        class="ctc-option-panel<?php echo 'file_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
  <?php $this->render_file_form('parnt'); ?>
  <?php $this->render_file_form('child'); ?>
  <?php $this->render_image_form(); ?>
  <div class="ctc-input-row clearfix" id="input_row_theme_image">
    <form id="ctc_theme_image_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>" enctype="multipart/form-data">
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
    <form id="ctc_screenshot_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>" enctype="multipart/form-data">
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
    <form id="ctc_export_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>">
      <?php wp_nonce_field( 'ctc_update' ); ?>
      <div class="ctc-input-cell"> <strong>
        <?php _e('Export Child Theme as Zip Archive', 'chld_thm_cfg'); ?>
        </strong> </div>
      <div class="ctc-input-cell-wide">
        <input class="ctc_submit button button-primary" id="ctc_export_child_zip" 
                name="ctc_export_child_zip"  type="submit" 
                value="<?php _e('Export', 'chld_thm_cfg'); ?>" />
      </div>
    </form>
  </div>
  <?php if ('direct' != $this->ctc()->fs_method): ?>
  <div class="ctc-input-row clearfix" id="input_row_permissions">
    <form id="ctc_permission_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>">
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
  </div>
  <?php endif; ?>
</div>
