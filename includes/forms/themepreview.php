<?php 
if (!defined('ABSPATH')) exit;
// Theme Preview
// Renders localized version of theme preview that is merged into 
// jQuery selectmenu object for parent and child theme options
?>

<div id="ctc_theme_option_<?php echo $slug; ?>" class="clearfix ctc-theme-option">
  <div class="ctc-theme-option-left"><img src="<?php echo $theme['screenshot']; ?>" class="ctc-theme-option-image"/></div>
  <div class="ctc-theme-option-right">
    <h3 class="theme-name"><?php echo $theme['Name']; ?></h3>
    <?php _e('Version: ', 'chld_thm_cfg'); echo esc_attr($theme['Version']);?>
    <br/>
    <?php _e('By: ', 'chld_thm_cfg'); echo esc_attr($theme['Author']);?>
    <br/>
    <a href="<?php echo admin_url('/customize.php?theme=' . $slug);?>" title="<?php _e('Live Preview', 'chld_thm_cfg'); ?>" class="ctc-live-preview">
    <?php _e('Live Preview', 'chld_thm_cfg');?>
    </a></div>
</div>
