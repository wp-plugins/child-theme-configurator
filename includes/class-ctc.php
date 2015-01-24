<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    Class: Child_Theme_Configurator
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Main Controller Class
    Version: 1.6.4
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014-2015 Lilaea Media
*/
class ChildThemeConfiguratorAdmin {

    // state
    var $is_ajax;
    var $is_get;
    var $is_post;
    var $skip_form;
    var $fs;

    var $fs_prompt;
    var $fs_method;
    var $uploadsubdir;
    var $menuName; // backward compatibility with plugin extension
    var $cache_updates  = TRUE;
    var $debug          = '';
    var $swatch_text    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    // state arrays
    var $themes         = array();
    var $errors         = array();
    var $files          = array();
    var $updates        = array();
    // objects
    var $css;
    var $ui;
    // config arrays
    var $postarrays     = array(
            'ctc_img',
            'ctc_file_parnt',
            'ctc_file_child',
            'ctc_additional_css',
        );
    var $configfields   = array(
            'theme_parnt', 
            'child_type', 
            'theme_child', 
            'child_name',
            'child_template', 
            'child_author',
            'child_version',
            'configtype', // backward compatability
            'revert',
        );
    var $actionfields   = array(
            'load_styles',
            'parnt_templates_submit',
            'child_templates_submit',
            'image_submit',
            'theme_image_submit',
            'theme_screenshot_submit',
            'export_child_zip',
            'reset_permission',
            'templates_writable_submit',
            'set_writable',
            'upgrade',
        );
    var $imgmimes       = array(
        	'jpg|jpeg|jpe'  => 'image/jpeg',
	        'gif'           => 'image/gif',
	        'png'           => 'image/png',
        );
    var $excludes       = array(
            'inc',
            'core',
            'lang',
            'css',
            'js',
            'lib',
            'theme',
            'options',
        );
    
    function __construct() {
        $this->menuName = CHLD_THM_CFG_MENU; // backward compatability for plugins extension
        add_action( 'chld_thm_cfg_tabs', array( &$this, 'render_addl_tabs' ), 10, 4 );
        add_action( 'chld_thm_cfg_panels', array( &$this, 'render_addl_panels' ), 10, 4 );
        $this->is_post = ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] );
        $this->is_get  = ( 'GET' == $_SERVER[ 'REQUEST_METHOD' ] );
    }
    function render() {
        $this->ui->render();
    }
    function enqueue_scripts() {
        wp_enqueue_style( 'chld-thm-cfg-admin', CHLD_THM_CFG_URL . 'css/chld-thm-cfg.css', array(), '1.6.4' );
        
        // we need to use local jQuery UI Widget/Menu/Selectmenu 1.11.2 because selectmenu is not included in < 1.11.2
        // this will be updated in a later release to use WP Core scripts when it is widely adopted
        if ( !wp_script_is( 'jquery-ui-selectmenu', 'registered' ) ): // selectmenu.min.js
            wp_enqueue_script( 'jquery-ui-selectmenu', CHLD_THM_CFG_URL . 'js/selectmenu.min.js', 
                array( 'jquery','jquery-ui-core','jquery-ui-position' ), FALSE, TRUE );
        endif;
        wp_enqueue_script( 'ctc-thm-cfg-ctcgrad', CHLD_THM_CFG_URL . 'js/ctcgrad.min.js', array( 'jquery' ), FALSE, TRUE );
        wp_enqueue_script( 'chld-thm-cfg-admin', CHLD_THM_CFG_URL . 'js/chld-thm-cfg.js',
            array(
                'jquery-ui-autocomplete', 
                'jquery-ui-selectmenu',   
                'wp-color-picker',
            ), FALSE, TRUE );
        $localize_array = apply_filters( 'chld_thm_cfg_localize_script', array(
            'ssl'               => is_ssl(),
            'homeurl'           => get_home_url(),
            'ajaxurl'           => admin_url( 'admin-ajax.php' ),
            'theme_uri'         => get_theme_root_uri(),
            'page'              => CHLD_THM_CFG_MENU,
            'themes'            => $this->themes,
            'source'            => apply_filters( 'chld_thm_cfg_source_uri', get_theme_root_uri() . '/' 
                                    . $this->css->get_prop( 'parnt' ) . '/style.css', $this->css ),
            'target'            => apply_filters( 'chld_thm_cfg_target_uri', get_theme_root_uri() . '/' 
                                    . $this->css->get_prop( 'child' ) . '/style.css', $this->css ),
            'parnt'             => $this->css->get_prop( 'parnt' ),
            'child'             => $this->css->get_prop( 'child' ),
            'addl_css'          => $this->css->get_prop( 'addl_css' ),
            'imports'           => $this->css->get_prop( 'imports' ),
            // caches will be loaded dynamically
            'rule'              => array(),
            'sel_ndx'           => array(),
            'val_qry'           => array(),
            'rule_val'          => array(),
            'sel_val'           => array(),
            'recent'            => array(),
            'field_labels'      => array(
                '_background_url'       => __( 'URL/None',                                                  'chld_thm_cfg' ),
                '_background_origin'    => __( 'Origin',                                                    'chld_thm_cfg' ),
                '_background_color1'    => __( 'Color 1',                                                   'chld_thm_cfg' ),
                '_background_color2'    => __( 'Color 2',                                                   'chld_thm_cfg' ),
                '_border_width'         => __( 'Width/None',                                                'chld_thm_cfg' ),
                '_border_style'         => __( 'Style',                                                     'chld_thm_cfg' ),
                '_border_color'         => __( 'Color',                                                     'chld_thm_cfg' ),
            ),
            'swatch_txt'        => $this->swatch_text,
            'load_txt'          => __( 'Are you sure? This will replace your current settings.',            'chld_thm_cfg' ),
            'swatch_label'      => __( 'Sample',                                                            'chld_thm_cfg' ),
            'important_label'   => __( '<span style="font-size:10px">!</span>',                             'chld_thm_cfg' ),
            'selector_txt'      => __( 'Selectors',                                                         'chld_thm_cfg' ),
            'close_txt'         => __( 'Close',                                                             'chld_thm_cfg' ),
            'edit_txt'          => __( 'Edit',                                                              'chld_thm_cfg' ),
            'cancel_txt'        => __( 'Cancel',                                                            'chld_thm_cfg' ),
            'rename_txt'        => __( 'Rename',                                                            'chld_thm_cfg' ),
            'css_fail_txt'      => __( 'The stylesheet cannot be displayed.',                               'chld_thm_cfg' ),
            'child_only_txt'    => __( '(Child Only)',                                                      'chld_thm_cfg' ),
            'inval_theme_txt'   => __( 'Please enter a valid Child Theme.',                                 'chld_thm_cfg' ),
            'inval_name_txt'    => __( 'Please enter a valid Child Theme name.',                            'chld_thm_cfg' ),
            'theme_exists_txt'  => __( '<strong>%s</strong> exists. Please enter a different Child Theme',  'chld_thm_cfg' ),
            'plugin_txt'        => __( 'Deactivating other plugins may resolve this issue.',                'chld_thm_cfg' ),
            'jquery_txt'        => __( 'Conflicting jQuery libraries were loaded by another plugin:',       'chld_thm_cfg' ),
            'js_txt'            => __( 'The page could not be loaded correctly so some controls have been disabled.',
                                                                                                            'chld_thm_cfg' ),
            'contact_txt'       => sprintf( __( '%sPlease contact Lilaea Media for additional assistance.%s',
                                                                                                            'chld_thm_cfg' ),
                '<a target="_blank" href="' . LILAEAMEDIA_URL . '/about/contact">',
                '</a>' ),
        ) );
        wp_localize_script(
            'chld-thm-cfg-admin', 
            'ctcAjax', 
            apply_filters( 'chld_thm_cfg_localize_array', $localize_array )
        );
    }
    
    /**
     * initialize configurator
     */
    function ctc_page_init () {
        // get all available themes
        $this->get_themes();
        // load config data and validate
        $this->load_config();
        // perform any checks prior to processing config data
        do_action( 'chld_thm_cfg_preprocess' );
        // process any additional forms
        do_action( 'chld_thm_cfg_forms', $this );  // hook for custom forms
        // process main post data
        $this->process_post();
        // initialize UI
        include_once( CHLD_THM_CFG_DIR . '/includes/class-ctc-ui.php' );
        $this->ui = new ChildThemeConfiguratorUI();
        // initialize help
        $this->ui->render_help_content();
        // load styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
        // load web fonts for this theme
        $this->load_imports();
	}
    
    function get_themes() {
        // create cache of theme info
        $this->themes = array( 'child' => array(), 'parnt' => array() );
        foreach ( wp_get_themes() as $theme ):
            // organize into parent and child themes
            $group  = $theme->parent() ? 'child' : 'parnt';
            // get the theme slug
            //if ( 
            $slug = $theme->get_stylesheet(); // )
                // add theme to themes array
                $this->themes[ $group ][ $slug ] = array(
                    'Name'          => $theme->get( 'Name' ),
                    'Author'        => $theme->get( 'Author' ),
                    'Version'       => $theme->get( 'Version' ),
                    'screenshot'    => $theme->get_screenshot(),
                    'allowed'       => $theme->is_allowed(),
                ); 
        endforeach;
    }

    function validate_post( $action = 'ctc_update', $noncefield = '_wpnonce', $cap = 'install_themes' ) {
        // security: request must be post, user must have permission, referrer must be local and nonce must match
        return ( $this->is_post 
            && current_user_can( $cap ) // ( 'edit_themes' )
            && ( $this->is_ajax ? check_ajax_referer( $action, $noncefield, FALSE ) : 
                check_admin_referer( $action, $noncefield, FALSE ) ) );
    }
    
    function load_config() {
        include_once( CHLD_THM_CFG_DIR . '/includes/class-ctc-css.php' );
        $this->css = new ChildThemeConfiguratorCSS();
        // if not new format or themes do not exist reinitialize
        if ( FALSE === $this->css->load_config()
            || ! $this->check_theme_exists( $this->css->get_prop( 'child' ) )
            || ! $this->check_theme_exists( $this->css->get_prop( 'parnt' ) ) ):          
            $this->css = new ChildThemeConfiguratorCSS();
        endif;
        if ( $this->is_get ):
            if ( $this->css->get_prop( 'child' ) ):
                // get filesystem credentials if available
                $this->verify_creds();
                $stylesheet = apply_filters( 'chld_thm_cfg_target', $this->css->get_child_target( 'style.css' ), $this->css );
                // check file permissions
                if ( !is_writable( $stylesheet ) && !$this->fs ):
	                add_action( 'admin_notices', array( $this, 'writable_notice' ) );
                endif;
                // enqueue flag will be null for anyone coming from < 1.6.0
                if ( !isset( $this->css->enqueue ) ):
                    add_action( 'admin_notices', array( $this, 'enqueue_notice' ) ); 	
                endif;
            // check for first run
            elseif ( !isset( $this->css->enqueue ) ):
                add_action( 'admin_notices', array( $this, 'config_notice' ) ); 	
            endif;
            // check if file ownership is messed up from old version or other plugin
            
            if ( fileowner( $this->css->get_child_target( '' ) ) != fileowner( get_theme_root() ) ):
	            add_action( 'admin_notices', array( $this, 'owner_notice' ) ); 
            endif;
        endif;	
    }
    
    /**
     * ajax callback for saving form data 
     */
    function ajax_save_postdata( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        // security check
        if ( $this->validate_post( $action ) ):
            if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' );
            $this->verify_creds(); // initialize filesystem access
            // add these actions before checking configtype
            // set configtype since this request came via ajax
            if ( 'ctc_plugin' == $_POST[ 'action' ] ) do_action( 'chld_thm_cfg_pluginmode' );
            // this action swaps out the above two actions if in plugins mode
            
            $this->load_config(); // get configuration data from options API
            $this->css->parse_post_data(); // parse any passed values
            
            // if child theme config has been set up, save new data
            // return recent edits and selected stylesheets as cache updates
            if ( $this->css->get_prop( 'child' ) ):
                $this->css->write_css();
                // add any additional updates to pass back to browser
                do_action( 'chld_thm_cfg_cache_updates' );
                $this->updates[] = array(
                    'obj'   => 'addl_css',
                    'key'   => '',
                    'data'  => $this->css->get_prop( 'addl_css' ),
                );
            endif;
            $result = $this->css->obj_to_utf8( $this->updates );
            
            // update config data in options API
            $this->css->save_config();
            // send all updates back to browser to update cache
            die( json_encode( $result ) );
        else:
            die( 0 );
        endif;
    }
    
    /**
     * ajax callback to query config data 
     */
    function ajax_query_css( $action = 'ctc_update' ) {
        $this->is_ajax = TRUE;
        if ( $this->validate_post( $action ) ):
            if ( 'ctc_plugin' == $action ) do_action( 'chld_thm_cfg_pluginmode' );
            $this->load_config();
            $regex = "/^ctc_query_/";
            foreach( preg_grep( $regex, array_keys( $_POST ) ) as $key ):
                $name = preg_replace( $regex, '', $key );
                $param[ $name ] = sanitize_text_field( $_POST[ $key ] );
            endforeach;
            if ( !empty( $param[ 'obj' ] ) ):
                // add any additional updates to pass back to browser
                do_action( 'chld_thm_cfg_cache_updates' );
                $this->updates[] = array(
                    'key'   => isset( $param[ 'key' ] ) ? $param[ 'key' ] : '',
                    'obj'   => $param[ 'obj' ],
                    'data'  => $this->css->get_prop( $param[ 'obj' ], $param ),
                );
                die( json_encode( $this->updates ) );
            endif;
        endif;
        die( 0 );
    }
    
    /***
     * Handles processing for all form submissions.
     * Older versions ( < 1.6.0 ) smelled like spaghetti so we moved conditions 
     * to switch statement with the main setup logic in a separate function.
     */
    function process_post() {
        // make sure this is a post
        if ( $this->is_post ):
            // see if a valid action was passed
            foreach ( $this->actionfields as $field ):
                if ( in_array( 'ctc_' . $field, array_keys( $_POST ) ) ):
                    $actionfield = $field;
                    break;
                endif;
            endforeach;
            if ( empty( $actionfield ) ) return FALSE;
            
            // make sure post passes security checkpoint        
            $this->errors = array();
            if ( $this->validate_post( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ) ):
                // zip export does not require filesystem access so check that first
                if ( 'export_child_zip' == $actionfield ):
                    $this->export_zip();
                    // if we get here the zip failed
                    $this->errors[] = __( 'Zip file creation failed.', 'chld_thm_cfg' );
                // all other actions require filesystem access
                else:
                    // handle uploaded file before checking filesystem
                    if ( 'theme_image_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_image' ] ) ):
                        $this->handle_file_upload( 'ctc_theme_image', $this->imgmimes );          
                    elseif ( 'theme_screenshot_submit' == $actionfield && isset( $_FILES[ 'ctc_theme_screenshot' ] ) ):
                        $this->handle_file_upload( 'ctc_theme_screenshot', $this->imgmimes );
                    endif;
                    // now we need to check filesystem access 
                    $args = preg_grep( "/nonce/", array_keys( $_POST ), PREG_GREP_INVERT );
                    $this->verify_creds( $args );
                    if ( $this->fs ):
                        $msg = FALSE;
                        // we have filesystem access so proceed with specific actions
                        switch( $actionfield ):
                            case 'load_styles':
                                // main child theme setup function
                                $msg = $this->setup_child_theme();
                                break;
                            
                            case 'parnt_templates_submit':
                                // copy parent templates to child
                                if ( isset( $_POST[ 'ctc_file_parnt' ] ) ):
                                    foreach ( $_POST[ 'ctc_file_parnt' ] as $file ):
                                        $this->copy_parent_file( sanitize_text_field( $file ) );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'child_templates_submit':
                                // delete child theme files
                                if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                    if ( in_array( 'functions', $_POST[ 'ctc_file_child' ] ) ):
                                        $this->errors[] = 
                                            __( 'The Functions file is required and cannot be deleted.', 
                                                'chld_thm_cfg' );
                                    else:
                                        foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                            $this->delete_child_file( sanitize_text_field( $file ), 
                                                ( preg_match( "/^style|ctc\-plugins/", $file ) ? 'css' : 'php' ) );
                                        endforeach;
                                        $msg = '8&tab=file_options';
                                    endif;
                                endif;
                                break;
                                
                            case 'image_submit':
                                // delete child theme images
                                if ( isset( $_POST[ 'ctc_img' ] ) ):
                                    foreach ( $_POST[ 'ctc_img' ] as $file ):
                                        $this->delete_child_file( 'images/' . sanitize_text_field( $file ), 'img' );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'templates_writable_submit':
                                // make specific files writable ( systems not running suExec )
                                if ( isset( $_POST[ 'ctc_file_child' ] ) ):
                                    foreach ( $_POST[ 'ctc_file_child' ] as $file ):
                                        $this->set_writable( sanitize_text_field( $file ), 
                                            ( 0 === strpos( $file, 'style' ) ? 'css' : 'php' ) );
                                    endforeach;
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                                
                            case 'set_writable':
                                // make child theme writable ( systems not running suExec )
                                $this->set_writable();
                                $msg = '8&tab=file_options';
                                break;
                            
                            case 'reset_permission':
                                // make child theme read-only ( systems not running suExec )
                                $this->unset_writable();
                                $msg = '8&tab=file_options';
                                break;
                            
                            case 'theme_image_submit':
                                // move uploaded child theme images (now we have filesystem access)
                                if ( isset( $_POST[ 'movefile' ] ) ):
                                    $this->move_file_upload( 'images' );
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                            
                            case 'theme_screenshot_submit':
                                // move uploaded child theme screenshot (now we have filesystem access)
                                if ( isset( $_POST[ 'movefile' ] ) ):
                                    // remove old screenshot
                                    foreach( array_keys( $this->imgmimes ) as $extreg ): 
                                        foreach ( explode( '|', $extreg ) as $ext ):
                                            $this->delete_child_file( 'screenshot', $ext );
                                        endforeach; 
                                    endforeach;
                                    $this->move_file_upload( '' );
                                    $msg = '8&tab=file_options';
                                endif;
                                break;
                            default:
                                // assume we are on the files tab so just redirect there
                                $msg = '8&tab=file_options';
                        endswitch;
                    endif; // end filesystem condition
                endif; // end zip export condition
                if ( empty( $this->errors ) && empty( $this->fs_prompt ) )
                    // no errors so we redirect with confirmation message
                    $this->update_redirect( $msg );
                // otherwise fail gracefully
                $msg = NULL;
                return FALSE;
            endif; // end post validation condition
            // if you end up here you are not welcome
            $msg = NULL;
            $this->errors[] = __( 'You do not have permission to configure child themes.', 'chld_thm_cfg' );
        endif; // end request method condition
        return FALSE;
    }
    
    /***
     * Handle the creation or update of a child theme
     */
    function setup_child_theme() {
        // sanitize and extract config fields into local vars
        foreach ( $this->configfields as $configfield ):
            
            $varparts = explode( '_', $configfield );
            $varname = end( $varparts );
            ${$varname} = empty( $_POST[ 'ctc_' . $configfield ] ) ? '' : 
                sanitize_text_field( $_POST[ 'ctc_' . $configfield ] );
                
        endforeach;
        
        // legacy plugin extension needs parent/child values but this version disables the inputs
        // so get we them from current css object
        if ( !$this->is_theme( $configtype ) && $this->is_legacy() ):
            $parnt  = $this->css->get_prop( 'parnt' );
            $child  = $this->css->get_prop( 'child' );
            $name   = $this->css->get_prop( 'child_name' );
        endif;        
        
        // validate parent and child theme inputs
        if ( $parnt ):
            if ( ! $this->check_theme_exists( $parnt ) ):
                $this->errors[] = sprintf( 
                    __( '%s does not exist. Please select a valid Parent Theme.', 
                        'chld_thm_cfg' ), $parnt );
            endif;
        else:
            $this->errors[] = __( 'Please select a valid Parent Theme.', 'chld_thm_cfg' );
        endif;
        if ( 'existing' == $type && empty( $child ) ):
            $this->errors[] = __( 'Please enter a valid Child Theme directory.', 'chld_thm_cfg' );
        endif;
        if ( empty( $name ) ):
            $name = ucfirst( $child );
        endif;
        if ( FALSE === $this->verify_child_dir( $child ) ):
            $this->errors[] = __( 'Your theme directories are not writable.', 'chld_thm_cfg' );
            add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        endif;
        
        // if this is a shiny brand new child theme certain rules apply
        if ( 'new' == $type ):
            if ( empty( $template ) && empty( $name ) ):
                $this->errors[] = __( 'Please enter a valid Child Theme template name.', 'chld_thm_cfg' );
            else:
                $child = strtolower( preg_replace( "%[^\w\-]%", '', empty( $template ) ? $name : $template ) );
                if ( $this->check_theme_exists( $child ) ):
                    $this->errors[] = sprintf( 
                        __( '<strong>%s</strong> exists. Please enter a different Child Theme template name.', 
                            'chld_thm_cfg' ), $child );
                endif;
            endif;
        endif;
        // if no errors so far, we are good to create child theme
        if ( empty( $this->errors ) ):
            $this->css = new ChildThemeConfiguratorCSS();
            // check if we have additional files from legacy plugin extension. if so, we have to override 
            // function to support wp_filesystem requirements
            if ( $this->is_theme( $configtype ) ):
                // is theme means this is not a plugin stylesheet config
                add_action( 'chld_thm_cfg_addl_files',   array( &$this, 'add_base_files' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files',   array( &$this, 'copy_screenshot' ), 10, 2 );
                add_action( 'chld_thm_cfg_addl_files',   array( &$this, 'enqueue_parent_css' ), 15, 2 );
            elseif( $this->is_legacy() 
                && has_action( 'chld_thm_cfg_addl_files' ) ):
                // backwards compatability for plugins extension < 2.0.0 (before pro)
                // action exists so we have to hijack it to use new filesystem checks
                remove_all_actions( 'chld_thm_cfg_addl_files' );
                add_action( 'chld_thm_cfg_addl_files', array( &$this, 'write_addl_files' ), 10, 2 );
                $this->css->set_prop( 'configtype', $configtype );
            endif;
            //echo 'parnt: ' . $parnt . ' child: ' . $child . LF;
            $this->css->set_prop( 'parnt', $parnt );
            $this->css->set_prop( 'child', $child );
            $this->css->set_prop( 'child_name', $name );
            $this->css->set_prop( 'child_author', $author );
            $this->css->set_prop( 'child_version', strlen( $version ) ? $version : '1.0' );
            
            if ( isset( $_POST[ 'ctc_parent_enqueue' ] ) )
                $this->css->set_prop( 'enqueue', sanitize_text_field( $_POST[ 'ctc_parent_enqueue' ] ) );
            elseif ( !$this->is_theme( $configtype ) )
                $this->css->set_prop( 'enqueue', 'enqueue' );
            
            // hook for add'l plugin files and subdirectories
            do_action( 'chld_thm_cfg_addl_files', $this );
            
            // parse parent stylesheet if theme or legacy plugin extension 
            if ( $this->is_theme( $configtype ) || $this->is_legacy() )
                $this->css->parse_css_file( 'parnt' );
            
            // parse child stylesheet, backup or skip ( to reset )
            $this->css->parse_css_file( 'child', $revert );
            
            // parse additional stylesheets
            if ( isset( $_POST[ 'ctc_additional_css' ] ) && is_array( $_POST[ 'ctc_additional_css' ] ) ):
                $this->css->addl_css = array();
                foreach ( $_POST[ 'ctc_additional_css' ] as $file ):
                    $this->css->parse_css_file( 'parnt', $file );
                    $this->css->addl_css[] = $file;
                endforeach;
            endif;
            
            // try to write new stylsheet. If it fails send alert.
            if ( FALSE === $this->css->write_css( isset( $_POST[ 'ctc_backup' ] ) ) ):
                $this->debug .= 'failed to write' . LF;
                $this->errors[] = __( 'Your stylesheet is not writable.', 'chld_thm_cfg' );
                add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
                return FALSE;
            endif; 
            
            // copy parent theme mods option set
            if ( isset( $_POST[ 'ctc_parent_mods' ] ) ):
                // we can copy settings from parent to child even if neither is currently active
                // so we need cases for active parent, active child or neither
                
                // get active theme
                $active_theme = get_stylesheet();
                // create temp array from parent settings
                $child_mods = get_option( 'theme_mods_' . $parnt );
                if ( $active_theme == $parnt ):
                    // if parent theme is active, get widgets from active sidebars_widgets array
                    $child_widgets = retrieve_widgets();
                else:
                    // otherwise get widgets from parent theme mods
                    $child_widgets = $child_mods[ 'sidebars_widgets' ][ 'data' ];
                endif;
                if ( $active_theme == $child ):
                    // if child theme is active, remove widgets from temp array
                    unset( $child_mods[ 'sidebars_widgets' ] );
                    // copy temp array to child mods
                    update_option( 'theme_mods_' . $child, $child_mods );
                    // copy widgets to active sidebars_widgets array
                    wp_set_sidebars_widgets( $child_widgets );
                else:
                    // otherwise copy widgets to temp array with time stamp
                    $child_mods[ 'sidebars_widgets' ][ 'data' ] = $child_widgets;
                    $child_mods[ 'sidebars_widgets' ][ 'time' ] = time();
                    // copy temp array to child theme mods
                    update_option( 'theme_mods_' . $child, $child_mods );
                endif;
            endif;
            
            // save new object to WP options table
            $this->css->save_config();
            
            // hook for add'l plugin options
            do_action( 'chld_thm_cfg_addl_options', $this ); // hook for add'l plugin options
            
            // return message id 1, which says new child theme created successfull;
            return 1; //isset( $_POST[ 'ctc_scan_subdirs' ] ) ? '9&tab=import_options' : 1;
        endif;
        return FALSE;
    }

    function load_imports() {
        // allows fonts and other externals to be previewed
        // loads early not to conflict with admin stylesheets
        $regex = "/\@import *(url)? *\( *['\"]?((https?:\/\/)?(.+?))['\"]? *\).*$/";
        if ( $imports = $this->css->get_prop( 'imports' ) ):
            $count = 1;
            foreach ( $imports as $import ):
                preg_match( $regex, $import, $matches );
                if ( empty( $matches[ 3 ] ) && !empty( $matches[ 4 ] ) ): // relative filepath
                    $url = get_stylesheet_directory_uri();
                    preg_replace( "#\.\./#", '', $matches[ 4 ], -1, $count );
                    for( $i = 0; $i < $count; $i++ ):
                        $url = dirname( $url );
                    endfor;
                    $import = preg_replace( $regex, '@import url(' . trailingslashit( $url ) . $matches[ 4 ] . ')', $import );
                endif;
                wp_enqueue_style( 'chld-thm-cfg-admin' . ++$count, preg_replace( $regex, "$2", $import ) );
            endforeach;
        endif;
    }

    /*
     * TODO: this is a stub for future use
     */
    function sanitize_options( $input ) {
        return $input;
    }
    
    function update_redirect( $msg = 1 ) {
        if ( empty( $this->is_ajax ) ):
            $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
            wp_safe_redirect(
                ( is_multisite() ? 
                    network_admin_url( 'themes.php' ) :
                    admin_url( 'tools.php' ) ) . '?page=' . $ctcpage . ( $msg ? '&updated=' . $msg : '' )
                );
            die();
        endif;
    }
    
    function verify_child_dir( $path ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $themedir = $wp_filesystem->find_folder( get_theme_root() );
        if ( ! $wp_filesystem->is_writable( $themedir ) ) return FALSE;
        $childparts = explode( '/', $this->normalize_path( $path ) );
        while ( count( $childparts ) ):
            $subdir = array_shift( $childparts );
            if ( empty( $subdir ) ) continue;
            $themedir = trailingslashit( $themedir ) . $subdir;
            if ( ! $wp_filesystem->is_dir( $themedir ) ):
                if ( ! $wp_filesystem->mkdir( $themedir, FS_CHMOD_DIR ) ):
                    return FALSE;
                endif;
            elseif ( ! $wp_filesystem->is_writable( $themedir ) ):
                return FALSE;
            endif;
        endwhile;
        return TRUE;
    }
    
    function add_base_files( $obj ){
        // add functions.php file
        $contents = "<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
";
        if ( FALSE === $this->write_child_file( 'functions.php', $contents ) 
            || FALSE === $this->write_child_file( 'style.css', $this->css->get_css_header() ) ) return FALSE;
    }
    
    function enqueue_parent_code(){
        return explode( "\n", "// AUTO GENERATED - Do not modify or remove comment markers above or below:
        
if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css' ); 
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css' );
" );
    }
    
    function enqueue_parent_css( $obj ) {
        $marker     = 'ENQUEUE PARENT ACTION';
        $insertion  = 'enqueue' == $this->css->enqueue ? $this->enqueue_parent_code() : array();
        if ( $filename   = $this->css->is_file_ok( $this->css->get_child_target( 'functions.php' ), 'write' ) ):
            $this->insert_with_markers( $filename, $marker, $insertion );
        endif;
    }
    
    /**
     * we would have used WP's insert_with_markers function, 
     * but it does not use wp_filesystem API!!!???
     */
    function insert_with_markers( $filename, $marker, $insertion ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        // make sure file exists with php header
        $this->add_base_files( $this );
        global $wp_filesystem;
        if( !$wp_filesystem->exists( $this->fspath( $filename ) ) ):
			$markerdata = FALSE;
		else:
            // get_contents_array returns extra linefeeds so just split it ourself
			$markerdata = explode( "\n", $wp_filesystem->get_contents( $this->fspath( $filename ) ) );
		endif;
        $newfile = '';
		$foundit = false;
		if ( $markerdata ) {
			$state = true;
			foreach ( $markerdata as $n => $markerline ) {
				if ( strpos( $markerline, '// BEGIN ' . $marker ) !== false )
					$state = false;
				if ( $state ):
					if ( $n + 1 < count( $markerdata ) )
						$newfile .= "{$markerline}\n";
					else
						$newfile .= "{$markerline}";
				endif;
				if ( strpos( $markerline, '// END ' . $marker ) !== false ):
					$newfile .= "// BEGIN {$marker}\n";
					if ( is_array( $insertion ) )
						foreach ( $insertion as $insertline )
							$newfile .= "{$insertline}\n";
					$newfile .= "// END {$marker}\n";
					$state = true;
					$foundit = true;
				endif;
			}
		}
		if ( !$foundit ) {
			$newfile .= "\n// BEGIN {$marker}\n";
			foreach ( $insertion as $insertline )
				$newfile .= "{$insertline}\n";
			$newfile .= "// END {$marker}\n";
		}
        if ( FALSE === $wp_filesystem->put_contents( $this->fspath( $filename ), $newfile ) ) return FALSE; 
    }
    
    function write_child_file( $file, $contents ) {
        if ( !$this->fs ): 
            $this->debug( 'no filesystem access' );
            return FALSE; // return if no filesystem access
        endif;
        global $wp_filesystem;
        $file = $this->fspath( $this->css->is_file_ok( $this->css->get_child_target( $file ), 'write' ) );
        //echo 'writing to filesystem: ' . $file . LF;
        if ( $file && !$wp_filesystem->exists( $file ) ):
            if ( FALSE === $wp_filesystem->put_contents( $file, $contents ) ):
                //echo 'filesystem write failed!' . LF;
                return FALSE; 
            endif;
        else:
            //echo 'file exists!' . LF;
            return FALSE;
        endif;
        //echo 'filesystem write successful' . LF;
    }
    
    function copy_screenshot( $obj ) {
        // always copy screenshot
        $this->copy_parent_file( 'screenshot' ); 
    }
    
    function copy_parent_file( $file, $ext = 'php' ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $parent_file = NULL;
        if ( 'screenshot' == $file ):
            foreach ( array_keys( $this->imgmimes ) as $extreg ): 
                foreach( explode( '|', $extreg ) as $ext ):
                    if ( $parent_file = $this->css->is_file_ok( $this->css->get_parent_source( 'screenshot.' . $ext ) ) ) break;
                endforeach; 
                if ( $parent_file ):
                    $parent_file = $this->fspath( $parent_file );
                    break;
                endif;
            endforeach;
        else:
            $parent_file = $this->fspath( $this->css->is_file_ok( $this->css->get_parent_source( $file . '.' . $ext ) ) );
        endif;
        // get child theme + file + ext ( passing empty string and full child path to theme_basename )
        $child_file = $this->css->get_child_target( $file . '.' . $ext );
        // return true if file already exists
        if ( $wp_filesystem->exists( $this->fspath( $child_file ) ) ) return TRUE;
        $child_dir = dirname( $this->theme_basename( '', $child_file ) );
        if ( $parent_file // sanity check
            && $child_file // sanity check
                && $this->verify_child_dir( $child_dir ) //create child subdir if necessary
                    && $wp_filesystem->copy( $parent_file, $this->fspath( $child_file ), FS_CHMOD_FILE ) ) return TRUE;
        $this->errors[] = __( 'Could not copy file.', 'chld_thm_cfg' );
    }
    
    function delete_child_file( $file, $ext = 'php' ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        // verify file is in child theme and exists before removing.
        $file = ( 'img' == $ext ? $file : $file . '.' . $ext );
        $child_file  = $this->fspath( $this->css->is_file_ok( $this->css->get_child_target( $file ), 'write' ) );
        if ( $wp_filesystem->exists( $child_file ) ):
            if ( !$wp_filesystem->delete( $child_file ) ) return FALSE;
        endif;
    }
    
    function get_files( $theme, $type = 'template' ) {
        if ( !isset( $this->files[ $theme ] ) ):
            $this->files[ $theme ] = array();
            $imgext = '(' . implode( '|', array_keys( $this->imgmimes ) ) . ')';
            foreach ( $this->css->recurse_directory(
                trailingslashit( get_theme_root() ) . $theme, '', TRUE ) as $file ):
                $file = $this->theme_basename( $theme, $file );
                if ( preg_match( "/^style\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'backup' ][ $file ] = $date;
                elseif ( preg_match( "/^ctc\-plugins\-(\d+)\.css$/", $file, $matches ) ):
                    $date = date_i18n( 'D, j M Y g:i A', strtotime( $matches[ 1 ] ) );
                    $this->files[ $theme ][ 'pluginbackup' ][ $file ] = $date;
                elseif ( preg_match( "/\.php$/", $file ) ):
                    $this->files[ $theme ][ 'template' ][] = $file;
                elseif ( preg_match( "/\.css$/", $file ) && 'style.css' != $file ):
                    $this->files[ $theme ][ 'stylesheet' ][] = $file;
                elseif ( preg_match( "/^images\/.+?\." . $imgext . "$/", $file ) ):
                    $this->files[ $theme ][ 'img' ][] = $file;
                endif;
            endforeach;
        endif;
        $types = explode(",", $type);
        $files = array();
        foreach ( $types as $type )
            if ( isset( $this->files[ $theme ][ $type ] ) )
                $files = array_merge( $this->files[ $theme ][ $type ], $files );
        return $files;
    }
        
    function theme_basename( $theme, $file ) {
        // if no theme passed, returns theme + file
        $themedir = trailingslashit( get_theme_root() ) . ( '' == $theme ? '' : trailingslashit( $theme ) );
        return preg_replace( '%^' . preg_quote( $themedir ) . '%', '', $file );
    }
    
    function uploads_basename( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $uplarr[ 'basedir' ] );
        return preg_replace( '%^' . preg_quote( $upldir ) . '%', '', $file );
    }
    
    function uploads_fullpath( $file ) {
        $file = $this->normalize_path( $file );
        $uplarr = wp_upload_dir();
        $upldir = trailingslashit( $uplarr[ 'basedir' ] );
        return $upldir . $file;
    }
    
    function serialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = implode( "%%", $_POST[ $field ] );
    }
    
    function unserialize_postarrays() {
        foreach ( $this->postarrays as $field )
            if ( isset( $_POST[ $field ] ) && !is_array( $_POST[ $field ] ) )
                $_POST[ $field ] = explode( "%%", $_POST[ $field ] );
    }
    
    function set_writable( $file = NULL ) {

        $file = isset( $file ) ? $this->css->get_child_target( $file . '.php' ) : 
            apply_filters( 'chld_thm_cfg_target', $this->css->get_child_target(), $this->css );
        if ( $this->fs ): // filesystem access
            global $wp_filesystem;
            if ( $file && $wp_filesystem->chmod( $this->fspath( $file ), 0666 ) ) return;
        endif;
        $this->errors[] = __( 'Could not set write permissions.', 'chld_thm_cfg' );
        add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        return FALSE;
    }
    
    function unset_writable() {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $dir        = untrailingslashit( $this->css->get_child_target( '' ) );
        $child      = $this->theme_basename( '', $dir );
        $newchild   = untrailingslashit( $child ) . '-new';
        $themedir   = trailingslashit( get_theme_root() );
        $fsthemedir = $this->fspath( $themedir );
        // is child theme owned by user? 
        if ( fileowner( $dir ) == fileowner( $themedir ) ):
            $copy   = FALSE;
            $wp_filesystem->chmod( $dir );
            // recursive chmod ( as user )
            // WP_Filesystem RECURSIVE CHMOD IS FLAWED! IT SETS ALL CHILDREN TO PERM OF OUTERMOST DIR
            //if ( $wp_filesystem->chmod( $this->fspath( $dir ), FALSE, TRUE ) ):
            //endif;
        else:
            $copy   = TRUE;
        endif;
        // n -> copy entire folder ( as user )
        $files = $this->css->recurse_directory( $dir, NULL, TRUE );
        $errors = array();
        foreach ( $files as $file ):
            $childfile  = $this->theme_basename( $child, $this->normalize_path( $file ) );
            $newfile    = trailingslashit( $newchild ) . $childfile;
            $childpath  = $fsthemedir . trailingslashit( $child ) . $childfile;
            $newpath    = $fsthemedir . $newfile;
            if ( $copy ):
                if ( $this->verify_child_dir( is_dir( $file ) ? $newfile : dirname( $newfile ) ) ):
                    if ( is_file( $file ) && !$wp_filesystem->copy( $childpath, $newpath ) ):
                        $errors[] = 'could not copy ' . $newpath;
                    endif;
                else:
                    $errors[] = 'invalid dir: ' . $newfile;
                endif;
            else:
                $wp_filesystem->chmod( $this->fspath( $file ) );
            endif;
        endforeach;
        if ( $copy ):
            // verify copy ( as webserver )
            $newfiles = $this->css->recurse_directory( trailingslashit( $themedir ) . $newchild, NULL, TRUE );
            $deleteddirs = $deletedfiles = 0;
            if ( count( $newfiles ) == count( $files ) ):
                // rename old ( as webserver )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child . '-old' ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $child, trailingslashit( $fsthemedir ) . $child . '-old' );
                // rename new ( as user )
                if ( !$wp_filesystem->exists( trailingslashit( $fsthemedir ) . $child ) )
                    $wp_filesystem->move( trailingslashit( $fsthemedir ) . $newchild, trailingslashit( $fsthemedir ) . $child );
                // remove old files ( as webserver )
                $oldfiles = $this->css->recurse_directory( trailingslashit( $themedir ) . $child . '-old', NULL, TRUE );
                array_unshift( $oldfiles, trailingslashit( $themedir ) . $child . '-old' );
                foreach ( array_reverse( $oldfiles ) as $file ):
                    if ( $wp_filesystem->delete( $this->fspath( $file ) ) 
                        || ( is_dir( $file ) && @rmdir( $file ) ) 
                            || ( is_file( $file ) && @unlink( $file ) ) ):
                        $deletedfiles++;
                    endif;
                endforeach;
                if ( $deletedfiles != count( $oldfiles ) ):
                    $errors[] = 'deleted: ' . $deletedfiles . ' != ' . count( $oldfiles ) . ' files';
                endif;
            else:
                $errors[] = 'newfiles != files';
            endif;
        endif;
        if ( count( $errors ) ):
            $this->errors[] = __( 'There were errors while resetting permissions.', 'chld_thm_cfg' ) ;
            add_action( 'admin_notices', array( $this, 'writable_notice' ) ); 	
        endif;
    }
    function set_skip_form() {
        $this->skip_form = TRUE;
    }
    function handle_file_upload( $field, $childdir = NULL, $mimes = NULL ){
        $uploadedfile = $_FILES[ $field ];
        $upload_overrides = array( 
            'test_form' => FALSE,
            'mimes' => ( is_array( $mimes ) ? $mimes : NULL )
        );
        if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        if ( isset( $movefile[ 'error' ] ) ):
            $this->errors[] = $movefile[ 'error' ];
            return FALSE;
        endif;
        $_POST[ 'movefile' ] = $this->uploads_basename( $movefile[ 'file' ] );        
    }
    
    function move_file_upload( $subdir = 'images' ) {
        if ( !$this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        $source_file = sanitize_text_field( $_POST[ 'movefile' ] );
        $target_file = ( '' == $subdir ? 
            preg_replace( "%^.+(\.\w+)$%", "screenshot$1", basename( $source_file ) ) : 
                trailingslashit( $subdir ) . basename( $source_file ) );
        if ( FALSE !== $this->verify_child_dir( trailingslashit( $this->css->get_prop( 'child' ) ) . $subdir ) ):
            $source_path = $this->fspath( $this->uploads_fullpath( $source_file ) );
            if ( $target_path = $this->css->is_file_ok( $this->css->get_child_target( $target_file ), 'write' ) ):
                $target_path = $this->fspath( $target_path );
                if ( $wp_filesystem->exists( $source_path ) ):
                    if ( $wp_filesystem->move( $source_path, $target_path ) ) return TRUE;
                endif;
            endif;
        endif;
        
        $this->errors[] = __( 'Could not upload file.', 'chld_thm_cfg' );        
    }
    
    function export_zip() {
        if ( ( $child = $this->css->get_prop( 'child' ) ) 
            && ( $dir = $this->css->is_file_ok( dirname( $this->css->get_child_target() ), 'search' ) )
            && ( $version = preg_replace( "%[^\w\.\-]%", '', $this->css->get_prop( 'version' ) ) ) ):
            // use php system upload dir to store temp files so that we can use pclzip
            $tmpdir = ini_get( 'upload_tmp_dir' ) ? ini_get( 'upload_tmp_dir' ) : sys_get_temp_dir();
            $file = trailingslashit( $tmpdir ) . $child . '-' . $version . '.zip';
            mbstring_binary_safe_encoding();

            require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

            $archive = new PclZip( $file );
            if ( $archive->create( $dir, PCLZIP_OPT_REMOVE_PATH, dirname( $dir ) ) == 0 ) return FALSE;
        	reset_mbstring_encoding();
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Length: ' . filesize( $file ) );
            header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            readfile( $file );
            unlink( $file );
            die();
        endif;
    }
        
    /*
     *
     */
    function verify_creds( $args = array() ) {
        $this->fs_prompt = $this->fs = FALSE;
        //fs prompt does not support arrays as post data - serialize arrays
        $this->serialize_postarrays();
        // generate callback url
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        $url = is_multisite() ?  network_admin_url( 'themes.php?page=' . $ctcpage ) :
            admin_url( 'tools.php?page=' . $ctcpage );
        $nonce_url = wp_nonce_url( $url, apply_filters( 'chld_thm_cfg_action', 'ctc_update' ), '_wpnonce' );
        // buffer output so we can process prior to http header
        ob_start();
        if ( $creds = request_filesystem_credentials( $nonce_url, '', FALSE, FALSE, $args ) ):
            // check filesystem permission if direct or ftp creds exist
            if ( WP_Filesystem( $creds ) )
                // login ok
                $this->fs = TRUE;
            else
                // incorrect credentials, get form with error flag
                $creds = request_filesystem_credentials( $nonce_url, '', TRUE, FALSE, $args );
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
    function fspath( $file ){
        if ( ! $this->fs ) return FALSE; // return if no filesystem access
        global $wp_filesystem;
        if ( is_dir( $file ) ):
            $dir = $file;
            $base = '';
        else:
            $dir = dirname( $file );
            $base = basename( $file );
        endif;
        $fsdir = $wp_filesystem->find_folder( $dir );
        return trailingslashit( $fsdir ) . $base;
    }
    
    function writable_notice() {
?>    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator is unable to write to the stylesheet. This can be resolved using one of the following options:<ol>', 'chld_thm_cfg' );
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
        if ( isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && preg_match( '%unix%i',$_SERVER[ 'SERVER_SOFTWARE' ] ) ):
            _e( '<li>Temporarily make the stylesheet writable by clicking the button below. You should change this back when you are finished editing for security by clicking "Make read-only" under the "Files" tab.</li>', 'chld_thm_cfg' );
?><form action="?page=<?php echo $ctcpage; ?>" method="post">
    <?php wp_nonce_field( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ); ?>
<input name="ctc_set_writable" class="button" type="submit" value="<?php _e( 'Temporarily make stylesheet writable', 'chld_thm_cfg' ); ?>"/></form><?php   endif;
        _e( '<li><a target="_blank"  href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants" title="Editin wp-config.php">Add your FTP/SSH credentials to the WordPress config file</a>.</li>', 'chld_thm_cfg' );
        if ( isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && preg_match( '%iis%i',$_SERVER[ 'SERVER_SOFTWARE' ] ) )
            _e( '<li><a target="_blank" href="http://technet.microsoft.com/en-us/library/cc771170" title="Setting Application Pool Identity">Assign WordPress to an application pool that has write permissions</a> (Windows IIS systems).</li>', 'chld_thm_cfg' );
        _e( '<li><a target="_blank" href="http://codex.wordpress.org/Changing_File_Permissions" title="Changing File Permissions">Set the stylesheet write permissions on the server manually</a> (not recommended).</li>', 'chld_thm_cfg' );
        if ( isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && preg_match( '%unix%i',$_SERVER[ 'SERVER_SOFTWARE' ] ) )
            _e( '<li>Run PHP under Apache with suEXEC (contact your web host).</li>', 'chld_thm_cfg' ) ?>
        </ol></p>
</div>
    <?php
    }
    function owner_notice() {
        $ctcpage = apply_filters( 'chld_thm_cfg_admin_page', CHLD_THM_CFG_MENU );
    ?>
    <div class="update-nag">
        <p><?php _e( 'This Child Theme is not owned by your website account. It may have been created by a prior version of this plugin or by another program. Moving forward, it must be owned by your website account to make changes. Child Theme Configurator will attempt to correct this when you click the button below.', 'chld_thm_cfg' ) ?></p>
<form action="?page=<?php echo $ctcpage; ?>" method="post">
    <?php wp_nonce_field( apply_filters( 'chld_thm_cfg_action', 'ctc_update' ) ); ?>
<input name="ctc_reset_permission" class="button" type="submit" value="<?php _e( 'Correct Child Theme Permissions', 'chld_thm_cfg' ); ?>"/></form>    </div>
    <?php
    }

    function enqueue_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator needs to update its interal data. Please set your preferences below and click "Generate Child Theme Files" to update your configuration.', 'chld_thm_cfg' ) ?></p>
    </div>
    <?php
    }

    function config_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( 'Child Theme Configurator did not detect any configuration data, either because this is the first time it has been used, or because a previously configured Child Theme has been removed. Please set your preferences below and click "Generate Child Theme Files".', 'chld_thm_cfg' ) ?></p>
    </div>
    <?php
    }

    function render_addl_tabs( $ctc, $active_tab = NULL, $hidechild = '' ) {
        include ( CHLD_THM_CFG_DIR . '/includes/forms/addl_tabs.php' );            
    }

    function render_addl_panels( $ctc, $active_tab = NULL, $hidechild = '' ) {
        include ( CHLD_THM_CFG_DIR . '/includes/forms/addl_panels.php' );            
    }

    // back compatibility function for legacy plugins extension
    function write_addl_files( $obj ) {
        global $chld_thm_cfg_plugins;
        if ( !is_object( $chld_thm_cfg_plugins ) || !$this->fs ) return FALSE;
        $configtype = $this->css->get_prop( 'configtype' );
        //echo $configtype . LF;
        if ( 'theme' == $configtype || !( $def = $chld_thm_cfg_plugins->defs->get_def( $configtype ) ) ) return FALSE;
        $child = trailingslashit( $this->css->get_prop( 'child' ) );
        if ( isset( $def[ 'addl' ] ) && is_array( $def[ 'addl' ] ) && count( $def[ 'addl' ] ) ):
            foreach ( $def[ 'addl' ] as $path => $type ):
            
                // sanitize the crap out of the target data -- it will be used to create paths
                $path = $this->normalize_path( preg_replace( "%[^\w\\//\-]%", '', sanitize_text_field( $child . $path ) ) );
                if ( ( 'dir' == $type && FALSE === $this->verify_child_dir( $path ) )
                    || ( 'dir' != $type && FALSE === $this->write_child_file( $path, '' ) ) ):
                    //$this->errors[] = __( 'Your theme directories are not writable.', 'chld_thm_cfg_plugins' );
                endif;
            endforeach;
        endif;
        // write main def file
        if ( isset( $def[ 'target' ] ) ):
            $path = $this->normalize_path( preg_replace( "%[^\w\\//\-\.]%", '', sanitize_text_field( $def[ 'target' ] ) ) ); //$child . 
            if ( FALSE === $this->write_child_file( $path, '' ) ):
                //echo "invalid path: " . $path . ' ' . ' was: ' . $def[ 'target' ] . LF;
                //$this->errors[] = __( 'Your stylesheet is not writable.', 'chld_thm_cfg_plugins' );
                return FALSE;
            endif;
        endif;        
    }
    // backwards compatability < WP 3.9
    function normalize_path( $path ) {
	    $path = str_replace( '\\', '/', $path );
	    $path = preg_replace( '|/+|','/', $path );
	    return $path;
    }
    
    function check_theme_exists( $theme ) {
        return in_array( $theme, array_keys( wp_get_themes() ) );
    }
    
    // helper functions to support legacy plugin extension
    function is_legacy() {
        return defined('CHLD_THM_CFG_PLUGINS_VERSION') 
            && version_compare( CHLD_THM_CFG_PLUGINS_VERSION, '2.0.0', '<' );
    }
    
    /* not using plugin mode */
    function is_theme( $configtype = '' ) {
        // if filter returns a value, we are using plugin mode
        // otherwise if configtype has a value and it is not a theme then we are in legacy plugin mode
        $pluginmode = apply_filters( 'chld_thm_cfg_action', NULL );
        if ( $pluginmode || ( !empty( $configtype ) && 'theme' != $configtype ) ):
            return FALSE;
        endif;
        if ( $this->is_legacy()
            && is_object( $this->css ) 
                && ( $configtype = $this->css->get_prop( 'configtype' ) ) 
                    && !empty( $configtype ) && 'theme' != $configtype ):
            return FALSE;
        endif;
        return TRUE;
    }
    
    /* returns parent theme either from existing config or passed as post var */
    function get_current_parent() {
        if ( isset( $_GET[ 'ctc_parent' ] ) && ( $parent = sanitize_text_field( $_GET[ 'ctc_parent' ] ) ) )
            return $parent;
        elseif ( $parent = $this->css->get_prop( 'parnt' ) )
            return $parent;
        else return get_template();
    }
    
    /* debug backtrace with extraneous steps (missing class, function or line) removed */
    function backtrace_summary() {
        $bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
        $thisstep = array_shift( $bt );
        foreach ( $bt as $ndx => $step )
            if ( isset( $step[ 'class' ] ) && isset( $step[ 'function' ] ) && isset( $step[ 'line' ] ) )
                echo $ndx . ': ' . $step[ 'class' ] . ' ' . $step[ 'function' ] . ' ' . $step[ 'line' ] . LF;
    }
    
    function debug( $msg = NULL ) {
        $this->debug .= isset( $msg ) ? $msg . LF : '';
    }
}
