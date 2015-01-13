<?php
if ( !defined( 'ABSPATH' ) ) exit;
// main CTC Page 
?>
<style type="text/css">
.ctc-status-icon.success {
 background:url(<?php echo admin_url( 'images/yes.png' );
?>) no-repeat;
}

.ctc-status-icon.failure {
background:url(<?php echo admin_url( 'images/no.png' );
?>) no-repeat;
}

.ctc-exit {
background:#f9f9f9 url(<?php echo includes_url( 'images/xit-2x.gif' );
?>) left top no-repeat;
}
</style>
<div class="wrap">
  <?php do_action( 'chld_thm_cfg_related_links' ); ?>
  <h2><?php echo __( 'Child Theme Configurator', 'chld_thm_cfg' ) . ' ' . __( 'version', 'chld_thm_cfg' ) . ' ' . CHLD_THM_CFG_VERSION; ?></h2>
  <?php 
if ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] && !$this->ctc()->fs ):
        echo $this->ctc()->fs_prompt;
else: ?>
  <div id="ctc_error_notice">
    <?php $this->settings_errors(); ?>
  </div>
    <?php 
    include ( $this->ctc()->pluginPath . 'includes/forms/tabs.php' ); 
?>
    <div class="ctc-option-panel-container">
      <?php 
    include ( $this->ctc()->pluginPath . 'includes/forms/parent-child.php' ); 
    if ( $enqueueset ):
        include ( $this->ctc()->pluginPath . 'includes/forms/rule-value.php' ); 
        include ( $this->ctc()->pluginPath . 'includes/forms/query-selector.php' ); 
        include ( $this->ctc()->pluginPath . 'includes/forms/at-import.php' ); ?>
      <div id="view_child_options_panel" 
        class="ctc-option-panel<?php echo 'view_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>> </div>
      <div id="view_parnt_options_panel" 
        class="ctc-option-panel<?php echo 'view_parnt_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?>> </div>
      <?php 
        if ( '' == $hidechild ): 
            include ( $this->ctc()->pluginPath . 'includes/forms/files.php' );
        endif; 
        do_action( 'chld_thm_cfg_panels', $this->ctc(), $active_tab, $hidechild ); 
    endif; ?>
    </div>
  <?php
endif;
?>
</div>
