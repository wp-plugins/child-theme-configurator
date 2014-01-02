/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.1.7
 *  Author: Lilaea Media
 *  Author URI: http://www.lilaeamedia.com/
 *  License: GPLv2
 *  Copyright (C) 2013 Lilaea Media
 */
jQuery(document).ready(function($){

    var lf = "\n", 
        currentQuery = 'base',
        currentSel,
        saveEvents = {},
    // initialize functions
    ctc_setup_iris = function(obj) {
        $(obj).iris({
            change: function() {
                ctc_coalesce_inputs(obj);
            }   
        });
    },

    ctc_coalesce_inputs = function(obj) {
        var regex       = /^(ctc_(ovrd_)?(parent|child)_([a-z\-]+)_(\d+))(_\w+)?$/,
            $container  = $(obj).parents('.ctc-selector-row, .ctc-parent-row').first(),
            $swatch     = $container.find('.ctc-swatch').first(),
            cssrules = { 'parent': {}, 'child': {} },
            gradient = { 
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
            postdata = {};
        // set up objects for all neighboring inputs
        $container.find('.ctc-parent-value, .ctc-child-value').each(function(){
            var inputid     = $(this).attr('id'),
                inputparts  = inputid.toString().match(regex),
                inputtheme  = inputparts[3],
                inputrule   = ('undefined' == typeof inputparts[4] ? '' : inputparts[4]),
                qsid        = inputparts[5],
                rulepart    = ('undefined' == typeof inputparts[6] ? '' : inputparts[6]),
                value       = ('parent' == inputtheme ? $(this).text() : $(this).val()),
                parts, subparts;
            if ('child' == inputtheme) {
                postdata[inputid] = value;
            }
            if ('' === value) return;
            // handle specific inputs
            if (false === ctc_is_empty(rulepart)) {
                switch(rulepart) {
                    case '_border_width':
                        cssrules[inputtheme][inputrule + '-width'] = value;
                        break;
                    case '_border_style':
                        cssrules[inputtheme][inputrule + '-style'] = value;
                        break;
                    case '_border_color':
                        cssrules[inputtheme][inputrule + '-color'] = value;
                        break;
                    case '_background_url':
                        cssrules[inputtheme]['background-image'] = ctc_image_url(inputtheme, value);
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
                if (parts = inputrule.toString().match(/^border(\-(top|right|bottom|left))?$/) && !value.match(/none/)) {
                    subparts = value.toString().split(/ +/);
                    cssrules[inputtheme][inputrule + '-width'] = 'undefined' == typeof subparts[0] ? '' : subparts[0];
                    cssrules[inputtheme][inputrule + '-style'] = 'undefined' == typeof subparts[1] ? '' : subparts[1];
                    cssrules[inputtheme][inputrule + '-color'] = 'undefined' == typeof subparts[2] ? '' : subparts[2];
                // handle background images
                } else if ( 'background-image' == inputrule ) {
                    if (value.toString().match(/url\(/)) {
                        cssrules[inputtheme]['background-image'] = ctc_image_url(inputtheme, value);
                    } else {
                        subparts = value.toString().split(/ +/);
                        if (subparts.length > 2) {
                            gradient[inputtheme].origin = 'undefined' == typeof subparts[0] ? 'top' : subparts[0];
                            gradient[inputtheme].start  = 'undefined' == typeof subparts[1] ? 'transparent' : subparts[1];
                            gradient[inputtheme].end    = 'undefined' == typeof subparts[2] ? 'transparent' : subparts[2];
                            has_gradient[inputtheme] = true;
                        } else {
                            cssrules[inputtheme]['background-image'] = value;
                        }
                    }
                } else {
                    cssrules[inputtheme][inputrule] = value;
                }
            }
        });
        // update swatch
        if ('undefined' != typeof $swatch) {
            $($swatch).removeAttr('style');
            if (has_gradient.parent) { $($swatch).ctcgrad(gradient.parent.origin, [gradient.parent.start, gradient.parent.end]); }
            $($swatch).css(cssrules.parent);  
            if (!($swatch.attr('id').toString().match(/parent/))){
                if (has_gradient.child) { $($swatch).ctcgrad(gradient.child.origin, [gradient.child.start, gradient.child.end]); }
                $($swatch).css(cssrules.child);
            }
        }
        return postdata;
    },
    ctc_update_cache = function(response) {
        var currQuery, currSelId, currRule;
        $(response).each(function(){
            switch (this.obj) {
                case 'imports':
                    ctcAjax.imports = this.data;
                    break;
            
                case 'rule_val':
                    ctcAjax.rule_val[this.key] = this.data;
                    currRuleId  = this.key;
                    break;
                
                case 'val_qry':
                    ctcAjax.val_qry[this.key] = this.data;
                    break;
                
                case 'rule':
                    ctcAjax.rule = this.data;
                    break;
                
                case 'sel_ndx':
                    if (ctc_is_empty(this.key)) { 
                        ctcAjax.sel_ndx = this.data;
                    } else if ('qsid' == this.key) {
                        ctcAjax.sel_ndx[this.data['query']][this.data['selector']] = this.data['qsid'];
                    } else { 
                        ctcAjax.sel_ndx[this.key] = this.data;
                        currQuery = this.key;
                    }
                    break;
                               
                case 'sel_val':
                    ctcAjax.sel_val[this.key] = this.data;
                    currSelId = this.key;
                    break;           
            }
        });
    },
    ctc_image_url = function(theme, value) {
        var parts = value.toString().match(/url\([" ]*(.+?)[" ]*\)/),
            path = ('undefined' == typeof parts ? null : parts[1]),
            url = ctcAjax.theme_uri + '/' + ('parent' == theme ? ctcAjax.parnt : ctcAjax.child) + '/',
            image_url;
        if (!path) { 
            return false; 
        } else if (path.toString().match(/^(https?:|\/)/)) { 
            image_url = value; 
        } else { 
            image_url = 'url(' + url + path + ')'; 
        }
        return image_url;
    },
    
    ctc_is_empty = function(obj) {
        // first bail when definitely empty or undefined (true) NOTE: zero is not empty
        if ('undefined' == typeof obj || false === obj || null === obj || '' === obj) { return true; }
        // then, if this is bool, string or number it must not be empty (false)
        if (true === obj || "string" === typeof obj || "number" === typeof obj) { return false; }
        // thanks to Abena Kuttin for Win safe version
        // check for object type to be safe
        if ("object" === typeof obj) {    
            // Use a standard for in loop
            for (var x in obj) {
                // A for in will iterate over members on the prototype
                // chain as well, but Object.getOwnPropertyNames returns
                // only those directly on the object, so use hasOwnProperty.
                if (obj.hasOwnProperty(x)) {
                    // any value means not empty (false)
                    return false;
                }
            }
            // no properties, so return empty (true)
            return true;
        } 
        // this must be an unsupported datatype, so return not empty
        return false; 
    
    },
    
    ctc_load_queries = function() {
        var arr = [];
        if (1 === loading.sel_ndx) return arr;
        if (0 === loading.sel_ndx) { // {
            // retrieve from server
            loading.sel_ndx = 1;
            ctc_query_css('sel_ndx', null, ctc_setup_query_menu);
            return arr;
        }
        if (false === ctc_is_empty(ctcAjax.sel_ndx)) {
            $.each(ctcAjax.sel_ndx, function(key, value) {
                var obj = { label: key, value: key };
                arr.push(obj);
            });
        }
        return arr;
    },
    
    ctc_load_selectors = function(query) {
        var arr = [];
        if (1 === loading.sel_ndx) {
            return arr;
        }
        if (0 === loading.sel_ndx) { 
            // retrieve from server
            loading.sel_ndx = 1;
            ctc_query_css('sel_ndx', query, ctc_setup_selector_menu);
            return arr;
        }
        if (false === ctc_is_empty(ctcAjax.sel_ndx[query])) {
            $.each(ctcAjax.sel_ndx[query], function(key, value) {
                var obj = { label: key, value: value };
                arr.push(obj);
            });
        }
        return arr;
    },
    
    ctc_load_rules = function() {
        var arr = [];
        if (1 === loading.rule) return arr;
        if (0 === loading.rule) { 
            loading.rule = 1;
            ctc_query_css('rule', null, ctc_setup_rule_menu);
            return arr;
        }
        if (false === ctc_is_empty(ctcAjax.rule)) { 
            $.each(ctcAjax.rule, function(key, value) {
                var obj = { label: value, value: key };
                arr.push(obj);
            });
        }
        return arr.sort(function (a, b) {
            if (a.label > b.label)
                return 1;
            if (a.label < b.label)
                return -1;
            return 0;
        });
    },
    
    ctc_render_child_rule_input = function(qsid, rule, specific) {
        var html = '', 
            value = (ctc_is_empty(ctcAjax.sel_val[qsid]) || ctc_is_empty(ctcAjax.sel_val[qsid].value) 
                || ctc_is_empty(ctcAjax.sel_val[qsid].value[rule]) ? '' : ctcAjax.sel_val[qsid].value[rule]),
            oldRuleObj = ctc_decode_value(rule, ('undefined' == typeof value ? '' : value['parnt'])),
            newRuleObj = ctc_decode_value(rule, ('undefined' == typeof value ? '' : value['child']));
        if (false === ctc_is_empty(ctcAjax.sel_val[qsid])) {
            html += '<div class="ctc-' + (specific ? 'selector' : 'input' ) + '-row clearfix">' + lf;
            html += '<div class="ctc-input-cell">' + (specific ? ctcAjax.sel_val[qsid].selector 
                + (ctc_is_empty(oldRuleObj.orig) ? '<br/>' + ctcAjax.child_only_txt : '') : rule) + '</div>' + lf;
            if (!specific) {
                html += '<div class="ctc-parent-value ctc-input-cell" id="ctc_parent_' + rule + '_' + qsid + '">' 
                + (ctc_is_empty(oldRuleObj.orig) ? '[no value]' : oldRuleObj.orig) + '</div>' + lf;
            }
            html += '<div class="ctc-input-cell">' + lf;
            if (false === ctc_is_empty(oldRuleObj.names)){
                $.each(oldRuleObj.names, function(ndx, newname) {
                    newname = (ctc_is_empty(newname) ? '' : newname);
                    html += '<div class="ctc-child-input-cell">' + lf;
                    var id = 'ctc_' + (specific? '' : 'ovrd_') + 'child_' + rule + '_' + qsid + newname,
                        newval;
                    if (false === (newval = newRuleObj.values.shift()) ){
                        newval = '';
                    }
                        
                    html += (ctc_is_empty(newname) ? '' : ctcAjax.field_labels[newname] + ':<br/>') 
                        + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                        + ((newname + rule).toString().match(/color/) ? ' color-picker' : '') 
                        + ((newname).toString().match(/url/) ? ' ctc-input-wide' : '')
                        + '" value="' + newval + '" />' + lf;
                    html += '</div>' + lf;
                });
            }
            html += '</div>' + lf;
            html += (specific ? '<div class="ctc-swatch ctc-specific" id="ctc_child_' + rule + '_' + qsid + '_swatch">' 
                + ctcAjax.swatch_txt + '</div>' + lf 
                + '<div class="ctc-child-input-cell ctc-button-cell" id="ctc_save_' + rule + '_' + qsid + '_cell">' + lf
                + '<input type="button" class="button ctc-save-input" id="ctc_save_' + rule + '_' + qsid 
                + '" name="ctc_save_' + rule + '_' + qsid + '" value="Save" /></div>' + lf : '');
            html += '</div><!-- end input row -->' + lf;
        }
        return html;
    },
    
    ctc_render_selector_inputs = function(qsid) {
        if (1 === loading.sel_val) {
            return false;
        }
        if (0 == loading.sel_val) { 
            loading.sel_val = 1;
            ctc_query_css('sel_val', qsid, ctc_render_selector_inputs);
            return false;
        }
        var html = '';
        if (false === ctc_is_empty(ctcAjax.sel_val[qsid] && false === ctc_is_empty(ctcAjax.sel_val[qsid].value))){
            $.each(ctcAjax.sel_val[qsid].value, function(rule, value) {
                html += ctc_render_child_rule_input(qsid, rule, false);
            });
        }
        $('#ctc_sel_ovrd_rule_inputs').html(html).find('.color-picker').each(function() {
            ctc_setup_iris(this);
        });
        ctc_coalesce_inputs('#ctc_child_all_0_swatch');
    }

    ctc_render_rule_value_inputs = function(ruleid) {
        if (1 === loading.rule_val) return false;

        if (0 == loading.rule_val) { 
            loading.rule_val = 1;
            ctc_query_css('rule_val', ruleid, ctc_render_rule_value_inputs);
            return false;
        }
        var rule = ctcAjax.rule[ruleid], 
            html = '<div class="ctc-input-row clearfix" id="ctc_rule_row_' + rule + '">' + lf;
        if (false === ctc_is_empty(ctcAjax.rule_val[ruleid])){
            $.each(ctcAjax.rule_val[ruleid], function(valid, value) {
                var oldRuleObj = ctc_decode_value(rule, value);
                html += '<div class="ctc-parent-row clearfix" id="ctc_rule_row_' + rule + '_' + valid + '">' + lf;
                html += '<div class="ctc-input-cell ctc-parent-value" id="ctc_parent_' + rule + '_' + valid + '">' 
                    + oldRuleObj.orig + '</div>' + lf;
                html += '<div class="ctc-input-cell">' + lf;
                html += '<div class="ctc-swatch ctc-specific" id="ctc_parent_'+rule+'_' + valid + '_swatch">' 
                    + ctcAjax.swatch_txt + '</div></div>' + lf;
                html += '<div class="ctc-input-cell"><a href="#" class="ctc-selector-handle" id="ctc_selector_' + rule + '_' + valid + '">'
                    + ctcAjax.selector_txt + '</a></div>' + lf;
                html += '<div id="ctc_selector_' + rule + '_' + valid + '_container" class="ctc-selector-container clearfix">' + lf;
                html += '<a href="#" id="ctc_selector_' + rule + '_' + valid + '_close" class="ctc-selector-handle" style="float:right">' 
                    + ctcAjax.close_txt + '</a><div id="ctc_status_val_qry_' + valid + '"></div>' + lf;
                html += '<div id="ctc_selector_' + rule + '_' + valid + '_rows"></div>' + lf;
                html += '</div></div>' + lf;
            });
            html += '</div>' + lf;
        }
        $('#ctc_rule_value_inputs').html(html).find('.ctc-swatch').each(function() {
            ctc_coalesce_inputs(this);
        });
    },

    ctc_render_selector_value_inputs = function(valid) {
        if (1 == loading.val_qry) return false;
        var params, 
            page_ruleid, 
            rule = $('#ctc_rule_menu_selected').text(), 
            selector, 
            html = '';
        if (0 === loading.val_qry) { 
            loading.val_qry = 1;
            params = { 'rule': rule };
            ctc_query_css('val_qry', valid, ctc_render_selector_value_inputs, params);
            return false;
        }
        if (false === ctc_is_empty(ctcAjax.val_qry[valid])){
            $.each(ctcAjax.val_qry[valid], function(rule, queries) {
                page_rule = rule;
                $.each(queries, function(query, selectors) {
                    html += '<h4 class="ctc-query-heading">' + query + '</h4>' + lf;
                    if (false === ctc_is_empty(selectors)){
                        $.each(selectors, function(qsid, data) {
                            ctcAjax.sel_val[qsid] = data;
                            html += ctc_render_child_rule_input(qsid, rule, true);
                        });
                    }
                });
            });
        }
        selector = '#ctc_selector_' + rule + '_' + valid + '_rows';
        $(selector).html(html).find('.color-picker').each(function() {
            ctc_setup_iris(this);
        });
        $(selector).find('.ctc-swatch').each(function() {
            ctc_coalesce_inputs(this);
        });

    },
    ctc_query_css = function(obj, key, callback, params) {
        var postdata = { 'ctc_query_obj' : obj, 'ctc_query_key': key },
            status_sel = '#ctc_status_' + obj + ('val_qry' == obj ? '_' + key : '');
        
        if ('object' === typeof params) {
            $.each(params, function(key, val){
                postdata['ctc_query_' + key] = val;
            });
        }
        $('.ctc-status-icon').remove();
        $(status_sel).append('<span class="ctc-status-icon spinner"></span>');
        $('.spinner').show();
        // add wp ajax action to array
        postdata['action'] = 'ctc_query';
        postdata['_wpnonce'] = $('#_wpnonce').val();
        // ajax post input data
        $.post(  
            // get ajax url from localized object
            ctcAjax.ajaxurl,  
            //Data  
            postdata,
            //on success function  
            function(response){
                // hide spinner
                loading[obj] = 2;
                $('.ctc-status-icon').removeClass('spinner');
                // show check mark
                if (ctc_is_empty(response)) {
                    $('.ctc-status-icon').addClass('failure');
                } else {
                    $('.ctc-status-icon').addClass('success');
                    // update data objects   
                    ctc_update_cache(response);
                    if ('function' === typeof callback) {
                        callback(key);
                    }
                    return false;  
                }
            },'json'
        ).fail(function(){
                loading[obj] = 0;
            // hide spinner
            $('.ctc-status-icon').removeClass('spinner');
            // show check mark
            $('.ctc-status-icon').addClass('failure');
        });  
        return false; 
    },
    ctc_save = function(obj) {
        var postdata = {},
            $selector, $query, $imports, $rule,
            id = $(obj).attr('id');
        if (ctc_is_empty(saveEvents[id])) {
            saveEvents[id] = 0;
        }
        saveEvents[id]++;
        // disable the button until ajax returns
        $(obj).prop('disabled', true);
        // clear previous success/fail icons
        $('.ctc-status-icon').remove();
        // show spinner
        $(obj).parent('.ctc-textarea-button-cell, .ctc-button-cell').append('<span class="ctc-status-icon spinner"></span>');
        $('.spinner').show();
        if (($selector = $('#ctc_new_selectors')) && 'ctc_save_new_selectors' == $(obj).attr('id')) {
            postdata['ctc_new_selectors'] = $selector.val();
            if ($query = $('#ctc_sel_ovrd_query_selected')) {
                postdata['ctc_sel_ovrd_query'] = $query.text();
            }
        } else if (($imports = $('#ctc_child_imports')) && 'ctc_save_imports' == $(obj).attr('id')) {
            postdata['ctc_child_imports'] = $imports.val();
        } else {
            // coalesce inputs
            postdata = ctc_coalesce_inputs(obj);
        }
        // add wp ajax action to array
        postdata['action'] = 'ctc_update';
        postdata['_wpnonce'] = $('#_wpnonce').val();
        // ajax post input data
        $.post(  
            // get ajax url from localized object
            ctcAjax.ajaxurl,  
            //Data  
            postdata,
            //on success function  
            function(response){
                //console.log(response);
                // release button
                $(obj).prop('disabled', false);
                // hide spinner
                $('.ctc-status-icon').removeClass('spinner');
                // show check mark
                if (ctc_is_empty(response)) {
                    $('.ctc-status-icon').addClass('failure');
                } else {
                    $('.ctc-status-icon').addClass('success');
                    $('#ctc_new_selectors').val('');
                    // update data objects   
                    ctc_update_cache(response);
                    ctc_setup_menus();
                }
                return false;  
            },
            'json'
        ).fail(function(){
            // release button
            $(obj).prop('disabled', false);
            // hide spinner
            $('.ctc-status-icon').removeClass('spinner');
            // show check mark
            $('.ctc-status-icon').addClass('failure');
        });  
        return false;  
    },
    /* stub for future ajax loader
    ctc_load_css = function(e) {
        
        
        var postdata = $(this).serializeArray();
        // disable the button until ajax returns
        $('#ctc_load_styles').prop('disabled', true);
        // clear previous success/fail icons
        $('.ctc-status-icon').remove();
        // show spinner
        $('#ctc_load_styles').parent('.ctc-input-cell').append('<span class="ctc-status-icon spinner"></span>');
        $('.spinner').show();
        return true;
        
        // ajax post input data
        $.post(  
            // get ajax url from localized object
            ctcAjax.ajaxurl,  
            //Data  
            postdata,
            //on success function  
            function(response){
                // release button
                $('#ctc_load_styles').prop('disabled', false);
                // hide spinner
                $('.ctc-status-icon').removeClass('spinner');
                // show check mark
                if (ctc_is_empty(response.updated)) {
                    $('.ctc-status-icon').addClass('failure');
                    // update notice
                    if (false === ctc_is_empty(response.error)) {
                        ctc_set_notice({'error': response.error});
                    }
                } else {
                    $('.ctc-status-icon').addClass('success');
                    // update notice
                    if (false === ctc_is_empty(response.updated)) {
                        ctc_set_notice({'updated': response.updated});
                    }
                    if (false === ctc_is_empty(response.themes)) {
                        ctcAjax.themes = response.themes;
                    }
                    if (false === ctc_is_empty(response.menu)) {
                        $('#ctc_theme_child').html(response.menu);
                    }
                    $('.nav-tab, .ctc-option-panel, .ctc-radio, .ctc-select, label').fadeIn('fast');
                }
                return false;  
            },
            'json'
        ).fail(function(){
            // release button
            $('#ctc_load_styles').prop('disabled', false);
            // hide spinner
            $('.ctc-status-icon').removeClass('spinner');
            // show check mark
            $('.ctc-status-icon').addClass('failure');
        });  
        return false; 
    },*/
    ctc_decode_value = function(rule, value) {
        value = ('undefined' == typeof value ? '' : value);
        var obj = { 'orig':   value };
        if (rule.toString().match(/^border(\-(top|right|bottom|left))?$/)) {
            var params = value.toString().split(/ +/);
            obj['names'] = [
                '_border_width',
                '_border_style',
                '_border_color',
            ];
            obj['values'] = [ 
                ('undefined' == typeof params[0] ? '' : params[0]),
                ('undefined' == typeof params[1] ? '' : params[1]),
                ('undefined' == typeof params[2] ? '' : params[2])
            ];
        } else if (rule.toString().match(/^background\-image/)) {
            obj['names'] = [
                '_background_url',
                '_background_origin', 
                '_background_color1', 
                '_background_color2'
            ];
            obj['values'] = ['','','',''];
            if (false === (ctc_is_empty(value)) && !(value.toString().match(/url/))) {
                var params = value.toString().split(/:/);
                obj['values'][1] = ('undefined' == typeof params[0] ? '' : params[0]);
                obj['values'][2] = ('undefined' == typeof params[1] ? '' : params[1]);
                obj['values'][3] = ('undefined' == typeof params[3] ? '' : params[3]);
                obj['orig'] = [ obj['values'][1], obj['values'][2], obj['values'][3] ].join(' '); // display "origin color1 color2"
            } else {
                obj['values'][0] = value;
            }
        } else {
            obj['names']    = [''];
            obj['values']   = [ value ];
        }
        return obj;
    },
    
    ctc_set_query = function(value) {
        currentQuery = value;
        $('#ctc_sel_ovrd_query').val('');
        $('#ctc_sel_ovrd_query_selected').text(value);
        $('#ctc_sel_ovrd_selector').val('');
        $('#ctc_sel_ovrd_selector_selected').html('&nbsp;');
        $('#ctc_sel_ovrd_rule_inputs').html('');
        ctc_setup_selector_menu(value);
        ctc_coalesce_inputs('#ctc_child_all_0_swatch');
        $('#ctc_new_selector_row').show();
    },
    
    ctc_set_selector = function(value,label) {
        $('#ctc_sel_ovrd_selector').val('');
        $('#ctc_sel_ovrd_selector_selected').text(label);
        $('#ctc_sel_ovrd_qsid').val(value);
        currentSel = value;
        if (1 != loading.sel_val) loading.sel_val = 0;
        ctc_render_selector_inputs(value);
        $('#ctc_sel_ovrd_new_rule, #ctc_sel_ovrd_rule_header,#ctc_sel_ovrd_rule_inputs_container,#ctc_sel_ovrd_rule_inputs').show();
    },
    
    ctc_set_rule = function(value,label) {
        $('#ctc_rule_menu').val('');
        $('#ctc_rule_menu_selected').text(label);
        if (1 != loading.rule_val) loading.rule_val = 0;
        ctc_render_rule_value_inputs(value);
        $('#ctc_rule_value_inputs,#ctc_input_row_rule_header').show();
    },
    ctc_setup_query_menu = function() {
        ctc_queries = ctc_load_queries();
        $('#ctc_sel_ovrd_query').autocomplete({
            source: ctc_queries,
            minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function(e, ui) {
                ctc_set_query(ui.item.value);
                return false;
            },
            focus: function(e) { e.preventDefault(); }
        });
    },
    ctc_setup_selector_menu = function(query) {
        ctc_selectors = ctc_load_selectors(query);
        $('#ctc_sel_ovrd_selector').autocomplete({
            source: ctc_selectors,
            selectFirst: true,
            autoFocus: true,
            select: function(e, ui) {
                ctc_set_selector(ui.item.value, ui.item.label);
                return false;
            },
            focus: function(e) { e.preventDefault(); }
        });
    },
    ctc_setup_rule_menu = function() {
        ctc_rules = ctc_load_rules();
        $('#ctc_rule_menu').autocomplete({
            source: ctc_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function(e, ui) {
                ctc_set_rule(ui.item.value, ui.item.label);
                return false;
            },
            focus: function(e) { e.preventDefault(); }
        });
    },
    ctc_filtered_rules = function(request, response) {
        var arr = [],
            noval = (ctc_is_empty(ctcAjax.sel_val[currentSel])) || (ctc_is_empty(ctcAjax.sel_val[currentSel].value));
        if (ctc_is_empty(ctc_rules)) { 
            ctc_rules = ctc_load_rules();
        }
        $.each(ctc_rules, function(key, val){
            var skip = false,
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
            if (matcher.test( val.label )) {
                if (false === noval) {
                    // skip rule if in current selector array
                    $.each(ctcAjax.sel_val[currentSel].value, function(rule, value) {
                        if (val.label == rule) {
                            skip = true;
                            return false;
                        }
                    });
                    if (skip) {
                        return;
                    }
                }
                // add rule
                arr.push(val);
            }
        });
        response(arr);
    },
    ctc_setup_new_rule_menu = function() {
        $('#ctc_new_rule_menu').autocomplete({
            source: ctc_filtered_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function(e, ui) {
                $('#ctc_sel_ovrd_rule_inputs').append(ctc_render_child_rule_input(currentSel, ui.item.label, false)).find('.color-picker').each(function() {
                    ctc_setup_iris(this);
                });
                $('#ctc_new_rule_menu').val('');
                if (ctc_is_empty(ctcAjax.sel_val[currentSel].value)) {
                    ctcAjax.sel_val[currentSel]['value'] = {};
                }
                ctcAjax.sel_val[currentSel].value[ui.item.label] = {'child': ''};
                return false;
            },
            focus: function(e) { e.preventDefault(); }
        });
    },
    ctc_setup_menus = function() {
        ctc_setup_query_menu();
        ctc_setup_selector_menu(currentQuery);
        ctc_setup_rule_menu();
        ctc_setup_new_rule_menu();
    },
    ctc_theme_exists = function(testslug, testtype) {
        var exists = false;
        $.each(ctcAjax.themes, function(type, theme){
            $.each(theme, function(slug, data){
                if (slug == testslug && ('parnt' == type || 'new' == testtype)) {
                    exists = true;
                    return false;
                }
            });
            if (exists) return false;
        });
        return exists;
    },
    
    ctc_set_notice = function(noticearr) {
        var errorHtml = '';
        if (false === ctc_is_empty(noticearr)) {
            $.each(noticearr, function(type, list){
                errorHtml += '<div class="' + type + '"><ul>' + lf;
                $(list).each(function(ndx, el){
                    errorHtml += '<li>' + el.toString() + '</li>' + lf;
                });
                errorHtml += '</ul></div>';        
            });
        }
        $('#ctc_error_notice').html(errorHtml);
    },
    ctc_validate = function() {
        var regex = /[^\w\-]/,
            newslug = $('#ctc_child_template').val().toString().replace(regex).toLowerCase(),
            slug = $('#ctc_theme_child').val().toString().replace(regex).toLowerCase(),
            type = $('input[name=ctc_child_type]:checked').val(),
            errors = [];
        if ('new' == type) slug = newslug;
        if (ctc_theme_exists(slug, type)) {
            errors.push(ctcAjax.theme_exists_txt.toString().replace(/%s/, slug));
        }
        if ('' === slug) {
            errors.push(ctcAjax.inval_theme_txt);
        }
        if ('' === $('#ctc_child_name').val()) {
            errors.push(ctcAjax.inval_name_txt);
        }
        if (errors.length) {
            ctc_set_notice({'error': errors});
            return false;
        }
        return true;
    },
    ctc_set_theme_menu = function(e) {
        var slug = $('#ctc_theme_child').val();
        if (false === ctc_is_empty(ctcAjax.themes.child[slug])) {
            $('#ctc_child_name').val(ctcAjax.themes.child[slug].Name);
            $('#ctc_child_author').val(ctcAjax.themes.child[slug].Author);
            $('#ctc_child_version').val(ctcAjax.themes.child[slug].Version);
        }
    },
    fade_update_notice = function() {
        $('.updated, .error').slideUp('slow', function(){ $('.updated').remove(); });
    },
    // initialize vars
    // ajax semaphores: 0 = reload, 1 = loading, 2 = loaded
    loading = {
        'rule':     2,
        'sel_ndx':  2,
        'val_qry':  0,
        'rule_val': 0,
        'sel_val':  0
    },
    
    ctc_selectors       = [],
    ctc_queries         = [],
    ctc_rules           = [];
    // -- end var definitions
    
    // initialize Iris color picker    
    $('.color-picker').each(function() {
        ctc_setup_iris(this);
    });
    // bind event handlers
    $('.ctc-option-panel-container').on('focus', '.color-picker', function(){
        ctc_set_notice('')
        $(this).iris('toggle');
        $('.iris-picker').css({'position':'absolute', 'z-index':10});
    });
    $('.ctc-option-panel-container').on('focus', 'input', function() {
        ctc_set_notice('')
        $('.color-picker').not(this).iris('hide');
    });
    $('.ctc-option-panel-container').on('change', '.ctc-child-value', function() {
        ctc_coalesce_inputs(this);
    });
    $('.ctc-option-panel-container').on('click', '.ctc-selector-handle', function(e) {
        e.preventDefault();
        ctc_set_notice('')
        var id = $(this).attr('id').toString().replace('_close', ''),
            valid = id.toString().replace(/\D+/g, '');
        if ($('#' + id + '_container').is(':hidden')) {
            if (1 != loading.val_qry) loading.val_qry = 0;
            ctc_render_selector_value_inputs(valid);
        }
        $('#' + id + '_container').fadeToggle('fast');
        $('.ctc-selector-container').not('#' + id + '_container').fadeOut('fast');
    });
    $('.nav-tab').on('click', function(e){
        e.preventDefault();
        // clear the notice box
        ctc_set_notice('')
        var id = '#' + $(this).attr('id'), panelid = id + '_panel';
        $('.nav-tab').removeClass('nav-tab-active');
        $('.ctc-option-panel').removeClass('ctc-option-panel-active');
        $('.ctc-selector-container').hide();
        $(id).addClass('nav-tab-active');
        $('.ctc-option-panel-container').scrollTop(0);
        $(panelid).addClass('ctc-option-panel-active');
    });
    $('#view_child_options,#view_parnt_options').on('click', function(e){
        ctc_set_notice('')
        var stamp = new Date().getTime(),
            theme = $(this).attr('id').toString().match(/(child|parnt)/)[1],
            css_uri = ctcAjax.theme_uri + '/' + ctcAjax[theme] + '/style.css?' + stamp;
        $.get(
            css_uri,
            function(response){
                $('#view_'+theme+'_options_panel').text(response);
            }
        ).fail(function(){
            $('#view_'+theme+'_options_panel').text(ctcAjax.css_fail_txt);
        });
    });
    $('#ctc_load_form').on('submit', function() {
        return (ctc_validate() && confirm(ctcAjax.load_txt) ) ;
    });
    $('#parent_child_options_panel').on('change', '#ctc_theme_child', ctc_set_theme_menu );
    $(document).on('click', '.ctc-save-input', function(e) {
        ctc_save(this);
    });
    // initialize menus
    ctc_setup_menus();
    ctc_set_query(currentQuery);
    $('input[type=submit],input[type=button]').prop('disabled', false);
    setTimeout(fade_update_notice, 6000);
});


