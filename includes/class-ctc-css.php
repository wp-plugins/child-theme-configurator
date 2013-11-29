<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator_CSS
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Handles all CSS output, parsing, normalization
    Version: 1.0.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2013 Lilaea Media
*/
class Child_Theme_Configurator_CSS {

    var $sel_ndx;
    var $val_ndx;
    var $data;
    var $imports;
    var $keynum;
    var $updates;
    var $author;
    var $child_theme;
    var $parent_theme;
    var $child_theme_name;
    
    function __construct() {
        $this->keynum           = 0;
        $this->child_theme      = '';
        $this->parent_theme     = '';
        $this->child_theme_name = '';
        $this->author           = 'Child Theme Configurator by Lilaea Media';
        $this->sel_ndx          = array();
        $this->val_ndx          = array();
        $this->data             = array();
        $this->imports          = array();
        $this->updates          = array();
    }
    
    function set_property($prop, $value) {
        if (is_scalar($this->{$prop}))
            $this->{$prop} = $value;
        else return false;
    }
    
    function get_property($objname, $selnum = null) {
        switch ($objname):
            case 'updates':
                return $this->obj_to_utf8($this->updates[$this->child_theme]);
            case 'imports':
                return $this->obj_to_utf8($this->imports);
            case 'sel_ndx':
                return $this->obj_to_utf8($this->sel_ndx);
            case 'val_ndx':
                return $this->obj_to_utf8($this->val_ndx);
            case 'child_theme':
                return $this->child_theme;
            case 'parent_theme':
                return $this->parent_theme;
            case 'child_theme_name':
                return $this->child_theme_name;
            case 'author':
                return $this->author;
            case 'data':
                return ($selnum ? $this->obj_to_utf8($this->data[$selnum]) : $this->obj_to_utf8($this->data));
        endswitch;
        return false;
    }

    function obj_to_utf8($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            return array_map(array(&$this, __FUNCTION__), $data);
        }
        else {
            return is_null( $data ) ? '' : utf8_encode($data);
        }
    }

    function normalize_background($value, &$rules, &$values){
        if (false !== strpos($value, 'gradient')):
            // only supporting linear syntax
            if (preg_match('#(linear\-|Microsoft\.)#', $value)):
                $values[] = $value;
                $rules[] = 'background-image';
            endif;
        else:
            $regex = '#^(\#\w{3,6})? *(url\([^\)]+\))?(.+)?$#';
            preg_match($regex, $value, $parts);
            if (empty($parts[1]) && empty($parts[2])):
                // this is a named color or single hex color
                $parts[1] = $parts[3];
                unset($parts[3]);
            endif;
            if (!empty($parts[1])):
                if ('none' != $parts[1]):
                    $rules[] = 'background-color'; 
                else: 
                    $rules[] = 'background'; 
                endif;
                $values[]   = $parts[1];
            endif;
            if (!empty($parts[2])):
                $rules[]    = 'background-image';
                $values[]   = $parts[2];
            endif;
            if (!empty($parts[3])):
                $position = array();
                foreach(preg_split('/ +/', trim($parts[3])) as $part):
                    if (false === strpos($part, 'repeat')):
                        $position[] = $part;
                    else:
                        $rules[] = 'background-repeat';
                        $values[] = $part;
                    endif;
                endforeach;
                if (count($position)):
                    $rules[] = 'background-position';
                    $values[] = implode(' ', $position);
                endif;
            endif;
        endif;
    }
    
    function normalize_font($value, &$rules, &$values) {
        $regex = '#^((\d+|bold|normal) )?((italic|normal) )?(([\d\.]+(px|r?em|%))[\/ ])?(([\d\.]+(px|r?em|%)?) )?(.+)$#is';
        preg_match($regex, $value, $parts);
        if (!empty($parts[2])):
            $rules[]    = 'font-weight';
            $values[]   = $parts[2];
        endif;
        if (!empty($parts[4])):
            $rules[]    = 'font-style';
            $values[]   = $parts[4];
        endif;      
        if (!empty($parts[6])):
            $rules[]    = 'font-size';
            $values[]   = $parts[6];
        endif;
        if (!empty($parts[9])):
            $rules[]    = 'line-height';
            $values[]   = $parts[9];
        endif;
        if (!empty($parts[11])):
            $rules[]    = 'font-family';
            $values[]   = $parts[11];
        endif;
    }
    
    function normalize_margin_padding($rule, $value, &$rules, &$values) {
        $parts = preg_split("/ +/", trim($value));
        if (empty($parts[1])) $parts[1] = $parts[0];
        if (empty($parts[2])) $parts[2] = $parts[0];
        if (empty($parts[3])) $parts[3] = $parts[1];
        $rules[0]   = $rule . '-top';
        $values[0]  = $parts[0];
        $rules[1]   = $rule . '-right';
        $values[1]  = $parts[1];
        $rules[2]   = $rule . '-bottom';
        $values[2]  = $parts[2];
        $rules[3]   = $rule . '-left';
        $values[3]  = $parts[3];
    }
    
    
    function parse_css_file($obj) {
        if (empty($this->{$obj}) || !is_scalar($this->{$obj})) return false;
        $stylesheet = get_theme_root() . '/' . $this->{$obj} . '/style.css';
        // read parent stylesheet
        if (!is_file($stylesheet)) return false;
        $styles = file_get_contents($stylesheet);
        // get theme name
        $regex = '#Theme Name:\s*(.+?)\n#i';
        preg_match($regex, $styles, $matches);
        if (empty($matches[1])) return false;
        $this->set_property('child_theme_name', $matches[1]);
        $this->parse_css($this->{$obj}, $styles);
    }
    function reset_updates() {
        $this->updates = array();
    }
    function update_arrays($template, $query, $sel, $rule = null, $value = null, $important = null, $old_value = null) {
        // add selector and query to index
        if (!isset($this->sel_ndx[$query][$sel])):
            // increment key number
            $this->sel_ndx[$query][$sel] = ++$this->keynum;
            $this->data[$this->sel_ndx[$query][$sel]]['selector'] = $sel;
            $this->data[$this->sel_ndx[$query][$sel]]['query'] = $query;
            $this->updates[$template]['insert'][] = array(
                'selector'  => $sel,
                'query'     => $query,
                'selnum'    => $this->sel_ndx[$query][$sel],
            );
        endif;
        // det data and value
        if ($rule):
            $selnum = $this->sel_ndx[$query][$sel];
            if ('' == $value && isset($old_value) && '' != $old_value):
                $this->data[$selnum]['value'][$rule][$template] = '';
                unset($this->val_ndx[$template][$rule][$value][$query][$selnum]);
                if (isset($this->val_ndx[$template][$rule][$old_value][$query][$selnum])):
                    unset($this->val_ndx[$template][$rule][$old_value][$query][$selnum]);
                    $delete = array(
                        'rule'      => $rule,
                        'value'     => $old_value,
                        'query'     => $query,
                        'selnum'    => $selnum,
                    );
                endif;
                if (isset($this->val_ndx[$template][$rule][$old_value][$query]) 
                    && !count($this->val_ndx[$template][$rule][$old_value][$query])):
                    unset($this->val_ndx[$template][$rule][$old_value][$query]);
                    $delete = array(
                        'rule'      => $rule,
                        'value'     => $old_value,
                        'query'     => $query,
                    );
                endif;
                if (isset($this->val_ndx[$template][$rule][$old_value]) 
                    && !count($this->val_ndx[$template][$rule][$old_value])):
                    unset($this->val_ndx[$template][$rule][$old_value]);
                    $delete = array(
                        'rule'      => $rule,
                        'value'     => $old_value,
                    );
                endif;
                if (isset($this->val_ndx[$template][$rule]) 
                    && !count($this->val_ndx[$template][$rule])):
                    unset($this->val_ndx[$template][$rule]);
                    $delete = array(
                        'rule'      => $rule,
                    );
                endif;
                $this->updates[$template]['del'][] = $delete;
                $this->updates[$template]['update'][] = array(
                        'rule'      => $rule,
                        'value'     => '',
                        'query'     => $query,
                        'selnum'    => $selnum,
                        'important' => '',
                );
            else:
                // add values to data array
                $this->data[$selnum]['value'][$rule][$template] = $value . ($important?' !important':'');
                // add rule and values to index
                $this->val_ndx[$template][$rule][$value][$query][$selnum] = 0;
                $this->updates[$template]['update'][] = array(
                        'rule'      => $rule,
                        'value'     => $value,
                        'query'     => $query,
                        'selnum'    => $selnum,
                        'important' => $important,
                );
            endif;
        endif;
    }
    
    function parse_css($template, $styles, $basequery = null, $parse_imports = true) {
        if (false === strpos($basequery, '@')):
            $basequery = 'base';
        endif;
        $ruleset = array();
        // ignore commented code
        $styles = preg_replace('#\/\*.*?\*\/#s', '', $styles);
        // space brace to ensure correct matching
        $styles = preg_replace('#(\{\s*)#', "$1\n", $styles);
        // get all imports
        if ($parse_imports):
            $regex = '#(\@import.+?);#';
            preg_match_all($regex, $styles, $matches);
            $this->imports[$template] = preg_grep('#style\.css#', $matches[1], PREG_GREP_INVERT);
            $this->updates[$template]['imports'] = $this->imports[$template];
        endif;
        // break into @ segments
        $regex = '#(\@media.+?)\{(.*?\})\s*\}#s';
        preg_match_all($regex, $styles, $matches);
        foreach ($matches[1] as $segment):
            $ruleset[trim($segment)] = array_shift($matches[2]);
        endforeach;
        // remove rulesets from styles
        $ruleset[$basequery] = preg_replace($regex, '', $styles);
        //echo 'template: ' . $template . ' styles: ' . $styles . ' basequery: ' . $basequery . LF;
        foreach ($ruleset as $query => $segment):
            // make sure there is semicolon before closing brace
            $segment = preg_replace('#(\})#', ";$1", $segment);
            $regex = '#\s([\.\#\:\w][\w\-\s\[\]\'\*\.\#\+:,"=>]+?)\s*\{(.*?)\}#s'; 
            preg_match_all($regex, $segment, $matches);
            foreach($matches[1] as $sel):
                $stuff  = array_shift($matches[2]);
                // normalize selector styling
                $sel = implode(', ', preg_split('#\s*,\s*#s', trim($sel)));
                $this->update_arrays($template, $query, $sel);
                //echo 'query: ' . $query . ' sel: ' . $sel . ' selnum: ' . $selnum . LF;
                foreach (explode(';', $stuff) as $ruleval):
                    if (false === strpos($ruleval, ':')) continue;
                    list($rule, $value) = explode(':', $ruleval, 2);
                    $rule   = trim($rule);
                    $value  = trim($value);
                    
                    $rules = $values = array();
                    // save important flag
                    $important = $this->is_important($value);
                    // normalize font
                    if ('font' == $rule):
                        $this->normalize_font($value, $rules, $values);
                    // normalize background
                    elseif('background' == $rule):
                        $this->normalize_background($value, $rules, $values);
                    // normalize margin/padding
                    elseif ('margin' == $rule || 'padding' == $rule):
                        $this->normalize_margin_padding($rule, $value, $rules, $values);
                    else:
                        $rules[]    = $rule;
                        $values[]   = $value;
                    endif;
                    foreach ($rules as $rule):
                        $value = trim(array_shift($values));
                        // normalize zero values
                        $value = preg_replace('#([: ])0(px|r?em)#', "$1\0", $value);
                        // normalize gradients
                        if (false !== strpos($value, 'gradient')):
                            if (false !== strpos($rule, 'filter')):
                                $rule = 'background-image';
                                continue; // treat as background-image, we'll add filter rule later
                            endif;
                            if (false !== strpos($value, 'webkit-gradient')) continue; // bail on legacy webkit, we'll add it later
                            $value = $this->encode_gradient($value);
                        endif;
                        // normalize common vendor prefixes
                        $rule = preg_replace('#(\-(o|ms|moz|webkit)\-)?(border\-radius|box\-shadow|transition)#', "$3", $rule);
                        $this->update_arrays($template, $query, $sel, $rule, $value, $important);
                    endforeach;
                endforeach;
            endforeach;
        endforeach;
    }
        
    function write_css() {
        // write new stylesheet
        $output = '/*' . LF;
        $output .= 'Theme Name: ' . $this->child_theme_name . LF;
        $output .= 'Template: ' . $this->parent_theme . LF;
        $output .= 'Author: ' . $this->author . LF;
        $output .= 'Version: 1.0' . LF;
        $output .= '*/' . LF . LF;
        $output .= '@charset "UTF-8";' . LF;
        $output .= '@import url(\'../' . $this->parent_theme . '/style.css\');' . LF;
        if (!empty($this->imports[$this->child_theme])):
            foreach ($this->imports[$this->child_theme] as $import):
                $output .= $import . ';' . LF;
            endforeach;
        endif;
        $output .= LF;
        foreach ($this->sort_queries() as $query => $sort_order):
            $selector = $this->sel_ndx[$query];
            asort($selector);
            $has_selector = 0;
            $sel_output   = '';
            if ('base' != $query) $sel_output .=  $query . ' {' . LF;
            foreach ($selector as $sel => $selnum):
                $has_value = 0;
                if (!empty($this->data[$selnum]['value'])):
                    foreach ($this->data[$selnum]['value'] as $rule => $value):
                        if (isset($value[$this->child_theme]) && '' != $value[$this->child_theme]):
                            if (! $has_value): 
                                $sel_output .= $sel . ' {' . LF; 
                                $has_value = 1;
                                $has_selector = 1;
                            endif;
                            $sel_output .= $this->add_vendor_rules($rule, $value[$this->child_theme]);
                        endif;
                    endforeach;
                    if ($has_value):
                        $sel_output .= '}' . LF;
                    endif;
                endif;
            endforeach;
            if ('base' != $query) $sel_output .= '}' . LF;
            if ($has_selector) $output .= $sel_output;
        endforeach;
        $themedir = get_theme_root() . '/' . $this->child_theme;

        $stylesheet = $themedir . '/style.css';
        if (!is_dir($themedir)):
            mkdir($themedir, 0755);
        endif;
        // backup current stylesheet if no backup exists
        if (is_file($stylesheet) && !is_file($stylesheet . '.bak')):
            file_put_contents($stylesheet . '.bak', file_get_contents($stylesheet));
        endif;
        // write new stylesheet
        file_put_contents($stylesheet, $output);        
    }
    
    function add_vendor_rules($rule, $value) {
        $rules = '';
        if (preg_match("/^(border\-radius|box\-shadow|transition)$/", $rule)):
            foreach(array('moz', 'webkit', 'o') as $prefix):
                $rules .= '    -' . $prefix . '-' . $rule . ': ' . $value . ';' . LF;
            endforeach;
            $rules .= '    ' . $rule . ': ' . $value . ';' . LF;
        elseif ('background-image' == $rule):
            // gradient?
            if ($gradient = $this->decode_gradient($value)):
                // standard gradient
                foreach(array('moz', 'webkit', 'o', 'ms') as $prefix):
                    $rules .= '    background-image: -' . $prefix . '-' . 'linear-gradient(' . $gradient['origin'] . ', ' 
                        . $gradient['color1'] . ', ' . $gradient['color2'] . ');' . LF;
                endforeach;
                // W3C standard gradient
                // rotate origin 90 degrees
                if (preg_match('/(\d+)deg/', $gradient['origin'], $matches)):
                    $org = (90 - $matches[1]) . 'deg';
                else: 
                    foreach (preg_split("/\s+/", $gradient['origin']) as $dir):
                        $dir = strtolower($dir);
                        $dirs[] = ('top' == $dir ? 'bottom' : ('bottom' == $dir ? 'top' : ('left' == $dir ? 'right' : ('right' == $dir ? 'left' : $dir))));
                    endforeach;
                    $org = 'to ' . implode(' ', $dirs);
                endif;
                $rules .= '    background-image: linear-gradient(' . $org . ', ' 
                    . $gradient['color1'] . ', ' . $gradient['color2'] . ');' . LF;
                
                // legacy webkit gradient - we'll add if there is demand
                // '-webkit-gradient(linear,' .$origin . ', ' . $color1 . ', '. $color2 . ')';
                
                // MS filter gradient
                $type = (in_array($gradient['origin'], array('left', 'right', '0deg', '180deg')) ? 1 : 0);
                $color1 = preg_replace("/^#/", '#00', $gradient['color1']);
                $rules .= '    filter: progid:DXImageTransform.Microsoft.Gradient(GradientType=' . $type . ', StartColorStr="' 
                    . strtoupper($color1) . '", EndColorStr="' . strtoupper($gradient['color2']) . '");' . LF;
            else:
                // url or other value
                $rules .= '    ' . $rule . ': ' . $value . ';' . LF;
            endif;
        else:
            $rules .= '    ' . $rule . ': ' . $value . ';' . LF;
        endif;
        return $rules;
    }
    function encode_gradient($value) {
        $regex = '#gradient[^\)]*?\((((top|bottom|left|right)?( (top|bottom|left|right))?|\d+deg),)?([^\)]*[\'"]?(\#\w{3,8}|rgba?\([\d, ]+?\))( \d+%)?)([^\)]*[\'"]?(\#\w{3,8}|rgba?\([\d, ]+?\))( \d+%)?)([^\)]*gradienttype=[\'"]?(\d)[\'"]?)?[^\)]*\)#i';
        $param = $parts = array();
        preg_match($regex, $value, $parts);
        if (empty($parts[13])):
            if (empty($parts[2])):
                $param[0] = 'top';
            else: 
                $param[0] = trim($parts[2]);
            endif;
            if (empty($parts[8])):
                $param[2] = '0%';
            else:
                $param[2] = trim($parts[8]);
            endif;
            if (empty($parts[11])):
                $param[4] = '100%';
            else:
                $param[4] = trim($parts[11]);
            endif;
        elseif('0' == $parts[13]):
            $param[0] = 'top';
            $param[2] = '0%';
            $param[4] = '100%';
        elseif ('1' == $parts[13]): 
            $param[0] = 'left';
            $param[2] = '0%';
            $param[4] = '100%';
        endif;
        $param[1] = $parts[7];
        $param[3]   = $parts[10];
        ksort($param);
        return implode(':', $param);
    }

    function decode_border($value) {
        if (preg_match('#^(0|none)#i', $value)):
            $parts[0] = $value;
            $parts[1] = $parts[2] = '';
        else:
            $parts = preg_split('#\s+#', $value, 3);
        endif;
        return array(
            'width' => empty($parts[0])?'':$parts[0],
            'style' => empty($parts[1])?'':$parts[1],
            'color' => empty($parts[2])?'':$parts[2],
        );
    }
    
    function decode_gradient($value) {
        $parts = explode(':', $value, 5);
        if (count($parts) == 5):
            return array(
                'origin' => empty($parts[0])?'':$parts[0],
                'color1' => empty($parts[1])?'':$parts[1],
                'stop1'  => empty($parts[2])?'':$parts[2],
                'color2' => empty($parts[3])?'':$parts[3],
                'stop2'  => empty($parts[4])?'':$parts[4],
            );
        else:
            return false;
        endif;
    }
    
    function parse_post_data() {
        if (isset($_POST['ctc_new_selectors'])):
            $this->parse_css($this->child_theme, LF . $_POST['ctc_new_selectors'], (isset($_POST['ctc_sel_ovrd_query'])?trim($_POST['ctc_sel_ovrd_query']):null), false);
        elseif (isset($_POST['ctc_child_imports'])):
            $this->parse_css($this->child_theme, $_POST['ctc_child_imports']);
        else:
            $parts = array();
            foreach (preg_grep('#^ctc_(ovrd_)?child#', array_keys($_POST)) as $post_key):
                if (preg_match('#^ctc_(ovrd_)?child_([\w\-]+?)_(\d+?)(_(.+))?$#', $post_key, $matches)):
                    $rule   = $matches[2];
                    $selnum = $matches[3];
                    $value  = sanitize_text_field($_POST[$post_key]);
                    if  (isset($this->data[$selnum]['value'][$rule]) && isset($this->data[$selnum]['value'][$rule][$this->child_theme])):
                        $child_value = $this->data[$selnum]['value'][$rule][$this->child_theme];
                    else: 
                        $child_value = $this->data[$selnum]['value'][$rule][$this->child_theme] = '';
                    endif;
                    if (isset($this->data[$selnum]['value'][$rule][$this->parent_theme])): 
                        $parent_value = $this->data[$selnum]['value'][$rule][$this->parent_theme];
                    else: 
                        $parent_value = $this->data[$selnum]['value'][$rule][$this->parent_theme] = '';
                    endif;
                    $important = $this->is_important($parent_value) ? ' !important' : '';
                    $query  = $this->data[$selnum]['query'];
                    if (!empty($matches[5])):
                        $parts[$selnum][$rule][$matches[5]] = $value;
                        $parts[$selnum][$rule]['important'] = $important;
                        $parts[$selnum][$rule]['query']     = $query;
                    else:
                        $this->update_arrays($this->child_theme, $query, $this->data[$selnum]['selector'], 
                            $rule, $value, $important, $child_value);
                    endif;
                endif;
            endforeach;
            foreach ($parts as $selnum => $rule_arr):
                foreach ($rule_arr as $rule => $rule_part):
                    if ('background' == $rule):
                        $value = $rule_part['background_url'];
                    elseif ('background-image' == $rule):
                        if (empty($rule_part['background_url'])):
                            if (empty($rule_part['background_color2'])):
                                $value = '';
                            else:
                                $value = implode(':', array(
                                    $rule_part['background_origin'], 
                                    $rule_part['background_color1'], '0%', 
                                    $rule_part['background_color2'], '100%'
                                ));
                            endif;
                        else:
                            $value = $rule_part['background_url'];
                        endif;
                    elseif (preg_match('#^border(\-(top|right|bottom|left))?$#', $rule)):
                        $value = implode(' ', array(
                            $rule_part['border_width'], 
                            $rule_part['border_style'], 
                            $rule_part['border_color']
                        ));
                    else:
                        $value = '';
                    endif;
                    $this->update_arrays($this->child_theme, $rule_part['query'], $this->data[$selnum]['selector'], 
                        $rule, $value, $rule_part['important'], $child_value);
                endforeach;
            endforeach; 
        endif;
    }
    /*
     * Strip important flag from value ref and return boolean
     */
    function is_important(&$value) {
        $important = 0;
        $value = str_replace('!important', '', $value, $important);
        return $important;
    }
    
    function sort_queries() {
        $queries = array();
        foreach (array_keys($this->sel_ndx) as $query):
            if ('base' == $query):
                $queries['base'] = -999999;
                continue;
            endif;
            if (preg_match("/((min|max)(\-device)?\-width)\s*:\s*(\d+)/", $query, $matches)):
                $queries[$query] = 'min-width' == $matches[1] ? $matches[4] : -$matches[4];
            else:
                $queries[$query] = 0;
            endif;
        endforeach;
        asort($queries);
        return $queries;
    }
}
?>