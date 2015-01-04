<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/*
    Class: Child_Theme_Configurator_UI
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Handles the plugin User Interface
    Version: 1.6.3
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/
class ChildThemeConfiguratorUI {
    // helper function to globalize ctc object
    function ctc() {
        global $chld_thm_cfg; 
        return $chld_thm_cfg;
    }

    function render() {
        $css        = $this->ctc()->css;
        $themes     = $this->ctc()->themes;
        $parent     = isset( $_GET['ctc_parent'] ) ? sanitize_text_field( $_GET['ctc_parent'] ) : $css->get_prop( 'parnt' );
        $child      = $css->get_prop( 'child' );
        $configtype = $css->get_prop( 'configtype' );
        if ( empty( $configtype ) ) $configtype = 'theme';
        $hidechild  = ( count( $themes['child'] ) ? '' : 'style="display:none"' );
        $enqueueset = 'theme' != $configtype || isset( $css->enqueue );
        $mustimport = $this->parent_stylesheet_check( $parent );
        $imports    = $css->get_prop( 'imports' );
        $id         = 0;
        $this->ctc()->fs_method = get_filesystem_method();
        add_thickbox();
        add_action( 'chld_thm_cfg_related_links', array( $this, 'lilaea_plug' ) );
        include ( $this->ctc()->pluginPath .'/includes/forms/main.php' ); 
    } 

     function parent_stylesheet_check( $parent ) {
        $file  = trailingslashit( get_theme_root() ) . trailingslashit( $parent ) . 'header.php';
        $regex = '/<link[^>]+?stylesheet_ur[li]/is';
        if ( file_exists( $file ) ):
            $contents = file_get_contents( $file );
            if ( preg_match( $regex, $contents ) ) return TRUE;
        endif;
        return FALSE;
    }
   
    
   function render_theme_menu( $template = 'child', $selected = NULL ) {
         ?>
        <select class="ctc-select" id="ctc_theme_<?php echo $template; ?>" name="ctc_theme_<?php echo $template; ?>" style="visibility:hidden"><?php
        foreach ( $this->ctc()->themes[$template] as $slug => $theme )
            echo '<option value="' . $slug . '"' . ( $slug == $selected ? ' selected' : '' ) . '>' 
                . esc_attr( $theme['Name'] ) . '</option>' . LF; ?>
        </select>
        <div style="display:none">
        <?php 
        foreach ( $this->ctc()->themes[$template] as $slug => $theme )
            include ( $this->ctc()->pluginPath . 'includes/forms/themepreview.php' ); ?>
        </div>
        <?php
    }
        
    function render_file_form( $template = 'parnt' ) {
        global $wp_filesystem; 
        if ( $theme = $this->ctc()->css->get_prop( $template ) ):
            $themeroot  = trailingslashit( get_theme_root() ) . trailingslashit( $theme );
            $files      = $this->ctc()->get_files( $theme );
            $counter    = 0;
            sort( $files );
            ob_start();
            foreach ( $files as $file ):
                $templatefile = preg_replace( '%\.php$%', '', $file );
                $excludes = implode( "|", ( array ) apply_filters( 'chld_thm_cfg_template_excludes', $this->ctc()->excludes ) );
                if ( 'parnt' == $template && ( preg_match( '%^(' . $excludes . ' )\w*\/%',$templatefile ) 
                    || 'functions' == basename( $templatefile ) ) ) continue; 
                include ( $this->ctc()->pluginPath . 'includes/forms/file.php' );            
            endforeach;
            if ( 'child' == $template && ( $backups = $this->ctc()->get_files( $theme, 'backup' ) ) ):
                foreach ( $backups as $backup => $label ):
                    $templatefile = preg_replace( '%\.css$%', '', $backup );
                    include ( $this->ctc()->pluginPath . 'includes/forms/backup.php' );            
                endforeach;
            endif;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ( $counter ):
                include ( $this->ctc()->pluginPath . 'includes/forms/fileform.php' );            
            endif;
        endif;
    }
    
    function render_image_form() {
         
        if ( $theme = $this->ctc()->css->get_prop( 'child' ) ):
            $themeuri   = trailingslashit( get_theme_root_uri() ) . trailingslashit( $theme );
            $files = $this->ctc()->get_files( $theme, 'img' );
            
            $counter = 0;
            sort( $files );
            ob_start();
            foreach ( $files as $file ): 
                $templatefile = preg_replace( '/^images\//', '', $file );
                include( $this->ctc()->pluginPath . 'includes/forms/image.php' );             
            endforeach;
            $inputs = ob_get_contents();
            ob_end_clean();
            if ( $counter ) include( $this->ctc()->pluginPath . 'includes/forms/images.php' );
        endif;
    }
    
    function get_theme_screenshot() {
        
        foreach ( array_keys( $this->ctc()->imgmimes ) as $extreg ): 
            foreach ( explode( '|', $extreg ) as $ext ):
                if ( $screenshot = $this->ctc()->css->is_file_ok( $this->ctc()->css->get_child_target( 'screenshot.' . $ext ) ) ):
                    $screenshot = trailingslashit( get_theme_root_uri() ) . $this->ctc()->theme_basename( '', $screenshot );
                    return $screenshot . '?' . time();
                endif;
            endforeach; 
        endforeach;
        return FALSE;
    }
    
    function settings_errors() {
        
        if ( count( $this->ctc()->errors ) ):
            echo '<div class="error"><ul>' . LF;
            foreach ( $this->ctc()->errors as $err ):
                echo '<li>' . $err . '</li>' . LF;
            endforeach;
            echo '</ul></div>' . LF;
        elseif ( isset( $_GET['updated'] ) ):
            echo '<div class="updated">' . LF;
            if ( 8 == $_GET['updated'] ):
                echo '<p>' . __( 'Child Theme files modified successfully.', 'chld_thm_cfg' ) . '</p>' . LF;
            else:
                $child_theme = wp_get_theme( $this->ctc()->css->get_prop( 'child' ) );
                echo '<p>' . apply_filters( 'chld_thm_cfg_update_msg', sprintf( __( 'Child Theme <strong>%s</strong> has been generated successfully.
                ', 'chld_thm_cfg' ), $child_theme->Name ), $this->ctc() ) . LF;
                if ( ! $this->ctc()->css->get_prop( 'configtype' ) || $this->ctc()->css->get_prop( 'configtype' ) == 'theme' ):
                echo '<strong>' . __( 'IMPORTANT:', 'chld_thm_cfg' ) . LF;
                if ( is_multisite() && !$child_theme->is_allowed() ): 
                    echo 'You must <a href="' . network_admin_url( '/themes.php' ) . '" title="' . __( 'Go to Themes', 'chld_thm_cfg' ) . '" class="ctc-live-preview">' . __( 'Network enable', 'chld_thm_cfg' ) . '</a> ' . __( 'your child theme.', 'chld_thm_cfg' );
                else: 
                    echo '<a href="' . admin_url( '/customize.php?theme=' . $this->ctc()->css->get_prop( 'child' ) ) . '" title="' . __( 'Live Preview', 'chld_thm_cfg' ) . '" class="ctc-live-preview">' . __( 'Test your child theme', 'chld_thm_cfg' ) . '</a> ' . __( 'before activating.', 'chld_thm_cfg' );
                endif;
                echo '</strong></p>' . LF;
                endif;
             endif;
            echo '</div>' . LF;
        endif;
    }
    
    function render_help_content() {
	    global $wp_version;
	    if ( version_compare( $wp_version, '3.3' ) >= 0 ) {
	
		    $screen = get_current_screen();
                
            // load help content via output buffer so we can use plain html for updates
            // then use regex to parse for help tab parameter values
            
            $regex_sidebar = '/' . preg_quote( '<!-- BEGIN sidebar -->' ) . '(.*?)' . preg_quote( '<!-- END sidebar -->' ) . '/s';
            $regex_tab = '/' . preg_quote( '<!-- BEGIN tab -->' ) . '\s*<h\d id="(.*?)">(.*?)<\/h\d>(.*?)' . preg_quote( '<!-- END tab -->' ) . '/s';
            ob_start();
            // stub for multiple languages future release
            include( $this->ctc()->pluginPath . 'includes/help/help_en_US.php' );
            $help_raw = ob_get_contents();
            ob_end_clean();
            // parse raw html for tokens
            preg_match( $regex_sidebar, $help_raw, $sidebar );
            preg_match_all( $regex_tab, $help_raw, $tabs );

    		// Add help tabs
            if ( isset( $tabs[1] ) ):
                while( count( $tabs[1] ) ):
                    $id         = array_shift( $tabs[1] );
                    $title      = array_shift( $tabs[2] );
                    $content    = array_shift( $tabs[3] );
	    	        $screen->add_help_tab( array(
	    	    	    'id'        => $id,
    		    	    'title'     => $title,
	    		        'content'   => $content, 
                    ) );
                endwhile;
            endif;
            if ( isset( $sidebar[1] ) )
                $screen->set_help_sidebar( $sidebar[1] );

        }
    }
    function lilaea_plug() {
        include ( $this->ctc()->pluginPath . 'includes/forms/related.php' );
    }
}
?>
