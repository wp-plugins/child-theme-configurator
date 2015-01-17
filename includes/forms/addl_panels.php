<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
if ( !defined( 'CHLD_THM_CFG_PLUGINS_VERSION' ) ):
$page = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
$linktext = __( 'Reload', 'chld_thm_cfg_plugins' );
$link = '<a href="' . ( is_multisite() ? 
    network_admin_url( 'themes.php' ) : 
        admin_url( 'tools.php' ) ) . '?page=' . $page . '&tab=addl_tab" title="' . $linktext . '">' . $linktext . '</a>';
?><div id="get_pro_panel" class="ctc-option-panel<?php echo 'get_pro' == $active_tab ? ' ctc-option-panel-active' : ''; ?>" <?php echo $hidechild; ?> >
<a href="http://www.lilaeamedia.com/child-theme-configurator-pro/" title="<?php _e( 'Learn more about CTC Pro', 'chld_thm_cfg' ); ?>"><img src="<?php echo CHLD_THM_CFG_URL . 'css/pro-banner.jpg'; ?>" width="610" height="197" /></a>
<h1><?php _e( 'Customizing WordPress Themes just got even easier.', 'chld_thm_cfg' ); ?></h1>
<p><?php _e( 'Thousands of users have already seen the benefits of using Child Theme Configurator. If you spend any amount of time customizing WordPress, CTC Pro will help maximize your productivity.', 'chld_thm_cfg' ); ?></p>
<h1><?php _e( 'Designed by Developers Who Use It Every Day.', 'chld_thm_cfg' ); ?></h1>
<p><?php _e( 'We\'ve packed in more features to make design work quicker and easier with <strong>Child Theme Configurator Pro.', 'chld_thm_cfg' ); ?></strong></p>
<ul>
<li><h3><?php _e( 'Custom Plugin Stylesheets', 'chld_thm_cfg' ); ?></h3>
<p><?php _e( 'Use the top-rated CTC interface to take control of your site\'s plugin styles. All new design makes it much easier to get the results you want.', 'chld_thm_cfg' ); ?></p></li>
<li><h3><?php _e( 'Links to all styles in a single view', 'chld_thm_cfg' ); ?></h3><p><?php _e( 'Use the "All Styles" panel to find the selector you wish to edit from a single combined list.', 'chld_thm_cfg' ); ?></p></li>
<li><h3><?php _e( 'Most recent edits', 'chld_thm_cfg' ); ?></h3><p><?php _e( 'Return to recently edited selectors from a toggleable sidebar.', 'chld_thm_cfg' ); ?></li>
<li><h3><?php _e( 'Free Upgrades', 'chld_thm_cfg' ); ?></h3>
<p><?php _e( 'Your Update Key gives you access to new Pro features as soon as they are released.', 'chld_thm_cfg' ); ?></li>
<li><h3><?php _e( 'Top-rated Online Support', 'chld_thm_cfg' ); ?></h3></li>
<li><h3><?php _e( 'Online Documentation', 'chld_thm_cfg' ); ?></h3></li>
<li><h3><?php _e( 'Tutorial Videos', 'chld_thm_cfg' ); ?></h3></li>
</ul>
<p><?php _e( 'For a limited time we are offering Child Theme Configurator Pro for a special introductory price.', 'chld_thm_cfg' ); ?></p>
<h3><a href="http://www.lilaeamedia.com/plugins/child-theme-configurator-pro/" title="<?php _e( 'Buy CTC Pro', 'chld_thm_cfg' ); ?>"><?php _e( 'Buy Now - Only $12.95 USD', 'chld_thm_cfg' ); ?></a></h3></div><?php
endif;
