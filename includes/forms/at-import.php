<?php  
if (!defined('ABSPATH')) exit;
// @imports Panel
?>

<div id="import_options_panel" 
        class="ctc-option-panel<?php echo 'import_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>>
  <form id="ctc_import_form" method="post" action="?page=<?php echo CHLD_THM_CFG_MENU; ?>">
    <?php wp_nonce_field( 'ctc_update' ); ?>
    <div class="ctc-input-row clearfix" id="ctc_child_imports_row">
      <div class="ctc-input-cell">
        <div class="ctc-textarea-button-cell" id="ctc_save_imports_cell">
          <input type="button" class="button ctc-save-input" id="ctc_save_imports" 
            name="ctc_save_imports" value="<?php _e('Save', 'chld_thm_cfg'); ?>"  disabled />
        </div>
        <strong>
        <?php _e('@import Statements', 'chld_thm_cfg'); ?>
        </strong>
      </div>
      <div class="ctc-input-cell-wide">
        <textarea id="ctc_child_imports" name="ctc_child_imports" wrap="off"><?php 
    if (!empty($imports)):
        foreach ($imports as $import):
            echo esc_textarea($import . ';' . LF);
        endforeach; 
    endif; ?>
</textarea>
      </div>
    </div>
  </form>
</div>
