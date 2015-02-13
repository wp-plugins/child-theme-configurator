/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.6.5.2
 *  Author: Lilaea Media
 *  Author URI: http://www.lilaeamedia.com/
 *  License: GPLv2
 *  Copyright (C) 2014-2015 Lilaea Media
 */
jQuery( document ).ready( function( $ ) {
    // initialize functions
    function esc_quot( str ) {
        return is_empty( str ) ? str : str.toString().replace( quot_regex, '&quot;' );
    }
    
    function gt( key ){
        return ( text = ctcAjax[ key + '_txt' ] ) ? text : '';
    }
    
    function from_ascii( str ) {
        var ascii = parseInt( str ),
            chr = String.fromCharCode( ascii )
        return chr;
    }
    
    function to_ascii( str ) {
        var ascii = str.charCodeAt( 0 );
        return ascii;
    }
    
    function theme_exists( testslug, testtype ) {
        var exists = false;
        $.each( ctcAjax.themes, function( type, theme ) {
            $.each( theme, function( slug, data ) {
                if ( slug == testslug && ( 'parnt' == type || 'new' == testtype ) ) {
                    exists = true;
                    return false;
                }
            } );
            if ( exists ) return false;
        } );
        return exists;
    }
    
    function validate() {
        var regex   = /[^\w\-]/,
            newslug = $( '#ctc_child_template' ).length ? $( '#ctc_child_template' )
                .val().toString().replace( regex ).toLowerCase() : '',
            slug    = $( '#ctc_theme_child' ).length ? $( '#ctc_theme_child' )
                .val().toString().replace( regex ).toLowerCase() : newslug,
            type    = $( 'input[name=ctc_child_type]:checked' ).val(),
            errors  = [];
        if ( 'new' == type ) slug = newslug;
        if ( theme_exists( slug, type ) ) {
            errors.push( gt( 'theme_exists' ).toString().replace( /%s/, slug ) );
        }
        if ( '' === slug ) {
            errors.push( gt( 'inval_theme' ) );
        }
        if ( '' === $( '#ctc_child_name' ).val() ) {
            errors.push( gt( 'inval_name' ) );
        }
        if ( errors.length ) {
            set_notice( { 'error': errors } );
            return false;
        }
        return true;
    }
    
    function autogen_slugs() {
        if ( $( '#ctc_theme_parnt' ).length ) {
            var parent  = $( '#ctc_theme_parnt' ).val(),
                slug    = slugbase = parent + '-child',
                name    = ctcAjax.themes.parnt[ parent ].Name + ' Child',
                suffix  = '',
                padded  = '',
                pad     = '00';
            while ( theme_exists( slug, 'new' ) ) {
                suffix  = ( '' == suffix ? 2 : suffix + 1 );
                padded  = pad.substring( 0, pad.length - suffix.toString().length ) + suffix.toString();
                slug    = slugbase + padded;
            }
            testslug = slug;
            testname = name + ( padded.length ? ' ' + padded : '' );
        }
    }
    
    function focus_panel( id ) {
        var panelid = id + '_panel';
        $( '.nav-tab' ).removeClass( 'nav-tab-active' );
        $( '.ctc-option-panel' ).removeClass( 'ctc-option-panel-active' );
        $( '.ctc-selector-container' ).hide();
        $( id ).addClass( 'nav-tab-active' );
        $( '.ctc-option-panel-container' ).scrollTop( 0 );
        $( panelid ).addClass( 'ctc-option-panel-active' );
    }
    
    function selector_input_toggle( obj ) {
        console.log( 'selector_input_toggle: ' + obj );
        var origval;
        if ( $( '#ctc_rewrite_selector' ).length ) {
            origval = $( '#ctc_rewrite_selector_orig' ).val();
            $( '#ctc_sel_ovrd_selector_selected' ).text( origval );
            $( obj ).text( gt( 'rename' ) );
        } else {
            origval = $( '#ctc_sel_ovrd_selector_selected' ).text();
            $( '#ctc_sel_ovrd_selector_selected' ).html( 
                '<textarea id="ctc_rewrite_selector"'
                + ' name="ctc_rewrite_selector" autocomplete="off"></textarea>'
                + '<input id="ctc_rewrite_selector_orig" name="ctc_rewrite_selector_orig"'
                + ' type="hidden" value="' + esc_quot( origval ) + '"/>' );
            $( '#ctc_rewrite_selector' ).val( origval );
            $( obj ).text( gt( 'cancel' ) );
        }
    }

    function fade_update_notice() {
        $( '.updated, .error' ).slideUp( 'slow', function() { $( '.updated' ).remove(); } );
    }
    
    function coalesce_inputs( obj ) {
        console.log( 'coalesce_inputs ' + $( obj ).attr( 'id' ) );
        var regex       = /^(ctc_(ovrd|\d+)_(parent|child)_([0-9a-z\-]+)_(\d+))(_\w+)?$/,
            container   = $( obj ).parents( '.ctc-selector-row, .ctc-parent-row' ).first(),
            swatch      = container.find( '.ctc-swatch' ).first(),
            cssrules    = { 'parent': {}, 'child': {} },
            gradient    = { 
                'parent': {
                    'origin':   '',
                    'start':    '',
                    'end':      ''
                }, 
                'child': {
                    'origin':   '',
                    'start':    '',
                    'end':      ''
                } 
            },
            has_gradient    = { 'child': false, 'parent': false },
            postdata        = {};
        // set up objects for all neighboring inputs
        container.find( '.ctc-parent-value, .ctc-child-value' ).each( function() {
            var inputid     = $( this ).attr( 'id' ),
                inputparts  = inputid.toString().match( regex ),
                inputseq    = inputparts[ 2 ],
                inputtheme  = inputparts[ 3 ],
                inputrule   = ( 'undefined' == typeof inputparts[ 4 ] ? '' : inputparts[ 4 ] ),
                qsid        = inputparts[ 5 ],
                rulepart    = ( 'undefined' == typeof inputparts[ 6 ] ? '' : inputparts[ 6 ] ),
                value       = ( 'parent' == inputtheme ? $( this ).text().replace( /!$/, '' ) : $( this ).val() ),
                important   = 'ctc_' + inputseq + '_child_' + inputrule + '_i_' + qsid,
                parts, subparts;
            if ( !is_empty( $( this ).data( 'color' ) ) ) {
                value = $( this ).data( 'color' );
                $( this ).data( 'color', null );
            }
            //console.log( 'id: ' + inputid + ' value: ' + value );
            if ( 'child' == inputtheme ) {
                postdata[ inputid ] = value;
                postdata[ important ] = ( $( '#' + important ).is( ':checked' ) ) ? 1 : 0;
            }
            if ( '' != value ) {
                // handle specific inputs
                if ( !is_empty( rulepart ) ) {
                    //console.log( 'rulepart: ' + rulepart + ' value: ' + value );
                    switch( rulepart ) {
                        case '_border_width':
                            cssrules[ inputtheme ][ inputrule + '-width' ] = ( 'none' == value ? 0 : value );
                            break;
                        case '_border_style':
                            cssrules[ inputtheme ][ inputrule + '-style' ] = value;
                            break;
                        case '_border_color':
                            cssrules[ inputtheme ][ inputrule + '-color' ] = value;
                            break;
                        case '_background_url':
                            cssrules[ inputtheme ][ 'background-image' ] = image_url( inputtheme, value );
                            break;
                        case '_background_color':
                            cssrules[ inputtheme ][ 'background-color' ] = obj.value;
                            break;
                        case '_background_color1':
                            gradient[ inputtheme ].start   = value;
                            has_gradient[ inputtheme ] = true;
                            break;
                        case '_background_color2':
                            gradient[ inputtheme ].end     = value;
                            has_gradient[ inputtheme ] = true;
                            break;
                        case '_background_origin':
                            gradient[ inputtheme ].origin  = value;
                            has_gradient[ inputtheme ] = true;
                            break;
                    }
                } else {
                    // handle borders
                    if ( parts = inputrule.toString().match( /^border(\-(top|right|bottom|left))?$/ ) && !value.match( /none/ ) ) {
                        subparts = value.toString().split( / +/ );
                        cssrules[ inputtheme ][ inputrule + '-width' ] = 'undefined' == typeof subparts[ 0 ] ? '' : subparts[ 0 ];
                        cssrules[ inputtheme ][ inputrule + '-style' ] = 'undefined' == typeof subparts[ 1 ] ? '' : subparts[ 1 ];
                        cssrules[ inputtheme ][ inputrule + '-color' ] = 'undefined' == typeof subparts[ 2 ] ? '' : subparts[ 2 ];
                    // handle background images
                    } else if ( 'background-image' == inputrule && !value.match( /none/ ) ) {
                        if ( value.toString().match( /url\(/ ) ) {
                            cssrules[ inputtheme ][ 'background-image' ] = image_url( inputtheme, value );
                        } else {
                            subparts = value.toString().split( / +/ );
                            if ( subparts.length > 2 ) {
                                gradient[ inputtheme ].origin = 'undefined' == typeof subparts[ 0 ] ? 'top' : subparts[ 0 ];
                                gradient[ inputtheme ].start  = 'undefined' == typeof subparts[ 1 ] ? 'transparent' : subparts[ 1 ];
                                gradient[ inputtheme ].end    = 'undefined' == typeof subparts[ 2 ] ? 'transparent' : subparts[ 2 ];
                                has_gradient[ inputtheme ] = true;
                            } else {
                                cssrules[ inputtheme ][ 'background-image' ] = value;
                            }
                        }
                    } else if ( 'seq' != inputrule ) {
                        cssrules[ inputtheme ][ inputrule ] = value;
                    }
                }
            }
        } );
        // update swatch
        if ( 'undefined' != typeof swatch && !is_empty( swatch.attr( 'id' ) ) ) {
            swatch.removeAttr( 'style' );
            if ( has_gradient.parent ) {
                swatch.ctcgrad( gradient.parent.origin, [ gradient.parent.start, gradient.parent.end ] );
            }
            swatch.css( cssrules.parent );  
            if ( !( swatch.attr( 'id' ).toString().match( /parent/ ) ) ) {
                if ( has_gradient.child ) {
                    swatch.ctcgrad( gradient.child.origin, [ gradient.child.start, gradient.child.end ] );
                }
                swatch.css( cssrules.child );
            }
            swatch.css( {'z-index':-1} );
        }
        return postdata;
    }
    
    function decode_value( rule, value ) {
        value = ( 'undefined' == typeof value ? '' : value );
        var obj = { 'orig':   value };
        if ( rule.toString().match( /^border(\-(top|right|bottom|left))?$/ ) ) {
            var params = value.toString().split( / +/ );
            obj[ 'names' ] = [
                '_border_width',
                '_border_style',
                '_border_color',
            ];
            obj[ 'values' ] = [ 
                ( 'undefined' == typeof params[ 0 ] ? '' : params[ 0 ] ),
                ( 'undefined' == typeof params[ 1 ] ? '' : params[ 1 ] ),
                ( 'undefined' == typeof params[ 2 ] ? '' : params[ 2 ] )
            ];
        } else if ( rule.toString().match( /^background\-image/ ) ) {
            obj[ 'names' ] = [
                '_background_url',
                '_background_origin', 
                '_background_color1', 
                '_background_color2'
            ];
            obj[ 'values' ] = [ '', '', '', '' ];
            if ( false === ( is_empty( value ) ) && !( value.toString().match( /(url|none)/ ) ) ) {
                var params = value.toString().split( /:/ );
                obj[ 'values' ][ 1 ] = ( 'undefined' == typeof params[ 0 ] ? '' : params[ 0 ] );
                obj[ 'values' ][ 2 ] = ( 'undefined' == typeof params[ 1 ] ? '' : params[ 1 ] );
                obj[ 'values' ][ 3 ] = ( 'undefined' == typeof params[ 3 ] ? '' : params[ 3 ] );
                obj[ 'orig' ] = [ 
                    obj[ 'values' ][ 1 ],
                    obj[ 'values' ][ 2 ],
                    obj[ 'values' ][ 3 ] 
                ].join( ' ' );
            } else {
                obj[ 'values' ][ 0 ] = value;
            }
        } else {
            obj[ 'names' ]    = [ '' ];
            obj[ 'values' ]   = [ value ];
        }
        return obj;
    }
    
    function image_url( theme, value ) {
        var parts = value.toString().match( /url\(['" ]*(.+?)['" ]*\)/ ),
            path = is_empty( parts ) ? null : parts[ 1 ],
            url = ctcAjax.theme_uri + '/' + ( 'parent' == theme ? ctcAjax.parnt : ctcAjax.child ) + '/',
            image_url;
        if ( !path ) { 
            return false; 
        } else if ( path.toString().match( /^(data:|https?:|\/)/ ) ) { 
            image_url = value; 
        } else { 
            image_url = 'url(' + url + path + ')'; 
        }
        return image_url;
    }
    
    function is_empty( obj ) {
        // first bail when definitely empty or undefined ( true ) NOTE: numeric zero returns false !
        if ( 'undefined' == typeof obj || false === obj || null === obj || '' === obj ) { return true; }
        // then, if this is bool, string or number it must not be empty ( false )
        if ( true === obj || "string" === typeof obj || "number" === typeof obj ) { return false; }
        // check for object type to be safe
        if ( "object" === typeof obj ) {    
            // Use a standard for in loop
            for ( var x in obj ) {
                // A for in will iterate over members on the prototype
                // chain as well, but Object.getOwnPropertyNames returns
                // only those directly on the object, so use hasOwnProperty.
                if ( obj.hasOwnProperty( x ) ) {
                    // any value means not empty ( false )
                    return false;
                }
            }
            // no properties, so return empty ( true )
            return true;
        } 
        // this must be an unsupported datatype, so return not empty
        return false; 
    
    }

    function setup_menus() {
        console.log( 'setup_menus' );
        setup_query_menu();
        setup_selector_menu();
        setup_rule_menu();
        setup_new_rule_menu();
        load_queries();
        load_rules();
        // selectors will be loaded after query selected
        set_query( current_query );
    }
    
    function load_queries() {
        console.log( 'load_queries' );
        // retrieve unique media queries
        query_css( 'queries', null );
    }
    
    function load_selectors() {
        console.log( 'load_selectors' );
        // retrieve unique selectors from query value
        query_css( 'selectors', current_query );
    }
    
    function load_rules() {
        console.log( 'load_rules' );
        // retrieve all unique rules
        query_css( 'rules', null );
    }
    
    function load_selector_values() {
        console.log( 'load_selector_values: ' + current_qsid );
        // retrieve individual values from qsid
        query_css( 'qsid', current_qsid );
    }
    
    function get_queries( request, response ) {
        console.log( 'get_queries' );
        var arr = [], 
            matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
        // note: key = ndx, value = query name
        $.each( cache_queries, function( key, val ) {
            if ( matcher.test( val ) ) {
                arr.push( { 'label': val, 'value': val } );
            }
        } );
        response( arr );
    }
    
    function get_selectors( request, response ) {
        console.log( 'get_selectors' );
        var arr = [], 
            matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
        // note: key = selector name, value = qsid
        $.each( cache_selectors, function( key, val ) {
            if ( matcher.test( key ) ) {
                arr.push( { 'label': key, 'value': val } );
            }
        } );
        response( arr );
    }
    
    function get_rules( request, response ) {
        console.log( 'get_rules' );
        var arr = [], 
            matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
        // note: key = ruleid, value = rule name
        $.each( cache_rules, function( key, val ) {
            if ( matcher.test( val ) ) {
                arr.push( { 'label': val, 'value': key } );
            }
        } );
        response( sort_object( arr ) );
    }
    
    function sort_object( arr ) {
        return arr.sort( function ( a, b ) {
            if ( a.label > b.label )
                return 1;
            if ( a.label < b.label )
                return -1;
            return 0;
        } );    
    }
    
    function get_filtered_rules( request, response ) {
        console.log( 'get_filtered_rules' );
        var arr = [],
            matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" ),
            noval = ( is_empty( current_qsdata ) ) || ( is_empty( current_qsdata.value ) );
        if ( is_empty( cache_rules ) ) { 
            load_rules();
        }
        $.each( cache_rules, function( key, val ) {
            var skip = false;
            if ( matcher.test( val ) ) {
                if ( false === noval ) {
                    // skip rule if in current selector array
                    $.each( current_qsdata.value, function( rule, value ) {
                        if ( val == rule.replace( /\d+/g, from_ascii ) ) {
                            skip = true;
                            return false;
                        }
                    } );
                    if ( skip ) {
                        return;
                    }
                }
                arr.push( { 'label': val, 'value': key } );
            }
        } );
        response( sort_object( arr ) );
    }
    
    /**
     * The "render" functions inject html into the DOM based on the JSON result of Ajax actions
     */
    function render_child_rule_input( qsid, rule, seq, data ) {
        console.log( 'render_child_rule_input: ' + qsid + ' rule: ' + rule + ' seq: ' + seq );
        var html = '', 
            value = ( is_empty( data ) || is_empty( data.value ) || is_empty( data.value[ rule ] ) ?
                '' : data.value[ rule ] ),
            parentObj = decode_value( rule, ( is_empty( value ) ?
                '' : value.parnt ) ),
            parentImp = ( !is_empty( value ) && !is_empty( value.i_parnt ) && 1 == value.i_parnt ) ? 
                gt( 'important' ) : '',
            childObj = decode_value( rule, ( is_empty( value ) ?
                '' : value[ 'child' ] ) ),
            childImp = ( !is_empty( value ) && !is_empty( value.i_child ) && 1 == value.i_child ) ? 1 : 0,
            impid = 'ctc_' + seq + '_child_' + rule + '_i_' + qsid;
        if ( !is_empty( data ) ) {
            html += '<div class="ctc-' + ( 'ovrd' == seq ? 'input' : 'selector' ) 
                + '-row clearfix">' + lf + '<div class="ctc-input-cell">'
                + ( 'ovrd' == seq ? rule.replace( /\d+/g, from_ascii ) : 
                    data.selector + '<br/><a href="#" class="ctc-selector-edit"'
                + ' id="ctc_selector_edit_' + qsid + '" >' + gt( 'edit' ) + '</a> '
                + ( is_empty( parentObj.orig ) ? gt( 'child_only' ) : '' ) ) 
                + '</div>' + lf;
            if ( 'ovrd' == seq ) {
                html += '<div class="ctc-parent-value ctc-input-cell"'
                    + ' id="ctc_' + seq + '_parent_' + rule + '_' + qsid + '">' 
                    + ( is_empty( parentObj.orig ) ? '[no value]' : parentObj.orig + parentImp ) 
                    + '</div>' + lf;
            }
            html += '<div class="ctc-input-cell">' + lf;
            if ( !is_empty( parentObj.names ) ) {
                $.each( parentObj.names, function( ndx, newname ) {
                    newname = ( is_empty( newname ) ? '' : newname );
                    html += '<div class="ctc-child-input-cell">' + lf;
                    var id = 'ctc_' + seq + '_child_' + rule + '_' + qsid + newname,
                        newval;
                    if ( false === ( newval = childObj.values.shift() ) ) {
                        newval = '';
                    }
                        
                    html += ( is_empty( newname ) ? '' : ctcAjax.field_labels[ newname ] + ':<br/>' ) 
                        + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                        + ( ( newname + rule ).toString().match( /color/ ) ? ' color-picker' : '' ) 
                        + ( ( newname ).toString().match( /url/ ) ? ' ctc-input-wide' : '' )
                        + '" value="' + esc_quot( newval ) + '" />' + lf + '</div>' + lf;
                } );
                html += '<label for="' + impid + '"><input type="checkbox"'
                    + ' id="' + impid + '" name="' + impid + '" value="1" '
                    + ( 1 === childImp ? 'checked' : '' ) + ' />' 
                    + gt( 'important' ) + '</label>' + lf;
            }
            html += '</div>' + lf + ( 'ovrd' == seq ? '' : 
                '<div class="ctc-swatch ctc-specific"'
                + ' id="ctc_child_' + rule + '_' + qsid + '_swatch">' 
                + gt( 'swatch' ) + '</div>' + lf 
                + '<div class="ctc-child-input-cell ctc-button-cell"'
                + ' id="ctc_save_' + rule + '_' + qsid + '_cell">' + lf
                + '<input type="button" class="button ctc-save-input"'
                + ' id="ctc_save_' + rule + '_' + qsid + '"'
                + ' name="ctc_save_' + rule + '_' + qsid + '"'
                + ' value="Save" /></div>' + lf )
                + '</div><!-- end input row -->' + lf;
            //console.log( html );
        } else {
            //console.log( 'sel_val ' + qsid + ' is empty' );
        }
        return html;
    }
    
    function render_selector_inputs() {
        console.log( 'render_selector_inputs: ' + qsid );
        var qsid = current_qsid,
            data = current_qsdata,
            id, html, val, selector;
            $( '#ctc_sel_ovrd_qsid' ).val( qsid );
            current_qsid = qsid;
            if ( is_empty( data.seq ) ) {
                $( '#ctc_child_load_order_container' ).html( '' );
            } else {
                id = 'ctc_ovrd_child_seq_' + qsid;
                val = parseInt( data.seq );
                html = '<input type="text" id="' + id + '" name="' + id + '"'
                    + ' class="ctc-child-value" value="' + val + '" />';
                $( '#ctc_child_load_order_container' ).html( html );
            }
            if ( is_empty( data.value ) ) {
                $( '#ctc_sel_ovrd_selector_selected' ).text( '' );
                $( '.ctc-rewrite-toggle' ).text( '' );
                $( '#ctc_sel_ovrd_new_rule,'
                    + '#ctc_sel_ovrd_rule_header,'
                    + '#ctc_sel_ovrd_rule_inputs_container,'
                    + '#ctc_sel_ovrd_rule_inputs,'
                    + '.ctc-rewrite-toggle' ).hide();
                $( '#ctc_sel_ovrd_rule_inputs' ).slideUp( function(){ 
                    $( '#ctc_sel_ovrd_rule_inputs' ).html( '' ); 
                } );
            } else {
                html = '';
                $.each( data.value, function( rule, value ) {
                    html += render_child_rule_input( qsid, rule, 'ovrd', data );
                } );
                $( '#ctc_sel_ovrd_rule_inputs' ).html( html ).find( '.color-picker' ).each( function() {
                    setup_iris( this );
                } );
                coalesce_inputs( '#ctc_child_all_0_swatch' );
                if ( jquery_err.length ) {
                    jquery_notice();
                } else {
                    console.log( 'reload menus: ' + ( reload_menus ? 'true' : 'false' ) );
                    if ( reload_menus ) {
                        set_query( data.query );
                        load_rules();
                    }
                    $( '#ctc_sel_ovrd_selector_selected' ).text( data.selector );
                    $( '.ctc-rewrite-toggle' ).text( gt( 'rename' ) );
                    $( '#ctc_sel_ovrd_new_rule,'
                        + '#ctc_sel_ovrd_rule_header,'
                        + '#ctc_sel_ovrd_rule_inputs_container,'
                        + '#ctc_sel_ovrd_rule_inputs,'
                        + '.ctc-rewrite-toggle' ).show();
                }
            }
    }
    
    function render_css_preview( theme ) {
        console.log( 'render_css_preview: ' + theme );
        var theme;
        if ( !( theme = theme.match( /(child|parnt)/ )[ 1 ] ) ) {
            theme = 'child';
        }
        // retrieve raw stylesheet ( parent or child )
        query_css( 'preview', theme );
    }
    
    function render_rule_value_inputs( ruleid, data ) {
        console.log( 'render_rule_value_inputs: ' + ruleid );
        console.log( data );
        var rule = cache_rules[ ruleid ], 
            html = '<div class="ctc-input-row clearfix" id="ctc_rule_row_' + rule + '">' + lf;
        console.log( 'rule: ' + rule );
        if ( !is_empty( data ) ) {
            $.each( data, function( valid, value ) {
                var parentObj = decode_value( rule, value );
                html += '<div class="ctc-parent-row clearfix"'
                    + ' id="ctc_rule_row_' + rule + '_' + valid + '">' + lf
                    + '<div class="ctc-input-cell ctc-parent-value"'
                    + ' id="ctc_' + valid + '_parent_' + rule + '_' + valid + '">' 
                    + parentObj.orig + '</div>' + lf
                    + '<div class="ctc-input-cell">' + lf
                    + '<div class="ctc-swatch ctc-specific"'
                    + ' id="ctc_' + valid + '_parent_' + rule + '_' + valid + '_swatch">' 
                    + gt( 'swatch' ) + '</div></div>' + lf
                    + '<div class="ctc-input-cell">'
                    + '<a href="#" class="ctc-selector-handle"'
                    + ' id="ctc_selector_' + rule + '_' + valid + '">'
                    + gt( 'selector' ) + '</a></div>' + lf
                    + '<div id="ctc_selector_' + rule + '_' + valid + '_container"'
                    + ' class="ctc-selector-container">' + lf
                    + '<a href="#" id="ctc_selector_' + rule + '_' + valid + '_close"'
                    + ' class="ctc-selector-handle ctc-exit" title="' 
                    + gt( 'close' ) + '"></a>'
                    + '<div id="ctc_selector_' + rule + '_' + valid + '_inner_container"'
                    + ' class="ctc-selector-inner-container clearfix">' + lf
                    + '<div id="ctc_status_val_qry_' + valid + '"></div>' + lf
                    + '<div id="ctc_selector_' + rule + '_' + valid + '_rows"></div>' + lf
                    + '</div></div></div>' + lf;
            } );
            html += '</div>' + lf;
        }
        $( '#ctc_rule_value_inputs' ).html( html ).find( '.ctc-swatch' ).each( function() {
            coalesce_inputs( this );
        } );
    }
    
    function render_recent( recent ) {
        console.log( 'render_recent' );
        var html = '';
        if ( is_empty( recent ) && !is_empty( gt( 'recent' ) ) ) {
            html += gt( 'recent' );
        } else if ( is_empty( recent ) ) {
            return;
        } else {
            html += '<ul>' + lf;
            $.each( recent, function( ndx, el ) {
                $.each( el, function ( key, value ) {
                    html += '<li><a href="#" class="ctc-selector-edit" id="ctc_selector_edit_' + key + '" >' + value + '</a></li>' + lf;
                } );
            } );
            html += '</ul>' + lf;
        }
        $( '#ctc_recent_selectors' ).html( html );
    }
    
    function render_selector_value_inputs( valid, data ) {
        console.log( 'render_selector_value_inputs: ' + valid );
        var html = '';
        if ( !is_empty( data ) ) {
            $.each( data, function( rule, queries ) {
                page_rule = rule;
                $.each( queries, function( query, selectors ) {
                    html += '<h4 class="ctc-query-heading">' + query + '</h4>' + lf;
                    if ( !is_empty( selectors ) ) {
                        $.each( selectors, function( qsid, qsdata ) {
                            html += render_child_rule_input( qsid, rule, valid, qsdata );
                        } );
                    }
                } );
            } );
        }
        selector = '#ctc_selector_' + rule + '_' + valid + '_rows';
        $( selector ).html( html ).find( '.color-picker' ).each( function() {
            setup_iris( this );
        } );
        $( selector ).find( '.ctc-swatch' ).each( function() {
            coalesce_inputs( this );
        } );
        if ( jquery_err.length ) jquery_notice();

    }
    /**
     * The "setup" functions initialize jQuery UI widgets
     */
    function setup_iris( obj ) {
        try {
            $( obj ).iris( {
                change: function( e, ui ) {

                    $( obj ).data( 'color', ui.color.toString() );
                    coalesce_inputs( obj );
                }
            } );
        } catch ( exn ) {
            jquery_exception( exn, 'Iris Color Picker' );
        }
    }
    
    function setup_query_menu() {
        console.log( 'setup_query_menu' );
        try {
            $( '#ctc_sel_ovrd_query' ).autocomplete( {
                source: get_queries,
                minLength: 0,
                selectFirst: true,
                autoFocus: true,
                select: function( e, ui ) {
                    set_query( ui.item.value );
                    return false;
                },
                focus: function( e ) { 
                    e.preventDefault(); 
                }
            } );
        } catch ( exn ) {
            jquery_exception( exn, 'Query Menu' );
        }
    }
    
    function setup_selector_menu() {
        console.log( 'setup_selector_menu' );
        try {
            $( '#ctc_sel_ovrd_selector' ).autocomplete( {
                source: get_selectors,
                selectFirst: true,
                autoFocus: true,
                select: function( e, ui ) {
                    set_selector( ui.item.value, ui.item.label );
                    return false;
                },
                focus: function( e ) { 
                    e.preventDefault(); 
                }
            } );
        } catch ( exn ) {
            jquery_exception( exn, 'Selector Menu' );
        }
    }
    
    function setup_rule_menu() {
        console.log( 'setup_rule_menu' );
        try {
        $( '#ctc_rule_menu' ).autocomplete( {
            source: get_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function( e, ui ) {
                set_rule( ui.item.value, ui.item.label );
                return false;
            },
            focus: function( e ) { 
                e.preventDefault(); 
            }
        } );
        } catch ( exn ) {
            jquery_exception( exn, 'Rule Menu' );
        }
    }
    
    function setup_new_rule_menu() {
        try {
        $( '#ctc_new_rule_menu' ).autocomplete( {
            source: get_filtered_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function( e, ui ) {
                e.preventDefault();
                if ( is_empty( current_qsdata.value ) ) {
                    current_qsdata[ 'value' ] = {};
                }
                current_qsdata.value[ ui.item.label ] = {'child': ''};
                var newrule = ui.item.label.replace( /[^\w\-]/g, to_ascii ),
                    n = $( render_child_rule_input( current_qsid, newrule, 'ovrd', current_qsdata ) );
                $( '#ctc_sel_ovrd_rule_inputs' ).append( n );
                $( '#ctc_new_rule_menu' ).val( '' );
                
                n.find( 'input[type="text"]' ).each( function( ndx, el ) {
                    if ( $( el ).hasClass( 'color-picker' ) )
                        setup_iris( el );
                    $( el ).focus();
                } );
                if ( jquery_err.length ) jquery_notice();
                return false;
            },
            focus: function( e ) { 
                e.preventDefault(); 
            }
        } );
        } catch ( exn ) {
            jquery_exception( exn, 'New Rule Menu' );
        }
    }
    
    /**
     * The "set" functions apply values to inputs
     */
    function set_existing() {
        if ( $( '#ctc_theme_child' ).length && $( '#ctc_child_type_existing' ).is( ':checked' ) ) {
            var child   = $( '#ctc_theme_child' ).val();
            if ( !is_empty( child ) ) {
                $( '#ctc_child_name' ).val( ctcAjax.themes[ 'child' ][ child ].Name );
                $( '#ctc_child_author' ).val( ctcAjax.themes[ 'child' ][ child ].Author );
                $( '#ctc_child_version' ).val( ctcAjax.themes[ 'child' ][ child ].Version );
            }
        }
    }
    
    function set_notice( noticearr ) {
        var errorHtml = '';
        if ( !is_empty( noticearr ) ) {
            $.each( noticearr, function( type, list ) {
                errorHtml += '<div class="' + type + '"><ul>' + lf;
                $( list ).each( function( ndx, el ) {
                    errorHtml += '<li>' + el.toString() + '</li>' + lf;
                } );
                errorHtml += '</ul></div>';        
            } );
        }
        $( '#ctc_error_notice' ).html( errorHtml );
        $('html, body').animate({ scrollTop: 0 }, 'slow');        
    }
    
    function set_parent_menu( obj ) {
        $( '#ctc_theme_parent' ).parents( '.ctc-input-row' ).first()
            .append( '<span class="ctc-status-icon spinner"></span>' );
        $( '.spinner' ).show();
        document.location='?page=' + ctcAjax.page + '&ctc_parent=' + obj.value;
    }
    
    function set_child_menu( obj ) {
        if ( !is_empty( ctcAjax.themes.child[ obj.value ] ) ) {
            $( '#ctc_child_name' ).val( ctcAjax.themes.child[ obj.value ].Name );
            $( '#ctc_child_author' ).val( ctcAjax.themes.child[ obj.value ].Author );
            $( '#ctc_child_version' ).val( ctcAjax.themes.child[ obj.value ].Version );
        }
    }
    
    function set_query( value ) {
        console.log( 'set_query: ' + value );
        current_query = value;
        $( '#ctc_sel_ovrd_query' ).val( '' );
        $( '#ctc_sel_ovrd_query_selected' ).text( value );
        $( '#ctc_sel_ovrd_selector' ).val( '' );
        $( '#ctc_sel_ovrd_selector_selected' ).html( '&nbsp;' );
        //$( '#ctc_sel_ovrd_rule_inputs' ).html( '' );
        load_selectors();
    }
    
    function set_selector( value, label ) {
        console.log( 'set_selector: ' + value + ' label: ' + label );
        $( '#ctc_sel_ovrd_selector' ).val( '' );
        current_qsid = value;
        reload_menus = false;
        load_selector_values();
    }
    
    function set_rule( value, label ) {
        console.log( 'set_rule: ' + value + ' label: ' + label );
        $( '#ctc_rule_menu' ).val( '' );
        $( '#ctc_rule_menu_selected' ).text( label );
        $( '.ctc-rewrite-toggle' ).text( gt( 'rename' ) );
        $( '#ctc_rule_value_inputs, #ctc_input_row_rule_header' ).show();
        // retrieve unique values by rule
        query_css( 'rule_val', value );
    }
    
    function set_qsid( obj ) {
        console.log( 'set_qsid: ' + $( obj ).attr( 'id' ) );
        current_qsid = $( obj ).attr( 'id' ).match( /_(\d+)$/ )[ 1 ];
        focus_panel( '#query_selector_options' );
        reload_menus = true;
        load_selector_values();  
    }
    
    /**
     * slurp website home page and parse header for linked stylesheets
     * set these to be parsed as "default" stylesheets
     */
    function set_addl_css() { 
        console.log( 'set_addl_css' );
        var template    = $( '#ctc_theme_parnt' ).val(),
            theme_uri   = ctcAjax.theme_uri.replace( /^https?:\/\//, '' ),
            homeurl     = ctcAjax.homeurl.replace( /^https?/, ctcAjax.ssl ? 'https' : 'http' ),
            url         = homeurl + '?preview=1&p=x&template=' + template + '&stylesheet=' + template,
            regex       = new RegExp( "<link rel=[\"']stylesheet[\"'][^>]+?" 
                + theme_uri + '/' + template + '/(.+?\\.css)[^>]+?>', 'g' ),
            additional;
        if ( is_empty( template ) ) return;
        //console.log( template );
        if ( template != ctcAjax.parnt ) {
            $.get( url, function( data ) {
                console.log( data );
                while ( additional = regex.exec( data ) ) {
                    console.log( additional );
                    if ( 'style.css' == additional[ 1 ] ) break; // bail after main stylesheet
                    if ( additional[ 1 ].match( /bootstrap/ ) ) continue; // don't autoselect Bootstrap stylesheets
                    $( '.ctc_checkbox' ).each( function( ndx, el ) {
                        if ( $( this ).val() == additional[ 1 ] ) $( this ).prop( 'checked', true );
                    } );
                }
                data = null; // send page to garbage
            } );
        } else {
            console.log('existing... using addl_css array');
            $( ctcAjax.addl_css ).each( function( ndx, el ) {
                $( '#ctc_stylesheet_files .ctc_checkbox' ).each( function( index, elem ) {
                    if ( $( this ).val() == el ) $( this ).prop( 'checked', true );
                } );
            } );
        }
    }
    
    /**
     * Retrieve data from server and execute callback on completion
     * Previously set semaphores control the callback behavior
     */
    function query_css( obj, key, params ) {
        console.log( 'query_css: ' + obj + ' key: ' + key );
        var postdata = { 'ctc_query_obj' : obj, 'ctc_query_key': key },
            status_sel = '#ctc_status_' + obj + ( 'val_qry' == obj ? '_' + key : '' );
        
        if ( 'object' === typeof params ) {
            $.each( params, function( key, val ) {
                postdata[ 'ctc_query_' + key ] = val;
            } );
        }
        $( '.query-icon' ).remove();
        $( status_sel ).append( '<span class="ctc-status-icon spinner query-icon"></span>' );
        $( '.spinner' ).show();
        // add wp ajax action to array
        //console.log( $( '#ctc_action' ).val() );
        postdata[ 'action' ] = ( !is_empty( $( '#ctc_action' ).val() ) 
            && 'plugin' == $( '#ctc_action' ).val() ) ? 
                'ctc_plgqry' : 'ctc_query';
        postdata[ '_wpnonce' ] = $( '#_wpnonce' ).val();
        // ajax post input data
        //console.log( 'query_css postdata:' );
        //console.log( postdata );
        ajax_post( obj, postdata );
    }
    /**
     * Post data to server for saving and execute callback on completion
     * Previously set semaphores control the callback behavior
     */
    function save( obj ) {
        console.log( 'save: ' + $( obj ).attr( 'id' ) );
        var url = ctcAjax.ajaxurl,  // get ajax url from localized object
            postdata = {},
            $selector, $query, $imports, $rule,
            id = $( obj ).attr( 'id' ), newsel, origsel;

        // disable the button until ajax returns
        $( obj ).prop( 'disabled', true );
        // clear previous success/fail icons
        $( '.save-icon' ).remove();
        // show spinner
        $( obj ).parent( '.ctc-textarea-button-cell, .ctc-button-cell' )
            .append( '<span class="ctc-status-icon spinner save-icon"></span>' );
        if ( id.match( /ctc_configtype/ ) ) {
            $( obj ).parents( '.ctc-input-row' ).first()
                .append( '<span class="ctc-status-icon spinner save-icon"></span>' );
            postdata[ 'ctc_configtype' ] = $( obj ).val();
        } else if ( ( $selector = $( '#ctc_new_selectors' ) ) 
            && 'ctc_save_new_selectors' == $( obj ).attr( 'id' ) ) {
            postdata[ 'ctc_new_selectors' ] = $selector.val();
            if ( $query = $( '#ctc_sel_ovrd_query_selected' ) ) {
                postdata[ 'ctc_sel_ovrd_query' ] = $query.text();
            }
            reload_menus = true;
        } else if ( ( $imports = $( '#ctc_child_imports' ) ) 
            && 'ctc_save_imports' == $( obj ).attr( 'id' ) ) {
            postdata[ 'ctc_child_imports' ] = $imports.val();
        } else if ( 'ctc_is_debug' == $( obj ).attr( 'id' ) ) {
            postdata[ 'ctc_is_debug' ] = $( '#ctc_is_debug' ).is( ':checked' ) ? 1 : 0;
        } else {
            // coalesce inputs
            postdata = coalesce_inputs( obj );
        }
        $( '.save-icon' ).show();
        // add rename selector value if it exists
        $( '#ctc_sel_ovrd_selector_selected' )
            .find( '#ctc_rewrite_selector' ).each( function() {
            newsel = $( '#ctc_rewrite_selector' ).val();
            origsel = $( '#ctc_rewrite_selector_orig' ).val();
            if ( is_empty( newsel ) || !newsel.toString().match( /\w/ ) ) {
                newsel = origsel;
            } else {
                postdata[ 'ctc_rewrite_selector' ] = newsel;
                reload_menus = true;
            }
            $( '.ctc-rewrite-toggle' ).text( gt( 'rename' ) );
            $( '#ctc_sel_ovrd_selector_selected' ).html( newsel );
        } );
        // add wp ajax action to array
        //console.log( $( '#ctc_action' ).val() );
        postdata[ 'action' ] = ( !is_empty( $( '#ctc_action' ).val() ) 
            && 'plugin' == $( '#ctc_action' ).val() ) ? 
                'ctc_plugin' : 'ctc_update';
        postdata[ '_wpnonce' ] = $( '#_wpnonce' ).val();
        // console.log( postdata );
        // ajax post input data
        ajax_post( 'qsid', postdata );
    }
    
    function ajax_post( obj, data ) {
        console.log( 'ajax_post: ' + obj );
        console.log( data );
        var url = ctcAjax.ajaxurl;  // get ajax url from localized object
        $.ajax( { 
            url:        url,  
            data:       data,
            dataType:   'json',
            type:       'POST'
        } ).done( function( response ) {
            handle_success( obj, response );
        } ).fail( function() {
            handle_failure( obj );
        } );  
    }
    
    function handle_failure( obj ) {
        console.log( 'handle_failure: ' + obj );
        $( '.query-icon, .save-icon' ).removeClass( 'spinner' );
        // FIXME: need distinction between query and save here
        $( '.query-icon' ).addClass( 'failure' );
        $( '.save-icon' ).addClass( 'failure' );
        $( 'input[type=submit], input[type=button]' ).prop( 'disabled', false );
        $( '.ajax-pending' ).removeClass( 'ajax-pending' );
        //FIXME: return fail text in ajax response
        if ( 'preview' == obj )
            $( '#view_parnt_options_panel,#view_child_options_panel' )
                .text( gt( 'css_fail' ) );
    }
    
    function handle_success( obj, response ) {
        // query response
        console.log( 'handle_success: ' + obj );
        console.log( response );
        // hide spinner
        $( '.query-icon, .save-icon' ).removeClass( 'spinner' );
        $( '.ajax-pending' ).removeClass( 'ajax-pending' );
        // hide spinner
        if ( is_empty( response ) ) {
            handle_failure( obj );
        } else {
            $( '#ctc_new_selectors' ).val( '' );
            // update data objects   
            // show check mark
            // FIXME: distinction between save and query, update specific status icon
            $( '.ctc-status-icon' ).addClass( 'success' );
            $( '.save-icon' ).addClass( 'success' );
            $( 'input[type=submit], input[type=button]' ).prop( 'disabled', false );
            // update data objects   
            $( response ).each( function() {
                switch ( this.obj ) {
                  case 'qsid':
                      current_qsid  = this.key;
                      current_qsdata = this.data;
                      render_selector_inputs();
                      break; 
                  case 'rule_val':
                      render_rule_value_inputs( this.key, this.data );
                      break;
                  case 'val_qry':
                      render_selector_value_inputs( this.key, this.data );
                      break;
                  case 'queries':
                      cache_queries = this.data;
                      break;
                  case 'selectors':
                      cache_selectors = this.data;
                      break;
                  case 'rules':
                      cache_rules = this.data;
                      break;
                  case 'recent':
                      render_recent( this.data );
                      break;
                  case 'debug':
                      $( '#ctc_debug_container' ).html( this.data );
                      break;
                  case 'preview':
                      $( '#view_' + this.key + '_options_panel' ).text( this.data );
                }
            } );
        }
    }
    
    function jquery_exception( exn, type ) {
        var ln = is_empty( exn.lineNumber ) ? '' : ' line: ' + exn.lineNumber,
            fn = is_empty( exn.fileName ) ? '' : ' ' + exn.fileName.split( /\?/ )[ 0 ];
        jquery_err.push( '<code><small>' + type + ': ' + exn.message + fn + ln + '</small></code>' );
    }
    
    function jquery_notice() {
        
        var culprits    = [],
            errors      = [];
        // disable form submits
        $( 'input[type=submit], input[type=button]' ).prop( 'disabled', true );
        $( 'script' ).each( function( ndx,el ){
            var url = $( this ).prop( 'src' );
            if ( !is_empty( url ) && url.match( /jquery(\.min|\.js|\-?ui)/i ) 
                && ! url.match( /load\-scripts.php/ ) ) {
                culprits.push( '<code><small>' + url.split( /\?/ )[ 0 ] + '</small></code>' );
            }
        } );
        errors.push( '<strong>' + gt( 'js' ) + '</strong>' );
        if ( 1 == ctcAjax.is_debug ) {
            errors.push( jquery_err.join( '<br/>' ) );
        }
        if ( culprits.length ) {
            errors.push( gt( 'jquery' ) + '<br/>' + culprits.join( '<br/>' ) );
        }
        errors.push( gt( 'plugin' ) + ' ' + gt( 'contact' ) );
        set_notice( { 'error': errors } );
    }
    // initialize vars
    console.log( 'initializing ctc vars' );
    var lf              = "\n", 
        quot_regex      = new RegExp( '"', 'g' ),
        testslug        = '',
        testname        = '',
        reload_menus    = false,
        current_query   = 'base',
        current_qsid    = null,
        current_qsdata  = {},
        // these caches are used as the source for autocomplete menus
        cache_selectors = {},
        cache_queries   = {},
        cache_rules     = {},
        jquery_err      = [];
    // -- end var definitions
    
    // auto populate parent/child tab values
    autogen_slugs();
    set_existing();
    // initialize theme menus
    if ( !$( '#ctc_theme_parnt' ).is( 'input' ) ) {
        try {
            $.widget( 'ctc.themeMenu', $.ui.selectmenu, {
                _renderItem: function( ul, item ) {
                    var li = $( "<li>" );
                    $( '#ctc_theme_option_' + item.value )
                        .detach().appendTo( li );
                    return li.appendTo( ul );
                }    
            } );
        } catch( exn ) {
            jquery_exception( exn, 'Theme Menu' );
        }
        try {
            $( '#ctc_theme_parnt' ).themeMenu( {
                select: function( event, ui ) {
                    set_parent_menu( ui.item );
                }
            } );
        } catch( exn ) {
            if ( 'function' == typeof themeMenu )
                $( '#ctc_theme_parnt' ).themeMenu( 'destroy' );
            else $( '#ctc_theme_parnt-button' ).remove();
            jquery_exception( exn, 'Parent Theme Menu' );
        }
        if ( is_empty( ctcAjax.themes.child ) ) {
            if ( $( '#ctc_child_name' ).length ) {
                $( '#ctc_child_name' ).val( testname );
                $( '#ctc_child_template' ).val( testslug );
            }
        } else {
            try {
                $( '#ctc_theme_child' ).themeMenu( {
                    select: function( event, ui ) {
                        set_child_menu( ui.item );
                    }
                } );
            } catch( exn ) {
                if ( 'function' == typeof themeMenu )
                    $( '#ctc_theme_child' ).themeMenu( 'destroy' );
                else $( '#ctc_theme_child-button' ).remove();
                jquery_exception( exn, 'Child Theme Menu' );
            }
        }
    }
    if ( is_empty( jquery_err ) ){
        // bind event handlers
        // these elements get replaced so use delegated events
        $( '#ctc_main' ).on( 'focus', '.color-picker', function() { //'.ctc-option-panel-container'
            //set_notice( '' )
            try {
                $( '.color-picker' ).not( this ).iris( 'hide' );
                $( this ).iris( 'toggle' );
                $( '.iris-picker' ).css( {'position':'absolute', 'z-index':10} );
            } catch ( exn ) {
                jquery_exception( exn, 'Iris Color Picker' );
            }
        } );
        
        $( '#ctc_main' ).on( 'change', '.ctc-child-value, input[type=checkbox]', function() {
            coalesce_inputs( this );
        } );
        
        $( '#ctc_main' ).on( 'click', '.ctc-selector-handle', function( e ) {
            //'.ctc-option-panel-container'
            e.preventDefault();
            if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
            $( this ).addClass( 'ajax-pending' );
            //set_notice( '' );
            var id = $( this ).attr( 'id' ).toString().replace( '_close', '' ),
                parts = id.toString().match( /_([^_]+)_(\d+)$/ );
            if ( $( '#' + id + '_container' ).is( ':hidden' ) ) {
                if ( !is_empty( parts[ 1 ] ) && !is_empty( parts[ 2 ] ) ) {
                    rule = parts[ 1 ];
                    valid = parts[ 2 ];
                    // retrieve selectors / values for individual value
                    query_css( 'val_qry', valid, { 'rule': rule } );
                }
            }
            $( '#' + id + '_container' ).fadeToggle( 'fast' );
            $( '.ctc-selector-container' ).not( '#' + id + '_container' ).fadeOut( 'fast' );
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-save-input', function( e ) {
            if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
            $( this ).addClass( 'ajax-pending' );
            save( this ); // refresh menus after updating data
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-selector-edit', function( e ) {
            e.preventDefault();
            if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
            $( this ).addClass( 'ajax-pending' );
            set_qsid( this );
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-rewrite-toggle', function( e ) {
            e.preventDefault();
            selector_input_toggle( this );
        } );
        $( '#ctc_main' ).on( 'click', '#ctc_copy_selector', function( e ) {
            var txt = $( '#ctc_sel_ovrd_selector_selected' ).text().trim();
            if ( !is_empty( txt ) )
                $( '#ctc_new_selectors' ).val( $( '#ctc_new_selectors' ).val() + lf + txt + " {\n\n}" );
        } );
        $( '#ctc_configtype' ).on( 'change', function( e ) {
            var val = $( this ).val();
            if ( is_empty( val ) || 'theme' == val ) {
                $( '.ctc-theme-only, .ctc-themeonly-container' ).removeClass( 'ctc-disabled' );
                $( '.ctc-theme-only, .ctc-themeonly-container input' ).prop( 'disabled', false );
                try {
                    $( '#ctc_theme_parnt, #ctc_theme_child' ).themeMenu( 'enable' );
                } catch ( exn ) {
                    jquery_exception( exn, 'Theme Menu' );
                }
            } else {
                $( '.ctc-theme-only, .ctc-themeonly-container' ).addClass( 'ctc-disabled' );
                $( '.ctc-theme-only, .ctc-themeonly-container input' ).prop( 'disabled', true );
                try {
                    $( '#ctc_theme_parnt, #ctc_theme_child' ).themeMenu( 'disable' );
                } catch ( exn ) {
                    jquery_exception( exn, 'Theme Menu' );
                }
            }
        } );    
        // these elements are not replaced so use direct selector events
        $( '.nav-tab' ).on( 'click', function( e ) {
            e.preventDefault();
            // clear the notice box
            //set_notice( '' );
            $( '.ctc-status-icon' ).remove();
            var id = '#' + $( this ).attr( 'id' );
            focus_panel( id );
        } );
        $( '.ctc-section-toggle' ).on( 'click', function( e ) {
            $( this ).toggleClass( 'open' );
            var id = $( this ).attr( 'id' ) + '_content';
            $( '#' + id ).slideToggle( 'fast' );
        } );
        $( '#view_child_options, #view_parnt_options' ).on( 'click', function( e ){ 
            if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
            $( this ).addClass( 'ajax-pending' );
            render_css_preview( $( this ).attr( 'id' ) ); 
        } );
        $( '#ctc_load_form' ).on( 'submit', function() {
            return ( validate() ); //&& confirm( gt( 'load' ) ) ) ;
        } );
        $( '#ctc_theme_child, #ctc_theme_child-button, #ctc_child_type_existing' )
            .on( 'focus click', function() {
            // change the inputs to use existing child theme
            $( '#ctc_child_type_existing' ).prop( 'checked', true );
            $( '#ctc_child_type_new' ).prop( 'checked', false );
            $( '#ctc_child_template' ).val( '' );
            set_existing();
        } );
        $( '#ctc_child_type_new, #ctc_child_template' ).on( 'focus click', function() {
            // change the inputs to use new child theme
            $( '#ctc_child_type_existing' ).prop( 'checked', false );
            $( '#ctc_child_type_new' ).prop( 'checked', true );
            $( '#ctc_child_name' ).val( testname );
            $( '#ctc_child_template' ).val( testslug );
        } );
        $( '#recent_edits' ).on( 'click', function( e ){
            e.preventDefault();
            if ( $( '.ctc-recent-container' ).is( ':visible' ) ) {
                $( '.ctc-recent-container' ).stop().slideUp();
                $( '.ctc-option-panel' ).css( { 'width': '95%' } );
            } else {
                // move recent edits to outer wrapper
                if ( !$('.ctc-recent-container').hasClass( 'moved' ) ) {
                    $( '.ctc-recent-container' ).addClass( 'moved' ).detach()
                        .appendTo('#ctc_option_panel_wrapper');
                }
                $( '.ctc-recent-container' ).stop().slideDown();
                $( '.ctc-option-panel' ).css( { 'width': '80%' } );
            }
            return false;
        } );
        $( '#ctc_is_debug' ).on( 'change', function( e ) {
            save( this );
        } );
        $( '.ctc-live-preview' ).on( 'click', function( e ) {
            e.stopImmediatePropagation();
            e.preventDefault();
            document.location = $( this ).prop( 'href' );
            return false;
        } );
        // initialize autoselect menus
        setup_menus();
        // mark additional linked stylesheets for parsing
        set_addl_css();
        // show last 25 selectors edited
        render_recent();
        // turn on submit buttons (disabled until everything is loaded to prevent errors)
        $( 'input[type=submit], input[type=button]' ).prop( 'disabled', false );
        // disappear any notices after 15 seconds
        setTimeout( fade_update_notice, 15000 );
    } else {
        //$( '.ctc-select' ).css( { 'visibility': 'visible' } ).show();
        jquery_notice();
    }
} );