/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.6.0
 *  Author: Lilaea Media
 *  Author URI: http://www.lilaeamedia.com/
 *  License: GPLv2
 *  Copyright (C) 2014 Lilaea Media
 */
jQuery(document).ready(function($){

    // initialize functions
    function esc_quot(str){
        return ctc_is_empty(str) ? str : str.toString().replace(quot_regex, '&quot;');
    }
    function ctc_setup_iris(obj) {
        console.log('setting up iris ' + ('undefined' != typeof $(obj).attr('id') ? $(obj).attr('id') : ''));
        $(obj).iris({
            change: function(e,ui) {
                console.log('change event ' 
                + ('undefined' != typeof $(this).attr('id') ? $(this).attr('id') : '') 
                + ' ' + ui.color.toString());
                $(obj).data('color', ui.color.toString());
                ctc_coalesce_inputs(obj);
            }
        });
    }
    function from_ascii(str) {
        var ascii = parseInt(str),
            chr = String.fromCharCode(ascii)
        return chr;
    }
    function to_ascii(str) {
        var ascii = str.charCodeAt(0);
        return ascii;
    }
    function ctc_coalesce_inputs(obj) {
        var regex       = /^(ctc_(ovrd|\d+)_(parent|child)_([0-9a-z\-]+)_(\d+))(_\w+)?$/,
            $container  = $(obj).parents('.ctc-selector-row, .ctc-parent-row').first(),
            $swatch     = $container.find('.ctc-swatch').first(),
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
        $container.find('.ctc-parent-value, .ctc-child-value').each(function(){
            var inputid     = $(this).attr('id'),
                inputparts  = inputid.toString().match(regex),
                inputseq    = inputparts[2],
                inputtheme  = inputparts[3],
                inputrule   = ('undefined' == typeof inputparts[4] ? '' : inputparts[4]),
                qsid        = inputparts[5],
                rulepart    = ('undefined' == typeof inputparts[6] ? '' : inputparts[6]),
                value       = ('parent' == inputtheme ? $(this).text().replace(/!$/,'') : $(this).val()),
                important   = 'ctc_' + inputseq + '_child_' + inputrule + '_i_' + qsid,
                parts, subparts;
            if (false === ctc_is_empty($(this).data('color'))) {
                value = $(this).data('color');
                $(this).data('color', null);
            }
            console.log('id: ' + inputid + ' value: ' + value);
            if ('child' == inputtheme) {
                postdata[inputid] = value;
                postdata[important] = ($('#' + important).is(':checked')) ? 1 : 0;
            }
            /*if ('' === value) {
                $('#'+important).prop('checked', false);
                return;
            }*/
            if ('' != value) {
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
                    } else if ('seq' != inputrule) {
                        cssrules[inputtheme][inputrule] = value;
                    }
                }
            }
        });
        // update swatch
        if ('undefined' != typeof $swatch && false === ctc_is_empty($swatch.attr('id'))) {
            $swatch.removeAttr('style');
            if (has_gradient.parent) { $swatch.ctcgrad(gradient.parent.origin, [gradient.parent.start, gradient.parent.end]); }
            console.log(cssrules.parent);
            $swatch.css(cssrules.parent);  
            if (!($swatch.attr('id').toString().match(/parent/))){
                if (has_gradient.child) { $swatch.ctcgrad(gradient.child.origin, [gradient.child.start, gradient.child.end]); }
            console.log(cssrules.child);
                $swatch.css(cssrules.child);
            }
            $swatch.css({'z-index':-1});
        }
        return postdata;
    }
    function ctc_update_cache(response) {
        var currQuery, currSelId, currRuleId;
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
                        if (ctc_is_empty(ctcAjax.sel_ndx[this.data.query])) {
                            ctcAjax.sel_ndx[this.data.query] = {}
                        } 
                        ctcAjax.sel_ndx[this.data.query][this.data.selector] = this.data.qsid;
                    } else { 
                        ctcAjax.sel_ndx[this.key] = this.data;
                        currQuery = this.key;
                    }
                    break;
                               
                case 'sel_val':
                    ctcAjax.sel_val[this.key] = this.data;
                    currSelId = this.key;
                    break; 
                case 'rewrite':
                    rewrite_id  = this.key;
                    rewrite_sel = this.data;
                    break;
            }
        });
    }
    function ctc_image_url(theme, value) {
        var parts = value.toString().match(/url\(['" ]*(.+?)['" ]*\)/),
            path = ctc_is_empty(parts) ? null : parts[1],
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
    }
    function ctc_is_empty(obj) {
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
    
    }
    function ctc_load_queries() {
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
    }
    function ctc_load_selectors(query) {
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
    }
    
    function ctc_load_rules() {
        var arr = [];
        if (1 === loading.rule) return arr;
        if (0 === loading.rule) { 
            loading.rule = 1;
            ctc_query_css('rule', null, ctc_setup_rule_menu);
            return arr;
        }
        if (false === ctc_is_empty(ctcAjax.rule)) { 
            $.each(ctcAjax.rule, function(key, value) {
                var obj = { label: value.replace(/\d+/g, from_ascii), value: key };
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
    }
    function ctc_render_child_rule_input(qsid, rule, seq) {
        var html        = '', 
            value       = (ctc_is_empty(ctcAjax.sel_val[qsid]) 
                || ctc_is_empty(ctcAjax.sel_val[qsid].value) 
                || ctc_is_empty(ctcAjax.sel_val[qsid].value[rule]) ? '' : ctcAjax.sel_val[qsid].value[rule]),
            oldRuleObj  = ctc_decode_value(rule, ('undefined' == typeof value ? '' : value['parnt'])),
            oldRuleFlag = (false === ctc_is_empty(value['i_parnt']) && value['i_parnt']) ? 
                ctcAjax.important_label : '',
            newRuleObj  = ctc_decode_value(rule, ('undefined' == typeof value ? '' : value['child'])),
            newRuleFlag = (false === ctc_is_empty(value['i_child']) && value['i_child']) ? 1 : 0,
            impid = 'ctc_' + seq + '_child_' + rule + '_i_' + qsid;
        if (false === ctc_is_empty(ctcAjax.sel_val[qsid])) {
            html += '<div class="ctc-' + ('ovrd' == seq ? 'input' : 'selector' ) + '-row clearfix">' + lf;
            html += '<div class="ctc-input-cell">' + ('ovrd' == seq ? rule.replace(/\d+/g, from_ascii) : ctcAjax.sel_val[qsid].selector 
                + '<br/><a href="#" class="ctc-selector-edit" id="ctc_selector_edit_' + qsid + '" >' + ctcAjax.edit_txt + '</a> '
                + (ctc_is_empty(oldRuleObj.orig) ? ctcAjax.child_only_txt : '')) 
                + '</div>' + lf;
            if ('ovrd' == seq) {
                html += '<div class="ctc-parent-value ctc-input-cell" id="ctc_' + seq + '_parent_' + rule + '_' + qsid + '">' 
                + (ctc_is_empty(oldRuleObj.orig) ? '[no value]' : oldRuleObj.orig + oldRuleFlag) + '</div>' + lf;
            }
            html += '<div class="ctc-input-cell">' + lf;
            if (false === ctc_is_empty(oldRuleObj.names)){
                $.each(oldRuleObj.names, function(ndx, newname) {
                    newname = (ctc_is_empty(newname) ? '' : newname);
                    html += '<div class="ctc-child-input-cell">' + lf;
                    var id = 'ctc_' + seq + '_child_' + rule + '_' + qsid + newname,
                        newval;
                    if (false === (newval = newRuleObj.values.shift()) ){
                        newval = '';
                    }
                        
                    html += (ctc_is_empty(newname) ? '' : ctcAjax.field_labels[newname] + ':<br/>') 
                        + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                        + ((newname + rule).toString().match(/color/) ? ' color-picker' : '') 
                        + ((newname).toString().match(/url/) ? ' ctc-input-wide' : '')
                        + '" value="' + esc_quot(newval) + '" />' + lf;
                    html += '</div>' + lf;
                });
                html += '<label for="' + impid + '"><input type="checkbox" id="' + impid + '" name="' + impid + '" value="1" '
                    + (1 === newRuleFlag ? 'checked' : '') + ' />' + ctcAjax.important_label + '</label>' + lf;
            }
            html += '</div>' + lf;
            html += ('ovrd' == seq ? '' : '<div class="ctc-swatch ctc-specific" id="ctc_child_' + rule + '_' + qsid + '_swatch">' 
                + ctcAjax.swatch_txt + '</div>' + lf 
                + '<div class="ctc-child-input-cell ctc-button-cell" id="ctc_save_' + rule + '_' + qsid + '_cell">' + lf
                + '<input type="button" class="button ctc-save-input" id="ctc_save_' + rule + '_' + qsid 
                + '" name="ctc_save_' + rule + '_' + qsid + '" value="Save" /></div>' + lf);
            html += '</div><!-- end input row -->' + lf;
        }
        return html;
    }
    function ctc_render_selector_inputs(qsid) {
        if (1 === loading.sel_val) {
            return false;
        }
        if (0 == loading.sel_val) { 
            loading.sel_val = 1;
            ctc_query_css('sel_val', qsid, ctc_render_selector_inputs);
            return false;
        }
        var id, html, val;
        if (ctc_is_empty(ctcAjax.sel_val[qsid])) {
            $('#ctc_sel_ovrd_rule_inputs').html('')
        } else {
            if (ctc_is_empty(ctcAjax.sel_val[qsid].seq)) {
                $('#ctc_child_load_order_container').html('');
            } else {
                id = 'ctc_ovrd_child_seq_' + qsid;
                val = parseInt(ctcAjax.sel_val[qsid].seq);
                html = '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value" value="' + val + '" />';
                $('#ctc_child_load_order_container').html(html);
            }
            if (ctc_is_empty(ctcAjax.sel_val[qsid].value)){
                $('#ctc_sel_ovrd_rule_inputs').html('');
            } else {
                html = '';
                $.each(ctcAjax.sel_val[qsid].value, function(rule, value) {
                    html += ctc_render_child_rule_input(qsid, rule, 'ovrd');
                });        
                $('#ctc_sel_ovrd_rule_inputs').html(html).find('.color-picker').each(function() {
                    ctc_setup_iris(this);
                });
                ctc_coalesce_inputs('#ctc_child_all_0_swatch');
            }
        }
    }
    function ctc_render_css_preview(theme) {
        if (1 === loading.preview) {
            return false;
        }
        if (0 == loading.preview) { 
            loading.preview = 1;
            var theme;
            if (!(theme = $(this).attr('id').toString().match(/(child|parnt)/)[1])) {
                theme = 'child';
            }
            ctc_set_notice('')
            ctc_query_css('preview', theme, ctc_render_css_preview);
            return false;
        }
        if (2 == loading.preview) {
            $('#view_'+theme+'_options_panel').text(ctcAjax.previewResponse); 
            loading.preview = 0;       
        }
    }
    function ctc_render_rule_value_inputs(ruleid) {
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
                html += '<div class="ctc-input-cell ctc-parent-value" id="ctc_' + valid + '_parent_' + rule + '_' + valid + '">' 
                    + oldRuleObj.orig + '</div>' + lf;
                html += '<div class="ctc-input-cell">' + lf;
                html += '<div class="ctc-swatch ctc-specific" id="ctc_' + valid + '_parent_' + rule + '_' + valid + '_swatch">' 
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
    }
    function ctc_render_selector_value_inputs(valid) {
        if (1 == loading.val_qry) return false;
        var params, 
            page_ruleid, 
            rule = $('#ctc_rule_menu_selected').text().replace(/[^\w\-]/g, to_ascii), 
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
                            html += ctc_render_child_rule_input(qsid, rule, valid);
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

    }
    function ctc_query_css(obj, key, callback, params) {
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
                // console.log(response);
                // hide spinner
                loading[obj] = 2;
                $('.ctc-status-icon').removeClass('spinner');
                // show check mark
                if (ctc_is_empty(response)) {
                    $('.ctc-status-icon').addClass('failure');
                    if ('preview' == obj) {
                        ctcAjax.previewResponse = ctcAjax.css_fail_txt;
                        callback(key);
                    }
                } else {
                    $('.ctc-status-icon').addClass('success');
                    if ('preview' == obj) {
                        ctcAjax.previewResponse = response.shift().data;
                    } else {
                        // update data objects   
                        ctc_update_cache(response);
                    }
                    if ('function' === typeof callback) {
                        callback(key);
                    }
                    return false;  
                }
            },'json'
        ).fail(function(){
            // hide spinner
            $('.ctc-status-icon').removeClass('spinner');
            // show check mark
            $('.ctc-status-icon').addClass('failure');
            if ('preview' == obj) {
                ctcAjax.previewResponse = ctcAjax.css_fail_txt;
                loading[obj] = 2;
                callback(key);
            } else {
                loading[obj] = 0;
            }
            
        });  
        return false; 
    }
    function ctc_save(obj) {
        var postdata = {},
            $selector, $query, $imports, $rule,
            id = $(obj).attr('id'), newsel;
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
        // add rename selector value if it exists
        $('#ctc_sel_ovrd_selector_selected').find('#ctc_rewrite_selector').each(function(){
            newsel = $('#ctc_rewrite_selector').val(),
                origsel = $('#ctc_rewrite_selector_orig').val();
            if (ctc_is_empty(newsel) || !newsel.toString().match(/\w/)) {
                newsel = origsel;
            } else {
                postdata['ctc_rewrite_selector'] = newsel;
            }
            $('.ctc-rewrite-toggle').text(ctcAjax.rename_txt);
            $('#ctc_sel_ovrd_selector_selected').html(newsel);
        });
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
                    if (false === ctc_is_empty(rewrite_id)) {
                        ctc_set_selector(rewrite_id, rewrite_sel);
                        rewrite_id = rewrite_sel = null;
                    }
                }
                return false;  
            }, 'json'
        ).fail(function(){
            // release button
            $(obj).prop('disabled', false);
            // hide spinner
            $('.ctc-status-icon').removeClass('spinner');
            // show check mark
            $('.ctc-status-icon').addClass('failure');
        });  
        return false;  
    }
    function ctc_decode_value(rule, value) {
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
    }
    function ctc_set_query(value) {
        currentQuery = value;
        $('#ctc_sel_ovrd_query').val('');
        $('#ctc_sel_ovrd_query_selected').text(value);
        $('#ctc_sel_ovrd_selector').val('');
        $('#ctc_sel_ovrd_selector_selected').html('&nbsp;');
        $('#ctc_sel_ovrd_rule_inputs').html('');
        ctc_setup_selector_menu(value);
        ctc_coalesce_inputs('#ctc_child_all_0_swatch');
        $('#ctc_new_selector_row').show();
    }
    function ctc_set_selector(value,label) {
        $('#ctc_sel_ovrd_selector').val('');
        $('#ctc_sel_ovrd_selector_selected').text(label);
        $('#ctc_sel_ovrd_qsid').val(value);
        currentSel = value;
        if (1 != loading.sel_val) loading.sel_val = 0;
        ctc_render_selector_inputs(value);
        $('.ctc-rewrite-toggle').text(ctcAjax.rename_txt);
        $('#ctc_sel_ovrd_new_rule, #ctc_sel_ovrd_rule_header,#ctc_sel_ovrd_rule_inputs_container,#ctc_sel_ovrd_rule_inputs,.ctc-rewrite-toggle').show();
    }
    function ctc_set_rule(value,label) {
        $('#ctc_rule_menu').val('');
        $('#ctc_rule_menu_selected').text(label);
        if (1 != loading.rule_val) loading.rule_val = 0;
        ctc_render_rule_value_inputs(value);
        $('.ctc-rewrite-toggle').text(ctcAjax.rename_txt);
        $('#ctc_rule_value_inputs,#ctc_input_row_rule_header').show();
    }
    function ctc_setup_query_menu() {
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
            focus: function(e) { 
                e.preventDefault(); 
            }
        });
    }
    function ctc_setup_selector_menu(query) {
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
    }
    function ctc_setup_rule_menu() {
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
    }
    function ctc_filtered_rules(request, response) {
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
                        if (val.label == rule.replace(/\d+/g, from_ascii)) {
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
    }
    function ctc_setup_new_rule_menu() {
        $('#ctc_new_rule_menu').autocomplete({
            source: ctc_filtered_rules,
            //minLength: 0,
            selectFirst: true,
            autoFocus: true,
            select: function(e, ui) {
                e.preventDefault();
                var n = $(ctc_render_child_rule_input(currentSel, ui.item.label.replace(/[^\w\-]/g, to_ascii), 'ovrd'));
                $('#ctc_sel_ovrd_rule_inputs').append(n);
                $('#ctc_new_rule_menu').val('');
                if (ctc_is_empty(ctcAjax.sel_val[currentSel].value)) {
                    ctcAjax.sel_val[currentSel]['value'] = {};
                }
                ctcAjax.sel_val[currentSel].value[ui.item.label] = {'child': ''};
                n.find('input[type="text"]').each(function(ndx, el){
                    if ($(el).hasClass('color-picker'))
                        ctc_setup_iris(el);
                    $(el).focus();
                });
                return false;
            },
            focus: function(e) { e.preventDefault(); }
        });
    }
    function ctc_setup_menus() {
        ctc_setup_query_menu();
        ctc_setup_selector_menu(currentQuery);
        ctc_setup_rule_menu();
        ctc_setup_new_rule_menu();
    }
    function ctc_theme_exists(testslug, testtype) {
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
    }
    function autogen_slugs() {
        var parent  = $('#ctc_theme_parnt').val(),
            slug    = slugbase = parent + '-child',
            name    = ctcAjax.themes.parnt[parent].Name + ' Child',
            suffix  = '',
            padded  = '',
            pad     = '00';
        while (ctc_theme_exists(slug, 'new')) {
            suffix  = ('' == suffix ? 2 : suffix + 1);
            padded  = pad.substring(0, pad.length - suffix.toString().length) + suffix.toString();
            slug    = slugbase + padded;
        }
        testslug = slug;
        testname = name + (padded.length ? ' ' + padded : '');
    }
    function ctc_set_notice(noticearr) {
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
    }
    function ctc_validate() {
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
    }
    function ctc_set_parent_menu(obj) {
        $('#ctc_theme_parent').parents('.ctc-input-row').first().append('<span class="ctc-status-icon spinner"></span>');
        $('.spinner').show();
        document.location='?page=chld_thm_cfg_menu&ctc_parent=' + obj.value;
    }
    function ctc_set_child_menu(obj) {
        if (false === ctc_is_empty(ctcAjax.themes.child[obj.value])) {
            $('#ctc_child_name').val(ctcAjax.themes.child[obj.value].Name);
            $('#ctc_child_author').val(ctcAjax.themes.child[obj.value].Author);
            $('#ctc_child_version').val(ctcAjax.themes.child[obj.value].Version);
        }
    }
    function fade_update_notice() {
        $('.updated, .error').slideUp('slow', function(){ $('.updated').remove(); });
    }
    function ctc_set_addl_css() { 
        var template    = $('#ctc_theme_parnt').val(),
            theme_uri   = ctcAjax.theme_uri.replace(/^https?:\/\//, ''),
            homeurl     = ctcAjax.homeurl.replace(/^https?/, ctcAjax.ssl ? 'https' : 'http'),
            url         = homeurl + '?preview=1&p=x&template=' + template + '&stylesheet=' + template,
            regex       = new RegExp("<link rel=[\"']stylesheet[\"'][^>]+?" + theme_uri + '/' + template + '/(.+?\\.css)[^>]+?>', 'g'),
            additional;
        if (ctc_is_empty(template)) return;
        $.get(url, function(data){
            while (additional = regex.exec(data)){
                if ('style.css' == additional[1]) break; // bail after main stylesheet
                if (additional[1].match(/bootstrap/)) continue; // don't autoselect Bootstrap stylesheets
                ctcAjax.addl_css.push(additional[1]);
                $('.ctc_checkbox').each(function(ndx,el){
                    if ($(this).val() == additional[1]) $(this).prop('checked', true);
                });
            }
        });
    }
    function ctc_focus_panel(id) {
        var panelid = id + '_panel';
        $('.nav-tab').removeClass('nav-tab-active');
        $('.ctc-option-panel').removeClass('ctc-option-panel-active');
        $('.ctc-selector-container').hide();
        $(id).addClass('nav-tab-active');
        $('.ctc-option-panel-container').scrollTop(0);
        $(panelid).addClass('ctc-option-panel-active');
    }
    function ctc_selector_edit(obj) {
        var qsid = $(obj).attr('id').match(/_(\d+)$/)[1],
            q = ctcAjax.sel_val[qsid].query,
            s = ctcAjax.sel_val[qsid].selector,
            id = '#query_selector_options';
        ctc_set_query(q);
        ctc_set_selector(qsid, s);
        ctc_focus_panel(id);        
    }
    function ctc_selector_input_toggle(obj) {
        var origval;
        if ($('#ctc_rewrite_selector').length) {
            origval = $('#ctc_rewrite_selector_orig').val();
            $('#ctc_sel_ovrd_selector_selected').text(origval);
            $(obj).text(ctcAjax.rename_txt);
        } else {
            origval = $('#ctc_sel_ovrd_selector_selected').text();
            $('#ctc_sel_ovrd_selector_selected').html('<input id="ctc_rewrite_selector" name="ctc_rewrite_selector" type="text" value="' 
                + esc_quot(origval) + '" autocomplete="off" /><input id="ctc_rewrite_selector_orig" name="ctc_rewrite_selector_orig" type="hidden" value="' 
                + esc_quot(origval) + '"/>');
            $(obj).text(ctcAjax.cancel_txt);
        }
    }
    // initialize vars
    var lf = "\n", 
        currentQuery = 'base',
        currentSel,
        saveEvents = {},
        rewrite_id, 
        rewrite_sel,
        quot_regex = new RegExp('"', 'g'),
        testslug    = '',
        testname    = '',
        // ajax semaphores: 0 = reload, 1 = loading, 2 = loaded
        loading = {
            'rule':     2,
            'sel_ndx':  2,
            'val_qry':  0,
            'rule_val': 0,
            'sel_val':  0,
            'preview':  0
        },
        ctc_selectors       = [],
        ctc_queries         = [],
        ctc_rules           = [];
    // -- end var definitions
    
    // initialize theme menus
    autogen_slugs();
    $.widget('ctc.themeMenu', $.ui.selectmenu, {
        _renderItem: function( ul, item ) {
            var li = $( "<li>" );
            $('#ctc_theme_option_' + item.value).detach().appendTo(li);
            return li.appendTo( ul );
        }    
    });
    $('#ctc_theme_parnt').themeMenu({
        select: function( event, ui ) {
            ctc_set_parent_menu(ui.item);
        }
    });
    if ( ctc_is_empty( ctcAjax.themes.child ) ) {
        $('#ctc_child_name').val(testname);
        $('#ctc_child_template').val(testslug);
    } else {
        $('#ctc_theme_child').themeMenu({
            select: function( event, ui ) {
                ctc_set_child_menu(ui.item);
            }
        });
    }

    // bind event handlers
    
    $('.ctc-option-panel-container').on('focus', '.color-picker', function(){
        ctc_set_notice('')
        $('.color-picker').not(this).iris('hide');
        $(this).iris('toggle');
        $('.iris-picker').css({'position':'absolute', 'z-index':10});
    });
    
    $('.ctc-option-panel-container').on('change', '.ctc-child-value, input[type=checkbox]', function() {
        ctc_coalesce_inputs(this);
    });
    
    $('.ctc-option-panel-container').on('click', '.ctc-selector-handle', function(e) {
        e.preventDefault();
        ctc_set_notice('')
        var id = $(this).attr('id').toString().replace('_close', ''),
            valid = id.toString().match(/_(\d+)$/)[1];
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
        ctc_set_notice('');
        $('.ctc-status-icon').remove();
        var id = '#' + $(this).attr('id');
        ctc_focus_panel(id);
    });
    $('#view_child_options,#view_parnt_options').on('click', ctc_render_css_preview);
    $('#ctc_load_form').on('submit', function() {
        return (ctc_validate() && confirm(ctcAjax.load_txt) ) ;
    });
    $(document).on('click', '.ctc-save-input', function(e) {
        ctc_save(this);
    });
    $(document).on('click', '.ctc-selector-edit', function(e) {
        ctc_selector_edit(this);
    });
    $(document).on('click', '.ctc-rewrite-toggle', function(e) {
        e.preventDefault();
        ctc_selector_input_toggle(this);
    });
    $(document).on('click', '.ctc-section-toggle', function(e){
        $(this).toggleClass('open');
        var id = $(this).attr('id') + '_content';
        $('#' + id).slideToggle('fast');
    });
    $(document).on('click', '.ctc-live-preview', function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        document.location = $(this).prop('href');
        return false;
    });
    $(document).on('change', '#ctc_configtype', function(e) {
        if ('theme' == $(this).val()) {
            $('.ctc-theme-only').stop().slideDown('fast');
        } else {
            $('.ctc-theme-only').stop().slideUp('fast');
        }
    });
    $('#ctc_theme_child,#ctc_theme_child-button,#ctc_child_type_existing').on('focus click', function(){
        $('#ctc_child_type_existing').prop('checked', true);
        $('#ctc_child_type_new').prop('checked', false);
        $('#ctc_child_template').val('');
    });
    $('#ctc_child_type_new,#ctc_child_template').on('focus click', function(){
        $('#ctc_child_type_existing').prop('checked', false);
        $('#ctc_child_type_new').prop('checked', true);
        $('#ctc_child_name').val(testname);
        $('#ctc_child_template').val(testslug);
    });
    // initialize menus
    ctc_setup_menus();
    ctc_set_query(currentQuery);
    ctc_set_addl_css();
    $('input[type=submit],input[type=button]').prop('disabled', false);
    setTimeout(fade_update_notice, 6000);
});