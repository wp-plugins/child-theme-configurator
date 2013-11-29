/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.0.1
 *  Author: Lilaea Media
 *  Author URI: http://www.lilaeamedia.com/
 *  License: GPLv2
 *  Copyright (C) 2013 Lilaea Media
 */
(function($){

    var lf = "\n", 
    
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
                inputparts  = inputid.match(regex),
                inputtheme  = inputparts[3],
                inputrule   = (undefined == inputparts[4] ? '' : inputparts[4]),
                selnum      = inputparts[5],
                rulepart    = (undefined == inputparts[6] ? '' : inputparts[6]),
                value       = ('parent' == inputtheme ? $(this).text() : $(this).val()),
                parts, subparts;
            if ('child' == inputtheme) {
                postdata[inputid] = value;
            }
            if (ctc_is_empty(value)) return;
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
                if (parts = inputrule.match(/^border(\-(top|right|bottom|left))?$/) && !value.match(/none/)) {
                    subparts = value.split(/ +/);
                    cssrules[inputtheme][inputrule + '-width'] = undefined == subparts[0] ? '' : subparts[0];
                    cssrules[inputtheme][inputrule + '-style'] = undefined == subparts[1] ? '' : subparts[1];
                    cssrules[inputtheme][inputrule + '-color'] = undefined == subparts[2] ? '' : subparts[2];
                // handle background images
                } else if ( 'background-image' == inputrule ) {
                    if (value.match(/url\(/)) {
                        cssrules[inputtheme]['background-image'] = ctc_image_url(inputtheme, value);
                    } else {
                        subparts = value.split(/ +/);
                        if (subparts.length > 2) {
                            gradient[inputtheme].origin = undefined == subparts[0] ? 'top' : subparts[0];
                            gradient[inputtheme].start  = undefined == subparts[1] ? 'transparent' : subparts[1];
                            gradient[inputtheme].end    = undefined == subparts[2] ? 'transparent' : subparts[2];
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
        if (undefined != $swatch) {
            $($swatch).removeAttr('style');
            if (has_gradient.parent) { $($swatch).ctcgrad(gradient.parent.origin, [gradient.parent.start, gradient.parent.end]); }
            $($swatch).css(cssrules.parent);  
            if (!($swatch.attr('id').match(/parent/))){
                if (has_gradient.child) { $($swatch).ctcgrad(gradient.child.origin, [gradient.child.start, gradient.child.end]); }
                $($swatch).css(cssrules.child);
            }
        }
        return postdata;
    },
    ctc_apply_updates = function(obj) {
        var currQuery, currSelnum, currRule;
        if (undefined != obj.imports) {
            ctcAjax.imports[ctcAjax.child_theme] = obj.imports;
        }
        $(obj.del).each(function(){
            if (undefined != this.selnum) {
                delete ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query][this.selnum];
            } else if (undefined != this.query) {
                delete ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query];
            } else if (undefined != this.value) {
                delete ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value];
            } else if (undefined != this.rule) {
                delete ctcAjax.val_ndx[ctcAjax.child_theme][this.rule];
            }
            if (undefined != this.query) currQuery = this.query;
            if (undefined != this.selnum) currSelnum = this.selnum;
            if (undefined != this.rule) currRule = this.rule;
        });
        $(obj.insert).each(function(){
            ctcAjax.sel_ndx[this.query][this.selector] = this.selnum;
            ctcAjax.data[this.selnum] = {
                'selector': this.selector,
                'query':    this.query,
                'value':    {}
            };
            if (undefined != this.query) currQuery = this.query;
            if (undefined != this.selnum) currSelnum = this.selnum;
            if (undefined != this.rule) currRule = this.rule;
        });
        $(obj.update).each(function(){
            if (undefined == ctcAjax.val_ndx[ctcAjax.child_theme]) {
                ctcAjax.val_ndx[ctcAjax.child_theme] = {};
            }
            if (undefined == ctcAjax.val_ndx[ctcAjax.child_theme][this.rule]) {
                ctcAjax.val_ndx[ctcAjax.child_theme][this.rule] = {};
            } 
            if (this.value && undefined == ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value]) {
                ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value] = {};
            } 
            if (this.value && undefined == ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query]) {
                ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query] = {};
            } 
            if (this.value && undefined == ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query][this.selnum]) {
                ctcAjax.val_ndx[ctcAjax.child_theme][this.rule][this.value][this.query][this.selnum] = 0;
            }
            if (this.rule && undefined == ctcAjax.data[this.selnum].value[this.rule]) {
                ctcAjax.data[this.selnum].value[this.rule] = {};
                //ctcAjax.val_ndx[ctcAjax.child_theme][this.rule] = {};
            }
            ctcAjax.data[this.selnum].value[this.rule][ctcAjax.child_theme] = this.value + (this.important > 0 ? ' !important':'');
            if (undefined != this.query) currQuery = this.query;
            if (undefined != this.selnum) currSelnum = this.selnum;
            if (undefined != this.rule) currRule = this.rule;
        });
        // refresh page with new values if available
        if (currQuery) { 
            ctc_set_query(currQuery, currQuery);
        }
        if (currSelnum) {
            ctc_set_selector(currSelnum, ctcAjax.data[currSelnum].selector);
        }
        if (currRule) {
            ctc_set_rule(currRule, currRule);
        }
    },
    ctc_image_url = function(theme, value) {
        var parts = value.match(/url\([" ]*(.+?)[" ]*\)/),
            path = (undefined == parts ? null : parts[1]),
            url = ctcAjax.theme_uri + '/' + ('parent' == theme ? ctcAjax.parent_theme : ctcAjax.child_theme) + '/',
            image_url;
        if (!path) { 
            return false; 
        } else if (path.match(/^(http:|\/)/)) { 
            image_url = value; 
        } else { 
            image_url = 'url(' + url + path + ')'; 
        }
        return image_url;
    },
    
    ctc_is_empty = function(obj) {
        // first bail when definitely empty or undefined (true)
        if (undefined == obj || false === obj || null === obj || '' === obj || 0 === obj) { return true; }
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
        if (false === ctc_is_empty(ctcAjax.sel_ndx)){
            $.each(ctcAjax.sel_ndx, function(key, value) {
                obj = { label: key, value: key };
                arr.push(obj);
            });
        }
        return arr;
    },
    
    ctc_load_selectors = function(query) {
        var arr = [];
        if (false === ctc_is_empty(ctcAjax.sel_ndx[query])){
            $.each(ctcAjax.sel_ndx[query], function(key, value) {
                obj = { label: key, value: value };
                arr.push(obj);
            });
        }
        return arr;
    },
    
    ctc_load_rules = function() {
        var obj = {},
            arr = [];
        if (false === ctc_is_empty(ctcAjax.val_ndx[ctcAjax.parent_theme])) {
            $.each(ctcAjax.val_ndx[ctcAjax.parent_theme], function(key, value) {
                obj[key]++;
            });
        }
        if (false === ctc_is_empty(ctcAjax.val_ndx[ctcAjax.child_theme])) {
            $.each(ctcAjax.val_ndx[ctcAjax.child_theme], function(key, value) {
                obj[key]++;
            });
        }
        $.each(['border-width', 'border-style', 'border-color', 'padding', 'margin', 'background', 'font'], function() {
            obj[this]++;
        });
        if (false === ctc_is_empty(obj)){
            $.each(obj, function(key, value) {
                arr.push(key);
            });
        }
        return arr.sort();
    },
    
    ctc_render_child_rule_input = function(selnum, rule, specific) {
        var html = '', 
            value = (undefined == ctcAjax.data[selnum].value || undefined == ctcAjax.data[selnum].value[rule] ? '' : ctcAjax.data[selnum].value[rule]),
            oldRuleObj = ctc_decode_value(rule, (undefined == value ? '' : value[ctcAjax.parent_theme])),
            newRuleObj = ctc_decode_value(rule, (undefined == value ? '' : value[ctcAjax.child_theme]));
        if (value) {
            unique_rule_value[rule + '%%' + value[ctcAjax.parent_theme]] = 1;
            unique_rule_value[rule + '%%' + value[ctcAjax.child_theme]] = 1;
        }
        html += '<div class="ctc-' + (specific ? 'selector' : 'input' ) + '-row clearfix">' + lf;
        html += '<div class="ctc-input-cell">' + (specific ? ctcAjax.data[selnum].selector : rule) + '</div>' + lf;
        html += '<div class="ctc-parent-value' + (specific ? ' ctc-hidden' : ' ctc-input-cell') +'" id="ctc_parent_' + rule + '_' + selnum + '">' 
            + (ctc_is_empty(oldRuleObj.orig) ? '[no value]' : oldRuleObj.orig) + '</div>' + lf;
        html += '<div class="ctc-input-cell">' + lf;
        if (false === ctc_is_empty(oldRuleObj.names)){
            $.each(oldRuleObj.names, function(ndx, newname) {
                newname = (ctc_is_empty(newname) ? '' : newname);
                html += '<div class="ctc-child-input-cell">' + lf;
                var id = 'ctc_' + (specific? '' : 'ovrd_') + 'child_' + rule + '_' + selnum + newname,
                    newval;
                if (!(newval = newRuleObj.values.shift()) ){
                    newval = '';
                }
                        
                html += (ctc_is_empty(newname) ? '' : ctcAjax.labels[newname] + ':<br/>') 
                    + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                    + ((newname + rule).match(/color/) ? ' color-picker' : '') 
                    + ((newname).match(/url/) ? ' ctc-input-wide' : '')
                    + '" value="' + newval + '" />' + lf;
                html += '</div>' + lf;
            });
        }
        html += '</div>' + lf;
        html += (specific ? '<div class="ctc-swatch ctc-specific" id="ctc_child_' + rule + '_' + selnum + '_swatch">' 
            + ctcAjax.swatch_text + '</div>' + lf 
            + '<div class="ctc-child-input-cell ctc-button-cell" id="ctc_save_' + rule + '_' + selnum + '_cell">' + lf
            + '<input type="button" class="button ctc-save-input" id="ctc_save_' + rule + '_' + selnum 
            + '" name="ctc_save_' + rule + '_' + selnum + '" value="Save" /></div>' + lf : '');
        html += '</div><!-- end input row -->' + lf;
        return html;
    },
    
    ctc_render_selector_inputs = function(selnum) {
        if (undefined == ctcAjax.data[selnum].value) return;
        var html = '', counter = 0;
        if (false === ctc_is_empty(ctcAjax.data[selnum].value)){
            $.each(ctcAjax.data[selnum].value, function(rule, value) {
                html += ctc_render_child_rule_input(selnum, rule, false);
            });
        }
        $('#ctc_sel_ovrd_rule_inputs').html(html).find('.color-picker').each(function() {
            ctc_setup_iris(this);
        });
        ctc_coalesce_inputs('#ctc_child_all_0_swatch');
    }

    ctc_render_rule_value_inputs = function(rule) {
        var html = '<div class="ctc-input-row clearfix" id="ctc_rule_row_' + rule + '">' + lf, 
            valID = 0, 
            themes = {parent: ctcAjax.parent_theme, child: ctcAjax.child_theme};
        unique_rule_value = {},

        $.each(themes, function(ndx, theme) {
            if (ctc_is_empty(ctcAjax.val_ndx[theme]) || undefined == ctcAjax.val_ndx[theme][rule]) return;
            $.each(ctcAjax.val_ndx[theme][rule], function(value, sel) {
                if (unique_rule_value[rule + '%%' + value]) {
                    return;
                } else {
                    valID++;
                    oldRuleObj = ctc_decode_value(rule, value),
                    html += '<div class="ctc-parent-row clearfix" id="ctc_rule_row_' + rule + '_' + valID + '">' + lf;
                    html += '<div class="ctc-input-cell ctc-parent-value" id="ctc_parent_' + rule + '_' + valID + '">' 
                        + oldRuleObj.orig + '</div>' + lf;
                    html += '<div class="ctc-input-cell">' + lf;
                    html += '<div class="ctc-swatch ctc-specific" id="ctc_parent_'+rule+'_' + valID + '_swatch">' 
                        + ctcAjax.swatch_text + '</div></div>' + lf;
                    html += '<div class="ctc-input-cell"><a href="#" class="ctc-selector-handle" id="ctc_selector_' + rule + '_' + valID + '">'
                        + ctcAjax.selector_text + '</a></div>' + lf;
                    html += '<div id="ctc_selector_' + rule + '_' + valID + '_container" class="ctc-selector-container clearfix">' + lf;
                    html += '<a href="#" id="ctc_selector_' + rule + '_' + valID + '_close" class="ctc-selector-handle" style="float:right">' 
                        + ctcAjax.close_text + '</a>' + lf;
                        html += ctc_render_selector_value_inputs(rule, sel);
                    html += '</div></div>' + lf;
                }
            });
        });
        html += '</div>' + lf;
        $('#ctc_rule_value_inputs').html(html).find('.color-picker').each(function() {
            ctc_setup_iris(this);
        });
        $('#ctc_rule_value_inputs').find('.ctc-swatch').each(function() {
            ctc_coalesce_inputs(this);
        });
    },
    
    ctc_render_selector_value_inputs = function(rule, sel) {
        var html = '', lastquery = '', query;
        if (false === ctc_is_empty(sel)){
            $.each(sel, function(query, selectors) {
                if (query != lastquery) { 
                    lastquery = query; 
                    html += '<h4 class="ctc-query-heading">' + query + '</h4>' + lf; //queryparts[1]
                }
                if (false === ctc_is_empty(selectors)){
                    $.each(selectors, function(selnum, zero) {
                        html += ctc_render_child_rule_input(selnum, rule, true);
                    });
                }
            });
        }
        return html;
    },
    
    ctc_save = function(obj) {
        var postdata = {},
            $selector, $query, $imports, $rule;
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
                    ctc_apply_updates(response);
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
    
    ctc_serialize = function(obj) {
        var serialized;
        if (undefined == obj) { 
            serialized = ''; 
        } else if ('string' === typeof obj || 'number' === typeof obj) { 
            serialized = obj; 
        } else if ('object' === typeof obj) {
            serialized = '';
            $.each(obj, function(ndx,el){
                serialized += ndx + ': ' + el + ',' + "\n";
            });
        }
        return serialized;
    },
    
    ctc_decode_value = function(rule, value) {
        value = (undefined == value ? '' : value);
        var obj = { 'orig':   value };
        if (rule.match(/^border(\-(top|right|bottom|left))?$/)) {
            var params = value.split(/ +/);
            obj['names'] = [
                '_border_width',
                '_border_style',
                '_border_color',
            ];
            obj['values'] = [ 
                (undefined == params[0] ? '' : params[0]),
                (undefined == params[1] ? '' : params[1]),
                (undefined == params[2] ? '' : params[2])
            ];
        } else if (rule.match(/^background\-image/)) {
            obj['names'] = [
                '_background_url',
                '_background_origin', 
                '_background_color1', 
                '_background_color2'
            ];
            obj['values'] = ['','','',''];
            if (value.match(/:/)) {
                var params = value.split(/:/);
                obj['values'][1] = (undefined == params[0] ? '' : params[0]);
                obj['values'][2] = (undefined == params[1] ? '' : params[1]);
                obj['values'][3] = (undefined == params[3] ? '' : params[3]);
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
    
    ctc_set_query = function(value, label) {
        $('#ctc_sel_ovrd_query').val('');
        $('#ctc_sel_ovrd_query_selected').text(label);
        ctc_selectors = ctc_load_selectors(value);
        $('#ctc_sel_ovrd_selector').autocomplete('option', { source: ctc_selectors }); 
        $('#ctc_new_selector_row').show();
    },
    
    ctc_set_selector = function(value,label) {
        $('#ctc_sel_ovrd_selector').val('');
        $('#ctc_sel_ovrd_selector_selected').text(label);
        $('#ctc_sel_ovrd_selnum').val(value);
        ctc_render_selector_inputs(value);
        $('#ctc_sel_ovrd_new_rule, #ctc_sel_ovrd_rule_header,#ctc_sel_ovrd_rule_inputs_container,#ctc_sel_ovrd_rule_inputs').show();
    },
    
    ctc_set_rule = function(value,label) {
        $('#ctc_rule_menu').val('');
        $('#ctc_rule_menu_selected').text(label);
        ctc_render_rule_value_inputs(value);
        $('#ctc_rule_value_inputs,#ctc_input_row_rule_header').show();
    },
    // initialize vars
        ctc_selectors       = [],
        ctc_queries         = ctc_load_queries(),
        ctc_rules           = ctc_load_rules(),
        unique_rule_value   = {},
        toggles             = {};
        
    // initialize tools    
    $('.color-picker').each(function() {
        ctc_setup_iris(this);
    });
    $('.ctc-option-panel-container').on('focus', '.color-picker', function(){
        $(this).iris('toggle');
        $('.iris-picker').css({'position':'absolute', 'z-index':10});
    });
    $('.ctc-option-panel-container').on('focus', 'input', function() {
        $('.color-picker').not(this).iris('hide');
    });
    $('.ctc-option-panel-container').on('change', '.ctc-child-value', function() {
        ctc_coalesce_inputs(this);
    });
    $('.ctc-option-panel-container').on('click', '.ctc-selector-handle', function(e) {
        e.preventDefault();
        var id = $(this).attr('id').replace('_close', '');
        $('#' + id + '_container').fadeToggle('fast');
        $('.ctc-selector-container').not('#' + id + '_container').fadeOut('fast');
    });
    $('.nav-tab').on('click', function(e){
        e.preventDefault();
        var id = '#' + $(this).attr('id'), panelid = id + '_panel';
        $('.nav-tab').removeClass('nav-tab-active');
        $('.ctc-option-panel').removeClass('ctc-option-panel-active');
        $('.ctc-selector-container').hide();
        $(id).addClass('nav-tab-active');
        $('.ctc-option-panel-container').scrollTop(0);
        $(panelid).addClass('ctc-option-panel-active');
    });
    $('#preview_options').on('click', function(e){
        var stamp = new Date().getTime(),
            css_uri = ctcAjax.theme_uri + '/' + ctcAjax.child_theme + '/style.css?' + stamp;
        $.get(
            css_uri,
            function(response){
                $('#preview_options_panel').text(response);
            }
        ).fail(function(){
            $('#preview_options_panel').text(ctcAjax.css_fail);
        });
    });
    $('#ctc_load_form').on('submit', function(e) {
        return confirm(ctcAjax.load_msg);
    });
    $(document).on('click', '.ctc-save-input', function(e) {
        ctc_save(this);
    });
    $('#ctc_sel_ovrd_query').autocomplete({
        source: ctc_queries,
        minLength: 0,
        selectFirst: true,
        autoFocus: true,
        select: function(e, ui) {
            ctc_set_query(ui.item.value, ui.item.label);
            return false;
        },
        focus: function(e) { e.preventDefault(); }
    });
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
    $('#ctc_new_rule_menu').autocomplete({
        source: ctc_rules,
        //minLength: 0,
        selectFirst: true,
        autoFocus: true,
        select: function(e, ui) {
            var sel = $('#ctc_sel_ovrd_selnum').val();
            $('#ctc_sel_ovrd_rule_inputs').append(ctc_render_child_rule_input(sel, ui.item.value, false)).find('.color-picker').each(function() {
                ctc_setup_iris(this);
            });
            $('#ctc_new_rule_menu').val('');
            return false;
        },
        focus: function(e) { e.preventDefault(); }
    });
})(jQuery);
    
