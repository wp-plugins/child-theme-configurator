/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.6.4
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
            newslug = $( '#ctc_child_template' ).length ? $( '#ctc_child_template' ).val().toString().replace( regex ).toLowerCase() : '',
            slug    = $( '#ctc_theme_child' ).length ? $( '#ctc_theme_child' ).val().toString().replace( regex ).toLowerCase() : newslug,
            type    = $( 'input[name=ctc_child_type]:checked' ).val(),
            errors  = [];
        if ( 'new' == type ) slug = newslug;
        if ( theme_exists( slug, type ) ) {
            errors.push( ctcAjax.theme_exists_txt.toString().replace( /%s/, slug ) );
        }
        if ( '' === slug ) {
            errors.push( ctcAjax.inval_theme_txt );
        }
        if ( '' === $( '#ctc_child_name' ).val() ) {
            errors.push( ctcAjax.inval_name_txt );
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
                name    = ctcAjax.themes.parnt[parent].Name + ' Child',
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
        var origval;
        if ( $( '#ctc_rewrite_selector' ).length ) {
            origval = $( '#ctc_rewrite_selector_orig' ).val();
            $( '#ctc_sel_ovrd_selector_selected' ).text( origval );
            $( obj ).text( ctcAjax.rename_txt );
        } else {
            origval = $( '#ctc_sel_ovrd_selector_selected' ).text();
            $( '#ctc_sel_ovrd_selector_selected' ).html( '<textarea id="ctc_rewrite_selector" name="ctc_rewrite_selector" autocomplete="off"></textarea><input id="ctc_rewrite_selector_orig" name="ctc_rewrite_selector_orig" type="hidden" value="' 
                + esc_quot( origval ) + '"/>' );
            $( '#ctc_rewrite_selector' ).val( origval );
            $( obj ).text( ctcAjax.cancel_txt );
        }
    }

    function fade_update_notice() {
        $( '.updated, .error' ).slideUp( 'slow', function() { $( '.updated' ).remove(); } );
    }
    
    function coalesce_inputs( obj ) {
        var regex       = /^(ctc_(ovrd|\d+)_(parent|child)_([0-9a-z\-]+)_(\d+))(_\w+)?$/,
            container  = $( obj ).parents( '.ctc-selector-row, .ctc-parent-row' ).first(),
            swatch     = container.find( '.ctc-swatch' ).first(),
            cssrules    = { 'parent': {}, 'child': {} },
            gradient    = { 
                'parent': {
                    'origin': '',
                    'start': '',
                    'end': ''
                }, 
                'child': {
                    'origin': '',
                    'start': '',
                    'end': ''
                } 
            },
            has_gradient = { 'child': false, 'parent': false },
            postdata    = {};
        // set up objects for all neighboring inputs
        container.find( '.ctc-parent-value, .ctc-child-value' ).each( function() {
            var inputid     = $( this ).attr( 'id' ),
                inputparts  = inputid.toString().match( regex ),
                inputseq    = inputparts[2],
                inputtheme  = inputparts[3],
                inputrule   = ( 'undefined' == typeof inputparts[4] ? '' : inputparts[4] ),
                qsid        = inputparts[5],
                rulepart    = ( 'undefined' == typeof inputparts[6] ? '' : inputparts[6] ),
                value       = ( 'parent' == inputtheme ? $( this ).text().replace( /!$/, '' ) : $( this ).val() ),
                important   = 'ctc_' + inputseq + '_child_' + inputrule + '_i_' + qsid,
                parts, subparts;
            if ( false === is_empty( $( this ).data( 'color' ) ) ) {
                value = $( this ).data( 'color' );
                $( this ).data( 'color', null );
            }
            //console.log( 'id: ' + inputid + ' value: ' + value );
            if ( 'child' == inputtheme ) {
                postdata[inputid] = value;
                postdata[important] = ( $( '#' + important ).is( ':checked' ) ) ? 1 : 0;
            }
            /*if ( '' === value ) {
                $( '#'+important ).prop( 'checked', false );
                return;
            }*/
            if ( '' != value ) {
                // handle specific inputs
                if ( false === is_empty( rulepart ) ) {
                    //console.log( 'rulepart: ' + rulepart + ' value: ' + value );
                    switch( rulepart ) {
                        case '_border_width':
                            cssrules[inputtheme][inputrule + '-width'] = ( 'none' == value ? 0 : value );
                            break;
                        case '_border_style':
                            cssrules[inputtheme][inputrule + '-style'] = value;
                            break;
                        case '_border_color':
                            cssrules[inputtheme][inputrule + '-color'] = value;
                            break;
                        case '_background_url':
                            cssrules[inputtheme]['background-image'] = image_url( inputtheme, value );
                            break;
                        case '_background_color':
                            cssrules[inputtheme]['background-color'] = obj.value;
                            break;
                        case '_background_color1':
                            gradient[inputtheme].start   = value;
                            has_gradient[inputtheme] = true;
                            break;
                        case '_background_color2':
                            gradient[inputtheme].end     = value;
                            has_gradient[inputtheme] = true;
                            break;
                        case '_background_origin':
                            gradient[inputtheme].origin  = value;
                            has_gradient[inputtheme] = true;
                            break;
                    }
                } else {
                    // handle borders
                    if ( parts = inputrule.toString().match( /^border(\-(top|right|bottom|left))?$/ ) && !value.match( /none/ ) ) {
                        subparts = value.toString().split( / +/ );
                        cssrules[inputtheme][inputrule + '-width'] = 'undefined' == typeof subparts[0] ? '' : subparts[0];
                        cssrules[inputtheme][inputrule + '-style'] = 'undefined' == typeof subparts[1] ? '' : subparts[1];
                        cssrules[inputtheme][inputrule + '-color'] = 'undefined' == typeof subparts[2] ? '' : subparts[2];
                    // handle background images
                    } else if ( 'background-image' == inputrule && !value.match( /none/ ) ) {
                        if ( value.toString().match( /url\(/ ) ) {
                            cssrules[inputtheme]['background-image'] = image_url( inputtheme, value );
                        } else {
                            subparts = value.toString().split( / +/ );
                            if ( subparts.length > 2 ) {
                                gradient[inputtheme].origin = 'undefined' == typeof subparts[0] ? 'top' : subparts[0];
                                gradient[inputtheme].start  = 'undefined' == typeof subparts[1] ? 'transparent' : subparts[1];
                                gradient[inputtheme].end    = 'undefined' == typeof subparts[2] ? 'transparent' : subparts[2];
                                has_gradient[inputtheme] = true;
                            } else {
                                cssrules[inputtheme]['background-image'] = value;
                            }
                        }
                    } else if ( 'seq' != inputrule ) {
                        cssrules[inputtheme][inputrule] = value;
                    }
                }
            }
        } );
        // update swatch
        if ( 'undefined' != typeof swatch && false === is_empty( swatch.attr( 'id' ) ) ) {
            swatch.removeAttr( 'style' );
            if ( has_gradient.parent ) { swatch.ctcgrad( gradient.parent.origin, [gradient.parent.start, gradient.parent.end] ); }
            swatch.css( cssrules.parent );  
            if ( !( swatch.attr( 'id' ).toString().match( /parent/ ) ) ) {
                if ( has_gradient.child ) { swatch.ctcgrad( gradient.child.origin, [gradient.child.start, gradient.child.end] ); }
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
            obj['names'] = [
                '_border_width',
                '_border_style',
                '_border_color',
            ];
            obj['values'] = [ 
                ( 'undefined' == typeof params[0] ? '' : params[0] ),
                ( 'undefined' == typeof params[1] ? '' : params[1] ),
                ( 'undefined' == typeof params[2] ? '' : params[2] )
            ];
        } else if ( rule.toString().match( /^background\-image/ ) ) {
            obj['names'] = [
                '_background_url',
                '_background_origin', 
                '_background_color1', 
                '_background_color2'
            ];
            obj['values'] = ['', '', '', ''];
            if ( false === ( is_empty( value ) ) && !( value.toString().match( /(url|none)/ ) ) ) {
                var params = value.toString().split( /:/ );
                obj['values'][1] = ( 'undefined' == typeof params[0] ? '' : params[0] );
                obj['values'][2] = ( 'undefined' == typeof params[1] ? '' : params[1] );
                obj['values'][3] = ( 'undefined' == typeof params[3] ? '' : params[3] );
                obj['orig'] = [ obj['values'][1], obj['values'][2], obj['values'][3] ].join( ' ' ); // display "origin color1 color2"
            } else {
                obj['values'][0] = value;
            }
        } else {
            obj['names']    = [''];
            obj['values']   = [ value ];
        }
        return obj;
    }
    
    function image_url( theme, value ) {
        var parts = value.toString().match( /url\(['" ]*(.+?)['" ]*\)/ ),
            path = is_empty( parts ) ? null : parts[1],
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
    
    function prune_if_empty( qsid ) {
        //console.log( 'prune_if_empty' );
        //console.log( semaphore );
        if ( "object" === typeof ctcAjax.sel_val[qsid] && "object" === typeof ctcAjax.sel_val[qsid].value ) {    
            for ( var rule in ctcAjax.sel_val[qsid].value ) {
                if ( ctcAjax.sel_val[qsid].value.hasOwnProperty( rule ) ) {
                    if ( false === is_empty( ctcAjax.sel_val[qsid].value[rule].child ) 
                        || false === is_empty( ctcAjax.sel_val[qsid].value[rule].parnt ) ) {
                        return qsid;
                    }
                }
            }
        }
        delete ctcAjax.sel_val[qsid];
        return false;
    }
    
    function load_menus() {
        semaphore.rld_rule    = 1;
        semaphore.rld_sel     = 1;
        load_queries();
        load_rules();
    }
    
    function load_queries() {
        //console.log( 'load queries' );
        //console.log( semaphore );
        if ( 1 === semaphore.sel_ndx ) return;
        if ( 0 === semaphore.sel_ndx || 1 == semaphore.rld_sel ) { // {
            // retrieve from server
            //console.log( ' loading queries...' );
            semaphore.sel_ndx = 1;
            semaphore.rld_sel = 0;
            query_css( 'sel_ndx', null, load_queries );
            return;
        }
        //console.log( 'queries loaded. building menu source ...' );
        cache_queries = [];
        if ( false === is_empty( ctcAjax.sel_ndx ) ) {
            $.each( ctcAjax.sel_ndx, function( key, value ) {
                var obj = { label: key, value: key };
                cache_queries.push( obj );
            } );
        }
        setup_query_menu();
        load_selectors();     
    }
    
    function load_selectors() {
        //console.log( 'load selectors' );
        //console.log( semaphore );
        if ( 1 === semaphore.sel_ndx ) return;
        if ( 0 === semaphore.sel_ndx ) { 
            //console.log( ' loading selectors...' );
            // retrieve from server
            semaphore.sel_ndx = 1;
            query_css( 'sel_ndx', current_query, load_selectors );
            return;
        }
        //console.log( 'selectors loaded. building menu source ...' );
        cache_selectors = [];
        if ( false === is_empty( ctcAjax.sel_ndx ) ) {
            $.each( ctcAjax.sel_ndx[current_query], function( key, value ) {
                var obj = { label: key, value: value };
                cache_selectors.push( obj );
            } );
        }
        setup_selector_menu();
        if ( semaphore.set_sel ) {
            // selector semaphore set, set selector menu value
            // this also triggers selector value refresh
            semaphore.set_sel = 0;
            set_selector( current_qsid, null );   
        }
    }
    
    function load_rules() {
        //console.log( 'load rules' );
        //console.log( semaphore );
        if ( 1 === semaphore.rule ) return;
        if ( 0 === semaphore.rule || 1 == semaphore.rld_rule ) { 
            //console.log( ' loading rules...' );
            semaphore.rule = 1;
            semaphore.rld_rule = 0;
            query_css( 'rule', null, load_rules );
            return;
        }
        //console.log( 'rules loaded. building menu source ...' );
        cache_rules = [];
        if ( false === is_empty( ctcAjax.rule ) ) {
            $.each( ctcAjax.rule, function( key, value ) {
                var obj = { label: value.replace( /\d+/g, from_ascii ), value: key };
                cache_rules.push( obj );
            } );
        }
        cache_rules = cache_rules.sort( function ( a, b ) {
            if ( a.label > b.label )
                return 1;
            if ( a.label < b.label )
                return -1;
            return 0;
        } );
        setup_rule_menu();
        setup_new_rule_menu();
    }
    
    function load_selector_values( qsid ) {
        //console.log( 'load_selector_values' );
        //console.log( semaphore );
        if ( 1 === semaphore.sel_val ) return false;
        if ( is_empty( ctcAjax.sel_val[ qsid ] ) ) { 
            if ( 0 == semaphore.sel_val ) {
                semaphore.sel_val = 1;
                query_css( 'sel_val', qsid, load_selector_values );
            }
            // if semaphore is 2 and sel_val[qsid] does not exist, this selector if invalid
            return false;
        }
        current_qsid = qsid;
        if ( 1 == semaphore.set_qry ) {
            // query semaphore set, set query menu value
            semaphore.set_qry = 0;
            set_query( ctcAjax.sel_val[qsid].query );
        } else if ( 1 == semaphore.new_sel ) {
            // qsid semaphore set, render selector inputs
            semaphore.new_sel = 0;
            render_selector_inputs( qsid );
        }
    }
    
    function load_filtered_rules( request, response ) {
        var arr = [],
            noval = ( is_empty( ctcAjax.sel_val[current_qsid] ) ) || ( is_empty( ctcAjax.sel_val[current_qsid].value ) );
        if ( is_empty( cache_rules ) ) { 
            load_rules();
        }
        $.each( cache_rules, function( key, val ) {
            var skip = false,
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
            if ( matcher.test( val.label ) ) {
                if ( false === noval ) {
                    // skip rule if in current selector array
                    $.each( ctcAjax.sel_val[current_qsid].value, function( rule, value ) {
                        if ( val.label == rule.replace( /\d+/g, from_ascii ) ) {
                            skip = true;
                            return false;
                        }
                    } );
                    if ( skip ) {
                        return;
                    }
                }
                // add rule
                arr.push( val );
            }
        } );
        response( arr );
    }
    
    /**
     * The "render" functions inject html into the DOM based on the JSON result of Ajax actions
     */
    function render_child_rule_input( qsid, rule, seq ) {
        var html        = '', 
            value       = ( is_empty( ctcAjax.sel_val[qsid] ) 
                || is_empty( ctcAjax.sel_val[qsid].value ) 
                || is_empty( ctcAjax.sel_val[qsid].value[rule] ) ? '' : ctcAjax.sel_val[qsid].value[rule] ),
            oldRuleObj  = decode_value( rule, ( 'undefined' == typeof value ? '' : value['parnt'] ) ),
            oldRuleFlag = ( false === is_empty( value['i_parnt'] ) && 1 == value['i_parnt'] ) ? 
                ctcAjax.important_label : '',
            newRuleObj  = decode_value( rule, ( 'undefined' == typeof value ? '' : value['child'] ) ),
            newRuleFlag = ( false === is_empty( value['i_child'] ) && 1 == value['i_child'] ) ? 1 : 0,
            impid = 'ctc_' + seq + '_child_' + rule + '_i_' + qsid;
        if ( false === is_empty( ctcAjax.sel_val[qsid] ) ) {
            html += '<div class="ctc-' + ( 'ovrd' == seq ? 'input' : 'selector' ) + '-row clearfix">' + lf;
            html += '<div class="ctc-input-cell">' + ( 'ovrd' == seq ? rule.replace( /\d+/g, from_ascii ) : ctcAjax.sel_val[qsid].selector 
                + '<br/><a href="#" class="ctc-selector-edit" id="ctc_selector_edit_' + qsid + '" >' + ctcAjax.edit_txt + '</a> '
                + ( is_empty( oldRuleObj.orig ) ? ctcAjax.child_only_txt : '' ) ) 
                + '</div>' + lf;
            if ( 'ovrd' == seq ) {
                html += '<div class="ctc-parent-value ctc-input-cell" id="ctc_' + seq + '_parent_' + rule + '_' + qsid + '">' 
                + ( is_empty( oldRuleObj.orig ) ? '[no value]' : oldRuleObj.orig + oldRuleFlag ) + '</div>' + lf;
            }
            html += '<div class="ctc-input-cell">' + lf;
            if ( false === is_empty( oldRuleObj.names ) ) {
                $.each( oldRuleObj.names, function( ndx, newname ) {
                    newname = ( is_empty( newname ) ? '' : newname );
                    html += '<div class="ctc-child-input-cell">' + lf;
                    var id = 'ctc_' + seq + '_child_' + rule + '_' + qsid + newname,
                        newval;
                    if ( false === ( newval = newRuleObj.values.shift() ) ) {
                        newval = '';
                    }
                        
                    html += ( is_empty( newname ) ? '' : ctcAjax.field_labels[newname] + ':<br/>' ) 
                        + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                        + ( ( newname + rule ).toString().match( /color/ ) ? ' color-picker' : '' ) 
                        + ( ( newname ).toString().match( /url/ ) ? ' ctc-input-wide' : '' )
                        + '" value="' + esc_quot( newval ) + '" />' + lf;
                    html += '</div>' + lf;
                } );
                html += '<label for="' + impid + '"><input type="checkbox" id="' + impid + '" name="' + impid + '" value="1" '
                    + ( 1 === newRuleFlag ? 'checked' : '' ) + ' />' + ctcAjax.important_label + '</label>' + lf;
            }
            html += '</div>' + lf;
            html += ( 'ovrd' == seq ? '' : '<div class="ctc-swatch ctc-specific" id="ctc_child_' + rule + '_' + qsid + '_swatch">' 
                + ctcAjax.swatch_txt + '</div>' + lf 
                + '<div class="ctc-child-input-cell ctc-button-cell" id="ctc_save_' + rule + '_' + qsid + '_cell">' + lf
                + '<input type="button" class="button ctc-save-input" id="ctc_save_' + rule + '_' + qsid 
                + '" name="ctc_save_' + rule + '_' + qsid + '" value="Save" /></div>' + lf );
            html += '</div><!-- end input row -->' + lf;
        }
        return html;
    }
    
    function render_selector_inputs( qsid ) {
        //console.log( 'render_selector_inputs' );
        //console.log( semaphore );
        var id, html, val, selector;
        if ( qsid = prune_if_empty( qsid ) ) {
            $( '#ctc_sel_ovrd_qsid' ).val( qsid );
            current_qsid = qsid;
            if ( is_empty( ctcAjax.sel_val[qsid].seq ) ) {
                $( '#ctc_child_load_order_container' ).html( '' );
            } else {
                id = 'ctc_ovrd_child_seq_' + qsid;
                val = parseInt( ctcAjax.sel_val[qsid].seq );
                html = '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value" value="' + val + '" />';
                $( '#ctc_child_load_order_container' ).html( html );
            }
            if ( is_empty( ctcAjax.sel_val[qsid].value ) ) {
                $( '#ctc_sel_ovrd_rule_inputs' ).slideUp( function(){ $( '#ctc_sel_ovrd_rule_inputs' ).html( '' ); } );
            } else {
                html = '';
                $.each( ctcAjax.sel_val[qsid].value, function( rule, value ) {
                    html += render_child_rule_input( qsid, rule, 'ovrd' );
                } );
                $( '#ctc_sel_ovrd_rule_inputs' ).html( html ).find( '.color-picker' ).each( function() {
                    setup_iris( this );
                } );
                coalesce_inputs( '#ctc_child_all_0_swatch' );
                if ( jquery_err.length ) {
                    jquery_notice();
                } else {
                    $( '#ctc_sel_ovrd_selector_selected' ).text( ctcAjax.sel_val[qsid].selector );
                    $( '.ctc-rewrite-toggle' ).text( ctcAjax.rename_txt );
                    $( '#ctc_sel_ovrd_new_rule, #ctc_sel_ovrd_rule_header, #ctc_sel_ovrd_rule_inputs_container, #ctc_sel_ovrd_rule_inputs, .ctc-rewrite-toggle' ).slideDown();
                }
            }
        } else {
            //selector = $( '#ctc_sel_ovrd_selector_selected' ).text();
            //delete ctcAjax.sel_ndx[current_query][selector];
            $( '#ctc_sel_ovrd_selector_selected' ).html( '&nbsp;' );
            $( '#ctc_sel_ovrd_new_rule, #ctc_sel_ovrd_rule_header, #ctc_sel_ovrd_rule_inputs_container, #ctc_sel_ovrd_rule_inputs, .ctc-rewrite-toggle' ).slideUp( function(){ $( '#ctc_sel_ovrd_rule_inputs' ).html( '' ); } );
            
        }
        html = null; // to garbage;
    }
    
    function render_css_preview( theme ) {
        if ( 1 === semaphore.preview ) {
            return false;
        }
        if ( 0 == semaphore.preview ) { 
            semaphore.preview = 1;
            var theme;
            if ( !( theme = theme.match( /(child|parnt)/ )[1] ) ) {
                theme = 'child';
            }
            //set_notice( '' )
            query_css( 'preview', theme, render_css_preview );
            return false;
        }
        //console.log(ctcAjax.previewResponse);
        $( '#view_'+theme+'_options_panel' ).text( ctcAjax.previewResponse );
        ctcAjax.previewResponse = null; // send to trash
        semaphore.preview = 0;       
    }
    
    function render_rule_value_inputs( ruleid ) {
        if ( 1 === semaphore.rule_val ) return false;

        if ( 0 == semaphore.rule_val ) { 
            semaphore.rule_val = 1;
            query_css( 'rule_val', ruleid, render_rule_value_inputs );
            return false;
        }
        var rule = ctcAjax.rule[ruleid], 
            html = '<div class="ctc-input-row clearfix" id="ctc_rule_row_' + rule + '">' + lf;
        if ( false === is_empty( ctcAjax.rule_val[ruleid] ) ) {
            $.each( ctcAjax.rule_val[ruleid], function( valid, value ) {
                var oldRuleObj = decode_value( rule, value );
                html += '<div class="ctc-parent-row clearfix" id="ctc_rule_row_' + rule + '_' + valid + '">' + lf;
                html += '<div class="ctc-input-cell ctc-parent-value" id="ctc_' + valid + '_parent_' + rule + '_' + valid + '">' 
                    + oldRuleObj.orig + '</div>' + lf;
                html += '<div class="ctc-input-cell">' + lf;
                html += '<div class="ctc-swatch ctc-specific" id="ctc_' + valid + '_parent_' + rule + '_' + valid + '_swatch">' 
                    + ctcAjax.swatch_txt + '</div></div>' + lf;
                html += '<div class="ctc-input-cell"><a href="#" class="ctc-selector-handle" id="ctc_selector_' + rule + '_' + valid + '">'
                    + ctcAjax.selector_txt + '</a></div>' + lf;
                html += '<div id="ctc_selector_' + rule + '_' + valid + '_container" class="ctc-selector-container">' + lf;
                html += '<a href="#" id="ctc_selector_' + rule + '_' + valid + '_close" class="ctc-selector-handle ctc-exit" title="' 
                    + ctcAjax.close_txt + '"></a>';
                html += '<div id="ctc_selector_' + rule + '_' + valid + '_inner_container" class="ctc-selector-inner-container clearfix">' + lf;
                html += '<div id="ctc_status_val_qry_' + valid + '"></div>' + lf;
                html += '<div id="ctc_selector_' + rule + '_' + valid + '_rows"></div>' + lf;
                html += '</div></div></div>' + lf;
            } );
            html += '</div>' + lf;
        }
        $( '#ctc_rule_value_inputs' ).html( html ).find( '.ctc-swatch' ).each( function() {
            coalesce_inputs( this );
        } );
        html = null; // send to the garbage
    }
    
    function render_recent() {
        if ( is_empty( ctcAjax.recent_txt ) ) return;
        var html = '';
        if ( is_empty( ctcAjax.recent ) ) {
            html += ctcAjax.recent_txt;
        } else {
            //console.log(ctcAjax.recent);
            html += '<ul>' + "\n";
            $.each( ctcAjax.recent, function( ndx, el ) {
                $.each( el, function ( key, value ) {
                    html += '<li><a href="#" class="ctc-selector-edit" id="ctc_selector_edit_' + key + '" >' 
                    + value + '</a></li>' + "\n";
                } );
            } );
            html += '</ul>' + "\n";
        }
        $( '#ctc_recent_selectors' ).html( html );
        html = null; // send to the garbage
    }
    
    function render_selector_value_inputs( valid ) {
        if ( 1 == semaphore.val_qry ) return false;
        var params, 
            page_ruleid, 
            rule = $( '#ctc_rule_menu_selected' ).text().replace( /[^\w\-]/g, to_ascii ), 
            selector, 
            html = '';
        if ( 0 === semaphore.val_qry ) { 
            semaphore.val_qry = 1;
            params = { 'rule': rule };
            query_css( 'val_qry', valid, render_selector_value_inputs, params );
            return false;
        }
        if ( false === is_empty( ctcAjax.val_qry[valid] ) ) {
            $.each( ctcAjax.val_qry[valid], function( rule, queries ) {
                page_rule = rule;
                $.each( queries, function( query, selectors ) {
                    html += '<h4 class="ctc-query-heading">' + query + '</h4>' + lf;
                    if ( false === is_empty( selectors ) ) {
                        $.each( selectors, function( qsid, data ) {
                            ctcAjax.sel_val[qsid] = data;
                            html += render_child_rule_input( qsid, rule, valid );
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
        //console.log( 'setting up iris ' + ( 'undefined' != typeof $( obj ).attr( 'id' ) ? $( obj ).attr( 'id' ) : '' ) );
        try {
            $( obj ).iris( {
                change: function( e, ui ) {
                    //console.log( 'change event ' 
                    //+ ( 'undefined' != typeof $( this ).attr( 'id' ) ? $( this ).attr( 'id' ) : '' ) 
                    //+ ' ' + ui.color.toString() );
                    $( obj ).data( 'color', ui.color.toString() );
                    coalesce_inputs( obj );
                }
            } );
        } catch ( exn ) {
            jquery_exception( exn, 'Iris Color Picker' );
        }
    }
    
    function setup_query_menu() {
        //console.log( 'setup query menu' );
        //console.log( semaphore );
        try {
            $( '#ctc_sel_ovrd_query' ).autocomplete( {
                source: cache_queries,
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
        //console.log( 'setup selector menu' );
        //console.log( semaphore );
        try {
            $( '#ctc_sel_ovrd_selector' ).autocomplete( {
                source: cache_selectors,
                selectFirst: true,
                autoFocus: true,
                select: function( e, ui ) {
                    set_selector( ui.item.value, ui.item.label );
                    return false;
                },
                focus: function( e ) { e.preventDefault(); }
            } );
        } catch ( exn ) {
            jquery_exception( exn, 'Selector Menu' );
        }
    }
    
    function setup_rule_menu() {
        //console.log( 'setup rule menu' );
        //console.log( semaphore );
        try {
        $( '#ctc_rule_menu' ).autocomplete( {
            source: cache_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function( e, ui ) {
                set_rule( ui.item.value, ui.item.label );
                return false;
            },
            focus: function( e ) { e.preventDefault(); }
        } );
        } catch ( exn ) {
            jquery_exception( exn, 'Rule Menu' );
        }
    }
    
    function setup_new_rule_menu() {
        try {
        $( '#ctc_new_rule_menu' ).autocomplete( {
            source: load_filtered_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function( e, ui ) {
                e.preventDefault();
                var n = $( render_child_rule_input( current_qsid, ui.item.label.replace( /[^\w\-]/g, to_ascii ), 'ovrd' ) );
                $( '#ctc_sel_ovrd_rule_inputs' ).append( n );
                $( '#ctc_new_rule_menu' ).val( '' );
                if ( is_empty( ctcAjax.sel_val[current_qsid].value ) ) {
                    ctcAjax.sel_val[current_qsid]['value'] = {};
                }
                ctcAjax.sel_val[current_qsid].value[ui.item.label] = {'child': ''};
                n.find( 'input[type="text"]' ).each( function( ndx, el ) {
                    if ( $( el ).hasClass( 'color-picker' ) )
                        setup_iris( el );
                    $( el ).focus();
                } );
                if ( jquery_err.length ) jquery_notice();
                return false;
            },
            focus: function( e ) { e.preventDefault(); }
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
            if ( false === is_empty( child ) ) {
                $( '#ctc_child_name' ).val( ctcAjax.themes['child'][child].Name );
                $( '#ctc_child_author' ).val( ctcAjax.themes['child'][child].Author );
                $( '#ctc_child_version' ).val( ctcAjax.themes['child'][child].Version );
            }
        }
    }
    
    function set_notice( noticearr ) {
        var errorHtml = '';
        if ( false === is_empty( noticearr ) ) {
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
        $( '#ctc_theme_parent' ).parents( '.ctc-input-row' ).first().append( '<span class="ctc-status-icon spinner"></span>' );
        $( '.spinner' ).show();
        document.location='?page=' + ctcAjax.page + '&ctc_parent=' + obj.value;
    }
    
    function set_child_menu( obj ) {
        if ( false === is_empty( ctcAjax.themes.child[obj.value] ) ) {
            $( '#ctc_child_name' ).val( ctcAjax.themes.child[obj.value].Name );
            $( '#ctc_child_author' ).val( ctcAjax.themes.child[obj.value].Author );
            $( '#ctc_child_version' ).val( ctcAjax.themes.child[obj.value].Version );
        }
    }
    
    function set_query( value ) {
        current_query = value;
        $( '#ctc_sel_ovrd_query' ).val( '' );
        $( '#ctc_sel_ovrd_query_selected' ).text( value );
        $( '#ctc_sel_ovrd_selector' ).val( '' );
        $( '#ctc_sel_ovrd_selector_selected' ).html( '&nbsp;' );
        $( '#ctc_sel_ovrd_rule_inputs' ).html( '' );
        load_selectors();
    }
    
    function set_selector( value, label ) {
        $( '#ctc_sel_ovrd_selector' ).val( '' );
        if ( 1 != semaphore.sel_val ) semaphore.sel_val = 0;
        current_qsid = value;
        // set flag to render inputs after qsid values load
        semaphore.new_sel = 1;
        load_selector_values( value );
    }
    
    function set_rule( value, label ) {
        $( '#ctc_rule_menu' ).val( '' );
        $( '#ctc_rule_menu_selected' ).text( label );
        if ( 1 != semaphore.rule_val ) semaphore.rule_val = 0;
        render_rule_value_inputs( value );
        $( '.ctc-rewrite-toggle' ).text( ctcAjax.rename_txt );
        $( '#ctc_rule_value_inputs, #ctc_input_row_rule_header' ).show();
    }
    
    function set_qsid( obj ) {
        //console.log( 'set_qsid' );
        //console.log( semaphore );
        var qsid = $( obj ).attr( 'id' ).match( /_(\d+)$/ )[1];
        focus_panel( '#query_selector_options' );
        // set flag to load selector (qsid) values if empty
        semaphore.sel_val = 0;
        // set flags to set menu values after qsid values load
        semaphore.set_sel = semaphore.set_qry = 1;
        current_qsid = qsid;
        load_selector_values( qsid );  
    }
    
    /**
     * slurp website home page and parse header for linked stylesheets
     * set these to be parsed as "default" stylesheets
     */
    function set_addl_css() { 
        
        var template    = $( '#ctc_theme_parnt' ).val(),
            theme_uri   = ctcAjax.theme_uri.replace( /^https?:\/\//, '' ),
            homeurl     = ctcAjax.homeurl.replace( /^https?/, ctcAjax.ssl ? 'https' : 'http' ),
            url         = homeurl + '?preview=1&p=x&template=' + template + '&stylesheet=' + template,
            regex       = new RegExp( "<link rel=[\"']stylesheet[\"'][^>]+?" + theme_uri + '/' + template + '/(.+?\\.css)[^>]+?>', 'g' ),
            additional;
        if ( is_empty( template ) ) return;
        if ( template != ctcAjax.parnt ) {
            $.get( url, function( data ) {
                //console.log( data );
                while ( additional = regex.exec( data ) ) {
                    if ( 'style.css' == additional[1] ) break; // bail after main stylesheet
                    if ( additional[1].match( /bootstrap/ ) ) continue; // don't autoselect Bootstrap stylesheets
                    $( '.ctc_checkbox' ).each( function( ndx, el ) {
                        if ( $( this ).val() == additional[1] ) $( this ).prop( 'checked', true );
                    } );
                }
                data = null; // send page to garbage
            } );
        } else {
            //console.log('existing... using addl_css array');
            $( ctcAjax.addl_css ).each( function( ndx, el ) {
                $( '#ctc_stylesheet_files .ctc_checkbox' ).each( function( index, elem ) {
                    if ( $( this ).val() == el ) $( this ).prop( 'checked', true );
                    //console.log($( this ).val() + ' <> ' + el);
                } );
            } );
        }
    }
    
    /**
     * Retrieve data from server and execute callback on completion
     * Previously set semaphores control the callback behavior
     */
    function query_css( obj, key, callback, params ) {
        var postdata = { 'ctc_query_obj' : obj, 'ctc_query_key': key },
            status_sel = '#ctc_status_' + obj + ( 'val_qry' == obj ? '_' + key : '' );
        
        if ( 'object' === typeof params ) {
            $.each( params, function( key, val ) {
                postdata['ctc_query_' + key] = val;
            } );
        }
        $( '.query-icon' ).remove();
        $( status_sel ).append( '<span class="ctc-status-icon spinner query-icon"></span>' );
        $( '.spinner' ).show();
        // add wp ajax action to array
        //console.log( $( '#ctc_action' ).val() );
        postdata['action'] = ( false === is_empty( $( '#ctc_action' ).val() ) 
            && 'plugin' == $( '#ctc_action' ).val() ) ? 
                'ctc_plgqry' : 'ctc_query';
        postdata['_wpnonce'] = $( '#_wpnonce' ).val();
        // ajax post input data
        //console.log( 'query_css postdata:' );
        //console.log( postdata );
        $.post(  
            // get ajax url from localized object
            ctcAjax.ajaxurl,  
            //Data  
            postdata,
            //on success function  
            function( response ) {
                //console.log( response );
                // hide spinner
                semaphore[obj] = 2;
                //$( '.ctc-status-icon' ).removeClass( 'spinner' );
                // show check mark
                if ( is_empty( response ) ) {
                    $( '.query-icon' ).addClass( 'failure' );
                    if ( 'preview' == obj ) {
                        ctcAjax.previewResponse = ctcAjax.css_fail_txt;
                        callback( key );
                    }
                } else {
                    $( '.ctc-status-icon' ).addClass( 'success' );
                        if ( 1 == semaphore.refresh ) {
                            //console.log( 'cache reset flag set. resetting caches...');
                            semaphore.refresh = 0;
                            // configuration has changed, wipe out the cache arrays
                            reset_caches();
                        }
                        //console.log( 'updating cache from ' + obj + ' query');
                        // update data objects   
                        update_cache( response );
                        response = null;
                        render_recent();
                    if ( 'function' === typeof callback ) {
                        callback( key );
                    }
                }
            }, 'json'
        ).fail( function() {
            // hide spinner
            $( '.query-icon' ).removeClass( 'spinner' );
            // show check mark
            $( '.query-icon' ).addClass( 'failure' );
            if ( 'preview' == obj ) {
                ctcAjax.previewResponse = ctcAjax.css_fail_txt;
                semaphore[obj] = 2;
                callback( key );
            } else {
                semaphore[obj] = 0;
            }
            
        } );  
        return false; 
    }
    /**
     * Post data to server for saving and execute callback on completion
     * Previously set semaphores control the callback behavior
     */
    function save( obj, callback ) {
        var postdata = {},
            $selector, $query, $imports, $rule,
            id = $( obj ).attr( 'id' ), newsel;

        // disable the button until ajax returns
        $( obj ).prop( 'disabled', true );
        // clear previous success/fail icons
        $( '.save-icon' ).remove();
        // show spinner
        $( obj ).parent( '.ctc-textarea-button-cell, .ctc-button-cell' ).append( '<span class="ctc-status-icon spinner save-icon"></span>' );
        if ( id.match( /ctc_configtype/ ) ) {
            $( obj ).parents( '.ctc-input-row' ).first().append( '<span class="ctc-status-icon spinner save-icon"></span>' );
            postdata[ 'ctc_configtype' ] = $( obj ).val();
        } else if ( ( $selector = $( '#ctc_new_selectors' ) ) && 'ctc_save_new_selectors' == $( obj ).attr( 'id' ) ) {
            postdata['ctc_new_selectors'] = $selector.val();
            if ( $query = $( '#ctc_sel_ovrd_query_selected' ) ) {
                postdata['ctc_sel_ovrd_query'] = $query.text();
            }
        } else if ( ( $imports = $( '#ctc_child_imports' ) ) && 'ctc_save_imports' == $( obj ).attr( 'id' ) ) {
            postdata['ctc_child_imports'] = $imports.val();
        } else {
            // coalesce inputs
            postdata = coalesce_inputs( obj );
        }
        $( '.save-icon' ).show();
        // add rename selector value if it exists
        $( '#ctc_sel_ovrd_selector_selected' ).find( '#ctc_rewrite_selector' ).each( function() {
            newsel = $( '#ctc_rewrite_selector' ).val(),
                origsel = $( '#ctc_rewrite_selector_orig' ).val();
            if ( is_empty( newsel ) || !newsel.toString().match( /\w/ ) ) {
                newsel = origsel;
            } else {
                postdata['ctc_rewrite_selector'] = newsel;
            }
            $( '.ctc-rewrite-toggle' ).text( ctcAjax.rename_txt );
            $( '#ctc_sel_ovrd_selector_selected' ).html( newsel );
        } );
        // add wp ajax action to array
        //console.log( $( '#ctc_action' ).val() );
        postdata['action'] = ( false === is_empty( $( '#ctc_action' ).val() ) 
            && 'plugin' == $( '#ctc_action' ).val() ) ? 
                'ctc_plugin' : 'ctc_update';
        postdata['_wpnonce'] = $( '#_wpnonce' ).val();
        //console.log( postdata );
        // ajax post input data
        $.post(  
            // get ajax url from localized object
            ctcAjax.ajaxurl,  
            //Data  
            postdata,
            //on success function  
            function( response ) {
                //console.log( response );
                // release button
                $( obj ).prop( 'disabled', false );
                // hide spinner
                $( '.save-icon' ).removeClass( 'spinner' );
                // show check mark
                if ( is_empty( response ) ) {
                    $( '.save-icon' ).addClass( 'failure' );
                } else {
                    $( '#ctc_new_selectors' ).val( '' );
                    if ( 1 == semaphore.refresh ) {
                        //console.log( 'cache reset flag set. resetting caches...');
                        semaphore.refresh = 0;
                        // configuration has changed, wipe out the cache arrays
                        reset_caches();
                    }
                    //console.log( 'updating cache from ' + id + ' save');
                    // update data objects   
                    update_cache( response );
                    response = null;
                    if ( is_empty( rewrite_id ) ) {
                        if ( current_qsid ) {
                            render_selector_inputs( current_qsid );
                            render_recent();
                        }
                    } else {
                        set_selector( rewrite_id, rewrite_sel );
                        rewrite_id = rewrite_sel = null;
                    }
                    $( '.save-icon' ).addClass( 'success' );
                    if ( 'function' === typeof callback ) {
                        callback();
                    }
                }
                return false;  
            }, 'json'
        ).fail( function() {
            // release button
            $( obj ).prop( 'disabled', false );
            // hide spinner
            $( '.save-icon' ).removeClass( 'spinner' );
            // show check mark
            $( '.save-icon' ).addClass( 'failure' );
        } );  
        return false;  
    }
    
    function update_cache( response ) {
        //console.log( 'update_cache' );
        //console.log( response );
        //console.log( semaphore );
        $( response ).each( function() {
            switch ( this.obj ) {
                case 'imports':
                    ctcAjax.imports = this.data;
                    break;
            
                case 'rule_val':
                    ctcAjax.rule_val[this.key] = this.data;
                    break;
                
                case 'val_qry':
                    ctcAjax.val_qry[this.key] = this.data;
                    break;
                
                case 'rule':
                    ctcAjax.rule = this.data;
                    break;
                
                case 'sel_ndx':
                    if ( is_empty( this.key ) ) { 
                        ctcAjax.sel_ndx = this.data;
                    } else if ( 'qsid' == this.key ) {
                        if ( is_empty( ctcAjax.sel_ndx[this.data.query] ) ) {
                            ctcAjax.sel_ndx[this.data.query] = {}
                        } 
                        ctcAjax.sel_ndx[this.data.query][this.data.selector] = this.data.qsid;
                    } else { 
                        ctcAjax.sel_ndx[this.key] = this.data;
                        current_query = this.key;
                    }
                    break;
                               
                case 'sel_val':
                    ctcAjax.sel_val[this.key] = this.data;
                    current_qsid  = this.key;
                    break; 
                    
                case 'rewrite':
                    current_qsid  = this.key;
                    rewrite_id  = this.key;
                    rewrite_sel = this.data;
                    break;
                    
                case 'recent':
                    ctcAjax.recent  = this.data;
                    break;
                    
                case 'stylesheets':
                    $( '#ctc_stylesheet_files' ).html( this.data );
                    break;
                    
                case 'backups':
                    $( '#ctc_backup_files' ).html( this.data );
                    break;

                case 'addl_css':
                    ctcAjax.addl_css  = this.data;
                    //console.log( 'addl_css' );
                    //console.log( this.data );
                    break;
                
                case 'preview':
                case 'all_styles':
                    // refresh preview
                    ctcAjax.previewResponse = this.data;
            }
        } );
        response = null; // send to garbage
    }
    
    function reset_caches() {
        //console.log( 'resetting caches...');
        current_query           = 'base';
        current_qsid            = null;
        cache_selectors         = [];
        cache_queries           = [];
        cache_rules             = [];

        semaphore[ 'rld_rule' ] = 1;  // force rule reload
        semaphore[ 'rld_sel' ]  = 1;  // force sel_ndx reload
        semaphore[ 'set_qry' ]  = 1;  // set query on qsid load

        ctcAjax.imports         = [];
        ctcAjax.recent          = {};
        ctcAjax.rule            = {};
        ctcAjax.sel_ndx         = {};
        ctcAjax.val_qry         = {};
        ctcAjax.rule_val        = {};
        ctcAjax.sel_val         = {};
        //console.log( 'caches reset. loading menus...');
        load_menus();
    }
    function jquery_exception( exn, type ) {
        var ln = is_empty( exn.lineNumber ) ? '' : ' line: ' + exn.lineNumber,
            fn = is_empty( exn.fileName ) ? '' : ' ' + exn.fileName.split( /\?/ )[0];
        jquery_err.push( '<code><small>' + type + ': ' + exn.message + fn + ln + '</small></code>' );
    }
    function jquery_notice() {
        
        var culprits    = [],
            errors      = [];
        // disable form submits
        $( 'input[type=submit], input[type=button]' ).prop( 'disabled', true );
        $( 'script' ).each( function( ndx,el ){
            var url = $( this ).prop( 'src' );
            if ( false === is_empty( url ) && url.match( /jquery(\.|\-?ui)/i ) && ! url.match( /load\-scripts.php/ ) ) {
                culprits.push( '<code><small>' + url.split( /\?/ )[0] + '</small></code>' );
            }
        } );
        errors.push( '<strong>' + ctcAjax.js_txt + '</strong>' );
        if ( false === is_empty( ctcAjax.is_debug ) ) {
            errors.push( jquery_err.join( '<br/>' ) );
        }
        if ( culprits.length ) {
            errors.push( ctcAjax.jquery_txt + '<br/>' + culprits.join( '<br/>' ) );
        }
        errors.push( ctcAjax.plugin_txt + ' ' + ctcAjax.contact_txt );
        set_notice( { 'error': errors } );
    }
    // initialize vars
    var lf = "\n", 
        quot_regex = new RegExp( '"', 'g' ),
        rewrite_id, 
        rewrite_sel,
        testslug    = '',
        testname    = '',
        current_query = 'base',
        current_qsid,
        semaphore = {
            // status flags: 0 = load, 1 = loading, 2 = loaded
            'rule':     0,  // rules
            'sel_ndx':  0,  // index of queries/selectors
            'val_qry':  0,  // values by qsid
            'rule_val': 0,  // values by rule
            'sel_val':  0,  // qsids (query/selector ids) by value
            'preview':  0,  // stylesheet preview
            'recent':   0,  // recent edits
            // these control behavior of ajax callbacks
            'rld_rule': 0,  // force rule reload
            'rld_sel':  0,  // force sel_ndx reload
            'set_qry':  0,  // set query on qsid load
            'set_sel':  0,  // set selector on sel load
            'new_sel':  0,  // render new inputs on qsid load
            'refresh':  0   // reset caches on load
        },
        // these caches are used as the source for autocomplete menus
        cache_selectors = [],
        cache_queries   = [],
        cache_rules     = [],
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
                    $( '#ctc_theme_option_' + item.value ).detach().appendTo( li );
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
    $( '.nav-tab' ).on( 'click', function( e ) {
        e.preventDefault();
        // clear the notice box
        //set_notice( '' );
        $( '.ctc-status-icon' ).remove();
        var id = '#' + $( this ).attr( 'id' );
        focus_panel( id );
    } );
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
        
        $( '#ctc_main' ).on( 'change', '.ctc-child-value, input[type=checkbox]', function() { //'.ctc-option-panel-container', 
            coalesce_inputs( this );
        } );
        
        $( '#ctc_main' ).on( 'click', '.ctc-selector-handle', function( e ) { //'.ctc-option-panel-container'
            e.preventDefault();
            //set_notice( '' );
            var id = $( this ).attr( 'id' ).toString().replace( '_close', '' ),
                valid = id.toString().match( /_(\d+)$/ )[1];
            if ( $( '#' + id + '_container' ).is( ':hidden' ) ) {
                if ( 1 != semaphore.val_qry ) semaphore.val_qry = 0;
                render_selector_value_inputs( valid );
            }
            $( '#' + id + '_container' ).fadeToggle( 'fast' );
            $( '.ctc-selector-container' ).not( '#' + id + '_container' ).fadeOut( 'fast' );
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-save-input', function( e ) {
            save( this , load_menus ); // refresh menus after updating data
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-selector-edit', function( e ) {
            set_qsid( this );
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-rewrite-toggle', function( e ) {
            e.preventDefault();
            selector_input_toggle( this );
        } );
        $( '#ctc_main' ).on( 'click', '.ctc-section-toggle', function( e ) {
            $( this ).toggleClass( 'open' );
            var id = $( this ).attr( 'id' ) + '_content';
            $( '#' + id ).slideToggle( 'fast' );
        } );
        $( '#ctc_main' ).on( 'click', '#ctc_copy_selector', function( e ) {
            var txt = $( '#ctc_sel_ovrd_selector_selected' ).text().trim();
            if ( false === is_empty( txt ) )
                $( '#ctc_new_selectors' ).val( $( '#ctc_new_selectors' ).val() + "\n" + txt + " {\n\n}" );
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
        $( '#view_child_options, #view_parnt_options' ).on( 'click', function( e ){ render_css_preview( $( this ).attr( 'id' ) ); } );
        $( '#ctc_load_form' ).on( 'submit', function() {
            return ( validate() ); //&& confirm( ctcAjax.load_txt ) ) ;
        } );
        $( '#ctc_theme_child, #ctc_theme_child-button, #ctc_child_type_existing' ).on( 'focus click', function() {
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
                $( this ).removeClass( 'open' );
                $( '.ctc-recent-container' ).stop().slideUp();
                $( '.ctc-option-panel' ).css( { 'width': '95%' } );
            } else {
                $( this ).addClass( 'open' );
                $( '.ctc-recent-container' ).stop().slideDown();
                $( '.ctc-option-panel' ).css( { 'width': '80%' } );
            }
            return false;
        } );
        $( '.ctc-live-preview' ).on( 'click', function( e ) {
            e.stopImmediatePropagation();
            e.preventDefault();
            document.location = $( this ).prop( 'href' );
            return false;
        } );
        // initialize autoselect menus
        load_menus();
        set_query( current_query );
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