<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: ChildThemeConfiguratorCSS
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Handles all CSS output, parsing, normalization
    Version: 1.6.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2014 Lilaea Media
*/
class ChildThemeConfiguratorCSS {
    // data dictionaries
    var $dict_query;    // @media queries and 'base'
    var $dict_sel;      // selectors  
    var $dict_qs;       // query/selector lookup
    var $dict_rule;     // css rules
    var $dict_val;      // css values
    var $dict_seq;      // child load order (priority)
    // hierarchies
    var $sel_ndx;       // query => selector hierarchy
    var $val_ndx;       // selector => rule => value hierarchy
    // key counters
    var $qskey;         // counter for dict_qs
    var $querykey;      // counter for dict_query
    var $selkey;        // counter for dict_sel
    var $rulekey;       // counter for dict_rule
    var $valkey;        // counter for dict_val
    // miscellaneous properties
    var $imports;       // @import rules
    var $styles;        // temporary update cache
    var $child;         // child theme slug
    var $parnt;         // parent theme slug
    var $parntss;       // parent additional stylesheets
    var $enqueue;       // load parent css method (enqueue, import, none)
    var $configtype;    // theme or plugin extension
    var $child_name;    // child theme name
    var $child_author;  // stylesheet author
    var $child_version; // stylesheet version
    var $vendorrule       = array(
        'box-sizing',
        'font-smoothing',
        'border-radius',
        'box-shadow',
        'transition',
        'transform'
    );
    var $configvars = array(
        'parntss',
        // the enqueue flag prevents the transition from 1.5.4 
        // from breaking the stylesheet by forcing the user to regenerate
        // the config data before updating the stylesheet. Otherwise,
        // removing the @import for the parent stylesheet will cause
        // the parent core styles to be missing.
        'enqueue', 
        'imports',
        'child_version',
        'child_author',
        'child_name',
        'configtype',
        'parnt',
        'child',
        'valkey',
        'rulekey',
        'qskey',
        'selkey',
        'querykey',                
    );
    var $dicts = array(
        'dict_qs',
        'dict_sel',
        'dict_query',
        'dict_rule',
        'dict_val',
        'dict_seq',
        'sel_ndx',
        'val_ndx'
    );
    function __construct() {
        // scalars
        $this->querykey         = 0;
        $this->selkey           = 0;
        $this->qskey            = 0;
        $this->rulekey          = 0;
        $this->valkey           = 0;
        $this->child            = '';
        $this->parnt            = '';
        $this->configtype       = 'theme';
        $this->child_name       = '';
        $this->child_author     = 'Child Theme Configurator';
        $this->child_version    = '1.0';
        // multi-dim arrays
        $this->dict_qs          = array();
        $this->dict_sel         = array();
        $this->dict_query       = array();
        $this->dict_rule        = array();
        $this->dict_val         = array();
        $this->dict_seq         = array();
        $this->sel_ndx          = array();
        $this->val_ndx          = array();
        $this->parntss          = array();
        $this->imports          = array('child' => array(), 'parnt' => array());
    }
    
    function ctc() {
        global $chld_thm_cfg;
        return $chld_thm_cfg;
    }
    
    function read_config() {
        
        if ($configarray = get_option(CHLD_THM_CFG_OPTIONS . '_configvars')):
            foreach ($this->configvars as $configkey)
                if (isset($configarray[$configkey]))
                    $this->{$configkey} = $configarray[$configkey];
            foreach ($this->dicts as $configkey):
                if ($configarray = get_option(CHLD_THM_CFG_OPTIONS . '_' . $configkey))
                    $this->{$configkey} = $configarray;
            endforeach;
        else:
            return FALSE;
        endif;
    }
    
    function save_config() {
        
        $configarray = array();
        foreach ($this->configvars as $configkey)
            $configarray[$configkey] = $this->{$configkey};
        update_option(CHLD_THM_CFG_OPTIONS . '_configvars', $configarray);
        foreach ($this->dicts as $configkey)
            update_option(CHLD_THM_CFG_OPTIONS . '_' . $configkey, $this->{$configkey});
        // remove pre 1.5.4 options
        delete_option(CHLD_THM_CFG_OPTIONS);
    }
    /*
     * get_prop
     * Getter interface (data sliced different ways depending on objname)
     */
    function get_prop($objname, $params = null) {
        switch ($objname):
            case 'imports':
                return $this->obj_to_utf8(is_array($this->imports['child']) ? (current($this->imports['child']) == 1 ? array_keys($this->imports['child']) : array_keys(array_flip($this->imports['child']))) : array());
            case 'sel_ndx':
                return $this->obj_to_utf8($this->denorm_sel_ndx(empty($params['key'])?null:$params['key']));
            case 'rule_val':
                return empty($params['key']) ? array() : $this->denorm_rule_val($params['key']);
            case 'val_qry':
                if (isset($params['rule']) && isset($this->dict_rule[$params['rule']])):
                    return empty($params['key']) ? 
                        array() : $this->denorm_val_query($params['key'], $params['rule']);
                endif;
            case 'sel_val':
                return empty($params['key']) ? 
                    array() : $this->denorm_sel_val($params['key']);
            case 'rule':
                return $this->obj_to_utf8(array_flip($this->dict_rule));
            case 'child':
                return $this->child;
            case 'parnt':
                return $this->parnt;
            case 'parntss':
                return isset($this->parntss) ? $this->parntss : array();
            case 'configtype':
                return $this->configtype;
            case 'child_name':
                return $this->child_name;
            case 'author':
                return $this->child_author;
            case 'version':
                return $this->child_version;
            case 'preview':
                $this->styles = '';
                if (empty($params['key']) || 'child' == $params['key']):
                    $this->read_stylesheet('child');
                else:
                    if (isset($this->parntss)):
                        foreach ($this->parntss as $template):
                            $this->styles .= '/*** BEGIN ' . $template . ' ***/' . LF;
                            $this->read_stylesheet('parnt', $template);
                            $this->styles .= '/*** END ' . $template . ' ***/' . LF;
                        endforeach;
                    endif;
                    $this->styles .= '/*** BEGIN style.css ***/' . LF;
                    $this->read_stylesheet('parnt');
                    $this->styles .= '/*** END style.css ***/' . LF;
                endif;
                $this->normalize_css();
                return $this->styles;
        endswitch;
        return FALSE;
    }

    /*
     * set_prop
     * Setter interface (scalar values only)
     */
    function set_prop($prop, $value) {
        if (is_scalar($this->{$prop}))
            $this->{$prop} = $value;
        else return FALSE;
    }
    
    function normalize_css() {
        if (preg_match("/(\}[\w\#\.]|; *\})/", $this->styles)):                       // prettify compressed CSS
            $this->styles = preg_replace("/\*\/\s*/s", "*/\n", $this->styles);      // end comment
            $this->styles = preg_replace("/\{\s*/s", " {\n    ", $this->styles);    // open brace
            $this->styles = preg_replace("/;\s*/s", ";\n    ", $this->styles);      // semicolon
            $this->styles = preg_replace("/\s*\}\s*/s", "\n}\n", $this->styles);    // close brace
        endif;
    }
   
    function get_css_header() {
        $parnt = $this->get_prop('parnt');
        return '/*' . LF
            . 'Theme Name: ' . $this->get_prop('child_name') . LF
            . 'Template: ' . $parnt . LF
            . 'Author: ' . $this->get_prop('author'). LF
            . 'Version: ' . $this->get_prop('version') . LF
            . 'Updated: ' . current_time('mysql') . LF
            . '*/' . LF . LF
            . '@charset "UTF-8";' . LF . LF
            . ('import' == $this->enqueue ? '@import url(\'../' . $parnt . '/style.css\');' . LF : '');
    }
   
    function get_child_target($file = 'style.css') {
        return trailingslashit(get_theme_root()) . trailingslashit($this->get_prop('child')) . $file;
    }
    function get_parent_source($file = 'style.css') {
        return trailingslashit(get_theme_root()) . trailingslashit($this->get_prop('parnt')) . $file;
    }
   
    /*
     * update_arrays
     * accepts CSS properties as raw strings and normilizes into 
     * CTC object arrays, creating update cache in the process.
     * Update cache is returned to UI via AJAX to refresh page
     */
    function update_arrays($template, $query, $sel, $rule = null, $value = null, $important = 0, $seq = null) {
        
        // normalize selector styling
        $sel = implode(', ', preg_split('#\s*,\s*#s', trim($sel)));
        // add selector and query to index
        if (!isset($this->dict_query[$query])) $this->dict_query[$query] = ++$this->querykey;
        if (!isset($this->dict_sel[$sel])) $this->dict_sel[$sel] = ++$this->selkey;
        if (!isset($this->sel_ndx[$this->dict_query[$query]][$this->dict_sel[$sel]])):
            // increment key number
            $this->sel_ndx[$this->dict_query[$query]][$this->dict_sel[$sel]] = ++$this->qskey;
            
            $this->dict_qs[$this->qskey]['s'] = $this->dict_sel[$sel];
            $this->dict_qs[$this->qskey]['q'] = $this->dict_query[$query];
            // tell the UI to update a single cached query/selector lookup by passing 'qsid' as the key
            // (normally the entire array is replaced):
            if ($this->ctc()->cache_updates)
                $this->ctc()->updates[] = array(
                    'obj'   => 'sel_ndx',
                    'key'   => 'qsid',
                    'data'  => array(
                        'query'     => $query,
                        'selector'  => $sel,
                        'qsid'      => $this->qskey,
                    ),
                );
        endif;
        // update sequence for this selector if this is a later instance to keep cascade priority
        if (!isset($this->dict_seq[$this->qskey]))
            $this->dict_seq[$this->qskey] = $this->qskey;
        // set data and value
        if ($rule):
            if (!isset($this->dict_rule[$rule])):
                $this->dict_rule[$rule] = ++$this->rulekey;
                // tell the UI to update a single cached rule:
                if ($this->ctc()->cache_updates):
                    $this->ctc()->updates[] = array(
                        'obj'   => 'rule',
                        'key'   => $this->rulekey,
                        'data'  => $rule,
                    );
                endif;
            endif;
            $qsid = $this->sel_ndx[$this->dict_query[$query]][$this->dict_sel[$sel]];
            $ruleid = $this->dict_rule[$rule];
            if (!isset($this->dict_val[$value])):
                $this->dict_val[$value] = ++$this->valkey;
            endif;
            $this->val_ndx[$qsid][$ruleid][$template] = $this->dict_val[$value];
            // set the important flag for this value
            $this->val_ndx[$qsid][$ruleid]['i_' . $template] = $important;
            // tell the UI to add a single cached query/selector data array:
            if ($this->ctc()->cache_updates):
                $updatearr = array(
                    'obj'   => 'sel_val',
                    'key'   => $qsid,
                    'data'  => $this->denorm_sel_val($qsid),
                );
                $this->ctc()->updates[] = $updatearr;
            endif;
            if (isset($seq)): // this is a renamed selector
                $this->dict_seq[$qsid] = $seq;
                if ($this->ctc()->cache_updates):
                    $this->ctc()->updates[] = array(
                        'obj'   => 'rewrite',
                        'key'   => $qsid,
                        'data'  => $sel,
                    );
                endif;
            endif;
        endif;
    }

    function read_stylesheet($template = 'child', $file = 'style.css') {
        
        // these conditions support revert/restore option in 1.6.0+
        if ('all' == $file) return;
        elseif ('' == $file) $file = 'style.css';
        // end revert/restore conditions
        
        $source = $this->get_prop($template);
        $configtype = $this->get_prop('configtype');
        if (empty($source) || !is_scalar($source)) return FALSE;
        $themedir = get_theme_root() . '/' . $source;
        $stylesheet = apply_filters('chld_thm_cfg_' . $template, $themedir . '/' . $file , $this);
        
        // read stylesheet
        if ($stylesheet_verified = $this->is_file_ok($stylesheet, 'read')):
            $import_url = preg_replace('%^' . preg_quote($themedir) . '/%', '', $stylesheet_verified);
            $this->styles .= @file_get_contents($stylesheet_verified) . "\n";
        endif;
    }
    
    function recurse_directory( $rootdir, $ext = 'css', $all = FALSE ) {
        if ( !$this->is_file_ok( $rootdir, 'search' ) ) return array(); // make sure we are only recursing theme and plugin files
        $files = array();
        $dirs = array($rootdir);
        $loops = 0;
        if ( 'img' == $ext ):
            
            $ext = '(' . implode('|', array_keys($this->ctc()->imgmimes)) . ')';
        endif;
        while(count($dirs) && $loops < CHLD_THM_CFG_MAX_RECURSE_LOOPS ):
            $loops++;
            $dir = array_shift($dirs);
            if ($handle = opendir($dir)):
                while (FALSE !== ($file = readdir($handle))):
                    if (preg_match("/^\./", $file)) continue;
                    $filepath  = trailingslashit($dir) . $file;
                    if (is_dir($filepath)):
                        array_unshift($dirs, $filepath);
                        if ($all): 
                            $files[] = $filepath; 
                        endif;
                    elseif (is_file($filepath) && ($all || preg_match("/\.".$ext."$/i", $filepath))):
                        $files[] = $filepath;
                    endif;
                endwhile;
                closedir($handle);
            endif;
        endwhile;
        return $files;
    }
    
    function convert_parent_rel_url_to_abs_url($url) {
        $source = $this->get_prop('parnt');
        $spliton = '%[/\\\\]%';
        $dirname = dirname($url);
        $dirs = preg_split($spliton, $dirname);
        $dds  = '';
        $themeuri = get_theme_root_uri();
        while (count($dirs)):
            $thisdir = array_pop($dirs);
            $upone = implode('/', $dirs);           
            $dds .= '\.\.\/';
            $regex = '%url\([\'" ]*' . $dds . '(.+?)[\'" ]*\)%';
            $fullurl = $themeuri . '/' . $source . '/' . $upone . ('' == $upone ? '' : '/' );
            $this->styles = preg_replace($regex, "url(" . $fullurl . "$1)", $this->styles);
        endwhile;
    }
    /*
     * parse_post_data
     * Parse user form input into separate properties and pass to update_arrays
     */
    function parse_post_data() {
        $this->cache_updates = TRUE;
        if (isset($_POST['ctc_new_selectors'])):
            $this->styles = $this->parse_css_input(LF . $_POST['ctc_new_selectors']);
            $this->parse_css('child', 
                (isset($_POST['ctc_sel_ovrd_query'])?trim($_POST['ctc_sel_ovrd_query']):null), FALSE);
        elseif (isset($_POST['ctc_child_imports'])):
            $this->imports['child'] = array();
            $this->styles = $this->parse_css_input($_POST['ctc_child_imports']);
            $this->parse_css('child');
        else:
            $newselector = isset($_POST['ctc_rewrite_selector']) ? $this->sanitize($this->parse_css_input($_POST['ctc_rewrite_selector'])) : NULL;
            // set the custom sequence value
            foreach (preg_grep('#^ctc_ovrd_child_seq_#', array_keys($_POST)) as $post_key):
                if (preg_match('#^ctc_ovrd_child_seq_(\d+)$#', $post_key, $matches)):
                    $qsid = $matches[1];
                    $this->dict_seq[$qsid] = intval($_POST[$post_key]);
                endif;
            endforeach;
            $parts = array();
            foreach (preg_grep('#^ctc_(ovrd|\d+)_child#', array_keys($_POST)) as $post_key):
                if (preg_match('#^ctc_(ovrd|\d+)_child_([\w\-]+?)_(\d+?)(_(.+))?$#', $post_key, $matches)):
                    $valid = $matches[1];
                    $rule   = $matches[2];
                    if (null == $rule || !isset($this->dict_rule[$rule])) continue;
                    $ruleid = $this->dict_rule[$rule];
                    $qsid = $matches[3];
                    $value  = $this->normalize_color($this->sanitize($this->parse_css_input($_POST[$post_key])));
                    $important = $this->is_important($value);
                    if (!empty($_POST['ctc_' . $valid . '_child_' . $rule . '_i_' . $qsid])) $important = 1;
                    
                    $selarr = $this->denorm_query_sel($qsid);
                    if (!empty($matches[5])):
                        $parts[$qsid][$rule][$matches[5]] = $value;
                        $parts[$qsid][$rule]['important'] = $important;
                        $parts[$qsid][$rule]['query']     = $selarr['query'];
                        $parts[$qsid][$rule]['selector']  = $selarr['selector'];
                    else:
                        if ($newselector && $newselector != $selarr['selector']):
                            // If this is a renamed selector, add new selector to array 
                            // and clear original child selector values.
                            // Passing the sequence in the last argument serves two purposes:
                            // 1. sets sequence for new renamed selector.
                            // 2. tells the update_arrays function to flag this as a 
                            //    renamed selector to pass back in result array.
                            $this->update_arrays('child', $selarr['query'], $newselector, 
                                $rule, trim($value), $important, $this->dict_seq[$qsid]);
                            $this->update_arrays('child', $selarr['query'], $selarr['selector'], $rule, '');
                        else:
                            // Otherwise, just update with the new values:
                            $this->update_arrays('child', $selarr['query'], $selarr['selector'], 
                                $rule, trim($value), $important);
                        endif;
                    endif;
                endif;
            endforeach;
            foreach ($parts as $qsid => $rule_arr):
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
                    if ($newselector && $newselector != $rule_part['selector']):
                        $this->update_arrays('child', $rule_part['query'], $newselector, 
                            $rule, trim($value), $rule_part['important'], $this->dict_seq[$qsid]);  
                        $this->update_arrays('child', $rule_part['query'], $rule_part['selector'], $rule, '');
                    else:
                        $this->update_arrays('child', $rule_part['query'], $rule_part['selector'], 
                            $rule, trim($value), $rule_part['important']);
                    endif;
                endforeach;
            endforeach; 
        endif;
    }
    
    /*
     * parse_css_input
     * Normalize raw user CSS input so that the parser can read it.
     * TODO: this is a stub for future use
     */
    function parse_css_input($styles) {
        return $this->repl_octal(stripslashes($this->esc_octal($styles)));
    }
    
    function sanitize($styles) {
        return sanitize_text_field(preg_replace( '/[^[:print:]]/', '', $styles));
    }

    function esc_octal($styles){
        return preg_replace("#(['\"])\\\\([0-9a-f]{4})(['\"])#i", "$1##bs##$2$3", $styles);
    }
    function repl_octal($styles) {
        return str_replace("##bs##", "\\", $styles);
    }
    /*
     * parse_css_file
     * reads stylesheet to get WordPress meta data and passes rest to parse_css 
     */
    function parse_css_file($template, $file = 'style.css') {
        
        $this->ctc()->cache_updates = FALSE;
        $this->styles = ''; // reset styles
        $this->read_stylesheet($template, $file);
        // get theme name
        $regex = '#Theme Name:\s*(.+?)\n#i';
        preg_match($regex, $this->styles, $matches);
        $child_name = $this->get_prop('child_name');
        if (!empty($matches[1]) && 'child' == $template && empty($child_name)) $this->set_prop('child_name', $matches[1]);
        $this->parse_css($template);
    }

    /*
     * parse_css
     * accepts raw CSS as text and parses into separate properties 
     */
    function parse_css($template, $basequery = null, $parse_imports = TRUE) {
        if (FALSE === strpos($basequery, '@')):
            $basequery = 'base';
        endif;
        $ruleset = array();
        // ignore commented code
        $this->styles = preg_replace('#\/\*.*?\*\/#s', '', $this->styles);
        // space brace to ensure correct matching
        $this->styles = preg_replace('#(\{\s*)#', "$1\n", $this->styles);
        // get all imports
        if ($parse_imports):
            
            $regex = '#(\@import.+?);#';
            preg_match_all($regex, $this->styles, $matches);
            foreach (preg_grep('#' . $this->get_prop('parnt') . '\/style\.css#', $matches[1], PREG_GREP_INVERT) as $import)
                $this->imports[$template][$import] = 1;
            if ($this->ctc()->cache_updates):
                $this->ctc()->updates[] = array(
                    'obj'  => 'imports',
                    'data' => array_keys($this->imports[$template]),
                );
            endif;
        endif;
        // break into @ segments
        foreach (array(
            '#(\@media[^\{]+?)\{(\s*?)\}#', // get an placehoder (empty) media queries
            '#(\@media[^\{]+?)\{(.*?\})?\s*?\}#s', // get all other media queries
        ) as $regex): // (((?!\@media).) backreference too memory intensive - rolled back in v 1.4.8.1
            preg_match_all($regex, $this->styles, $matches);
            foreach ($matches[1] as $segment):
                $ruleset[trim($segment)] = array_shift($matches[2]) . (isset($ruleset[trim($segment)])?$ruleset[trim($segment)]:'');
            endforeach;
            // stripping rulesets leaves base styles
            $this->styles = preg_replace($regex, '', $this->styles);
        endforeach;
        $ruleset[$basequery] = $this->styles;
        foreach ($ruleset as $query => $segment):
            // make sure there is semicolon before closing brace
            $segment = preg_replace('#(\})#', ";$1", $segment);
            $regex = '#\s([\.\#\:\w][\w\-\s\(\)\[\]\'\*\.\#\+:,"=>]+?)\s*\{(.*?)\}#s';  //
            preg_match_all($regex, $segment, $matches);
            foreach($matches[1] as $sel):
                $stuff  = array_shift($matches[2]);
                $this->update_arrays($template, $query, $sel);
                foreach (explode(';', $stuff) as $ruleval):
                    if ($this->qskey > CHLD_THM_CFG_MAX_SELECTORS) break;
                    if (FALSE === strpos($ruleval, ':')) continue;
                    list($rule, $value) = explode(':', $ruleval, 2);
                    $rule   = trim($rule);
                    $rule   = preg_replace_callback("/[^\w\-]/", array($this, 'to_ascii'), $rule);
                    $value  = trim($value);
                    
                    $rules = $values = array();
                    // save important flag
                    $important = $this->is_important($value);
                    // normalize color
                    $value = $this->normalize_color($value);
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
                        $value = preg_replace('#\b0(px|r?em)#', '0', $value);
                        // normalize gradients
                        if (FALSE !== strpos($value, 'gradient')):
                            if (FALSE !== strpos($rule, 'filter')):
                                $rule = 'background-image';
                                continue; // treat as background-image, we'll add filter rule later
                            endif;
                            if (FALSE !== strpos($value, 'webkit-gradient')) continue; // bail on legacy webkit, we'll add it later
                            $value = $this->encode_gradient($value);
                        endif;
                        // normalize common vendor prefixes
                        $rule = preg_replace('#(\-(o|ms|moz|webkit)\-)?(' . implode('|', $this->vendorrule) . ')#', "$3", $rule);
                        $this->update_arrays($template, $query, $sel, $rule, $value, $important);
                    endforeach;
                endforeach;
            endforeach;
        endforeach;
    }
    
    /*
     * write_css
     * converts normalized CSS object data into stylesheet.
     * Preserves selector sequence and !important flags of parent stylesheet.
     * @media query blocks are sorted using internal heuristics (see sort_queries)
     * New selectors are appended to the end of each media query block.
     */
    function write_css($backup = FALSE) {
        // write new stylesheet
        $output = apply_filters('chld_thm_cfg_css_header', $this->get_css_header(), $this);
        $imports = $this->get_prop('imports');
        if (!empty($imports)):
            foreach ($imports as $import):
                $output .= $import . ';' . LF;
            endforeach;
        endif;
        $output .= LF;
        // turn the dictionaries into indexes (value => id into id => value):
        $rulearr = array_flip($this->dict_rule);
        $valarr  = array_flip($this->dict_val);
        $selarr  = array_flip($this->dict_sel);
        foreach ($this->sort_queries() as $query => $sort_order):
            $has_selector = 0;
            $sel_output   = '';
            $selectors = $this->sel_ndx[$this->dict_query[$query]];
            uasort($selectors, array($this, 'cmp_seq'));
            if ('base' != $query) $sel_output .=  $query . ' {' . LF;
            foreach ($selectors as $selid => $qsid):
                $has_value = 0;
                $sel = $selarr[$selid];
                if (!empty($this->val_ndx[$qsid])):
                    $shorthand = array();
                    foreach ($this->val_ndx[$qsid] as $ruleid => $valid):
                        if (isset($valid['child']) 
                            && isset($valarr[$valid['child']]) 
                            && '' !== $valarr[$valid['child']]
                            && (!isset($valid['parnt']) || $valid['parnt'] != $valid['child'])):
                            if (! $has_value): 
                                $sel_output .= isset($this->dict_seq[$qsid])?'/*' . $this->dict_seq[$qsid] . '*/' . LF:''; 
                                // show load order
                                $sel_output .= $sel . ' {' . LF; 
                                $has_value = 1;
                                $has_selector = 1;
                            endif;
                            $important_parnt = empty($valid['i_parnt']) ? 0 : 1;
                            $important = isset($valid['i_child']) ? $valid['i_child'] : $important_parnt;
                            $sel_output .= $this->add_vendor_rules($rulearr[$ruleid], $valarr[$valid['child']], $shorthand, $important);
                        endif;
                    endforeach;
                    $sel_output .= $this->encode_shorthand($shorthand); // . ($important ? ' !important' : '');
                    if ($has_value):
                        $sel_output .= '}' . LF;
                    endif;
                endif;
            endforeach;
            if ('base' != $query) $sel_output .= '}' . LF;
            if ($has_selector) $output .= $sel_output;
        endforeach;
        $stylesheet = apply_filters('chld_thm_cfg_target', $this->get_child_target(), $this);
        if ($stylesheet_verified = $this->is_file_ok($stylesheet, 'write')):
            global $wp_filesystem; // this was initialized earlier;
                // backup current stylesheet
                if ($backup && is_file($stylesheet_verified)):
                    $timestamp  = date('YmdHis', current_time('timestamp'));
                    $bakfile    = preg_replace("/\.css$/", '', $stylesheet_verified) . '-' . $timestamp . '.css';
                    // don't write new stylesheet if backup fails
                    if (!$wp_filesystem->copy($this->ctc()->fspath($stylesheet_verified), $this->ctc()->fspath($bakfile))) return FALSE;
                endif;
                // write new stylesheet:
                // try direct write first, then wp_filesystem write
                // stylesheet must already exist and be writable by web server
                if (FALSE !== @file_put_contents($stylesheet_verified, $output)): //is_writable($stylesheet_verified) && 
                    return TRUE;
                elseif (FALSE !== $wp_filesystem->put_contents($this->ctc()->fspath($stylesheet_verified), $output)): 
                    return TRUE;
                endif;
        endif;   
        return FALSE;
    }
    
    /*
     * add_vendor_rules
     * Applies vendor prefixes to rules/values
     * These are based on commonly used practices and not all vendor prefixed are supported
     * TODO: verify this logic against vendor and W3C documentation
     */
    function add_vendor_rules($rule, $value, &$shorthand, $important = 0) {
        $rules = '';
        if ('filter' == $rule && (FALSE !== strpos($value, 'progid:DXImageTransform.Microsoft.Gradient'))) return;
        $importantstr = $important ? ' !important' : '';
        if (preg_match("/^(margin|padding)\-(top|right|bottom|left)$/", $rule, $matches)):
            $shorthand[$matches[1]][$matches[2]] = $value . $importantstr;
            return '';
        elseif (preg_match('/^(' . implode('|', $this->vendorrule) . ')$/', $rule)):
            foreach(array('moz', 'webkit', 'o') as $prefix):
                $rules .= '    -' . $prefix . '-' . $rule . ': ' . $value . $importantstr . ';' . LF;
            endforeach;
            $rules .= '    ' . $rule . ': ' . $value . $importantstr . ';' . LF;
        elseif ('background-image' == $rule):
            // gradient?
            if ($gradient = $this->decode_gradient($value)):
                // standard gradient
                foreach(array('moz', 'webkit', 'o', 'ms') as $prefix):
                    $rules .= '    background-image: -' . $prefix . '-' . 'linear-gradient(' . $gradient['origin'] . ', ' 
                        . $gradient['color1'] . ', ' . $gradient['color2'] . ')' . $importantstr . ';' . LF;
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
                    . $gradient['color1'] . ', ' . $gradient['color2'] . ')' . $importantstr . ';' . LF;
                
                // legacy webkit gradient - we'll add if there is demand
                // '-webkit-gradient(linear,' .$origin . ', ' . $color1 . ', '. $color2 . ')';
                
                // MS filter gradient
                $type = (in_array($gradient['origin'], array('left', 'right', '0deg', '180deg')) ? 1 : 0);
                $color1 = preg_replace("/^#/", '#00', $gradient['color1']);
                $rules .= '    filter: progid:DXImageTransform.Microsoft.Gradient(GradientType=' . $type . ', StartColorStr="' 
                    . strtoupper($color1) . '", EndColorStr="' . strtoupper($gradient['color2']) . '")' . $importantstr . ';' . LF;
            else:
                // url or other value
                $rules .= '    ' . $rule . ': ' . $value . $importantstr . ';' . LF;
            endif;
        else:
            $rule = preg_replace_callback("/\d+/", array($this, 'from_ascii'), $rule);
            $rules .= '    ' . $rule . ': ' . $value . $importantstr . ';' . LF;
        endif;
        return $rules;
    }

    /*
     * normalize_background
     * parses background shorthand value and returns
     * normalized rule/value pairs for each property
     */
    function normalize_background($value, &$rules, &$values){
        if (FALSE !== strpos($value, 'gradient')):
            // only supporting linear syntax
            if (preg_match('#(linear\-|Microsoft\.)#', $value)):
                $values[] = $value;
                $rules[] = 'background-image';
            endif;
        else:            
            $regex = '#(url *\([^\)]+\))#';
            if (preg_match($regex, $value, $matches)) $url = $matches[1];
            $parts = preg_split($regex, $value);
            
            if (count($parts) == 1):
                // this is a named color or single hex color or none
                $part = str_replace(' ', '', $parts[0]);
                $rules[] = 'none' == $part ? 'background' : 'background-color'; 
                $values[]   = $part;
            else:
                $rules[]    = 'background-image';
                $values[]   = $url;
                if (!empty($parts[0]) && '' !== $parts[0]):
                    $rules[]    = 'background-color';
                    $values[]   = trim($parts[0]);
                endif;
                $position = array();
                foreach(preg_split('/ +/', trim($parts[1])) as $part):
                    if ('' === $part) continue; // empty($part) || 
                    if (FALSE === strpos($part, 'repeat')):
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

    /*
     * normalize_font
     * parses font shorthand value and returns
     * normalized rule/value pairs for each property
     */
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

    /*
     * normalize_margin_padding
     * parses margin or padding shorthand value and returns
     * normalized rule/value pairs for each property
     * TODO: reassemble into shorthand when writing CSS file
     */
    function normalize_margin_padding($rule, $value, &$rules, &$values) {
        $parts = preg_split("/ +/", trim($value));
        if (!isset($parts[1])) $parts[1] = $parts[0];
        if (!isset($parts[2])) $parts[2] = $parts[0];
        if (!isset($parts[3])) $parts[3] = $parts[1];
        $rules[0]   = $rule . '-top';
        $values[0]  = $parts[0];
        $rules[1]   = $rule . '-right';
        $values[1]  = $parts[1];
        $rules[2]   = $rule . '-bottom';
        $values[2]  = $parts[2];
        $rules[3]   = $rule . '-left';
        $values[3]  = $parts[3];
    }

    function encode_shorthand($shorthand) {
        $rules = '';
        $importantstr = ' !important';
        foreach (array_keys($shorthand) as $key):
            $important = array();
            $rule = array();
            $importantct = 0;
            // which sides do we have and are they important?
            foreach($shorthand[$key] as $side => $val):
                $ict = 0;
                $rule[$side] = trim(preg_replace('/'.$importantstr.'/', '', $val, 1, $ict));
                $important[$side] = $ict;
                $importantct += $ict;
            endforeach;
            // shorthand must have 4 explicit values and all must have same priority
            if (4 == count($rule) && (0 == $importantct || 4 == $importantct )):
                // let's try to condense the values into as few as possible, starting with the top value
                $parts = array();
                $parts[0] = $rule['top'];
                // if left is not the same as right, we must use all 4 values
                if ($rule['left'] !== $rule['right']):
                    $parts[3] = $rule['left'];
                    $parts[2] = $rule['bottom'];
                    $parts[1] = $rule['right'];
                endif;
                // if top is not the same as bottom, we must use at least 3 values
                if ($rule['bottom'] !== $rule['top']):
                    $parts[2] = $rule['bottom'];
                    $parts[1] = $rule['right'];
                endif;
                // if top is not the same as right, we must use at least 2 values
                if ($rule['right'] !== $rule['top']):
                    $parts[1] = $rule['right'];
                endif;
                // the order of the sides is critical: top right bottom left
                ksort($parts);
                $shorthandstr = implode(' ', $parts);
                // if important counter is > 0, it must be == 4, add flag
                $rules .= '    ' . $key . ': ' . $shorthandstr . ($importantct ? ' ' . $importantstr : '') . ';' . LF;
            else:
                // otherwise return separate rule for each side
                foreach ($rule as $side => $value):
                    $rules .= '    ' . $key . '-' . $side . ': ' . $value . ($important[$side] ? $importantstr : '') . ';' . LF;
                endforeach;
            endif;
        endforeach;
        return $rules;
    }
    
    /*
     * encode_gradient
     * Normalize linear gradients from a bazillion formats into standard CTC syntax:
     * Currently only supports two-color linear gradients with no inner stops.
     * TODO: legacy webkit? more gradients? 
     */
    function encode_gradient($value) {
        $regex = '#gradient[^\)]*?\((((top|bottom|left|right)?( (top|bottom|left|right))?|\d+deg),)?([^\)]*[\'"]?(\#\w{3,8}|rgba?\([\d, ]+?\)|hsla?\([\d%, ]+?\))( \d+%)?)([^\)]*[\'"]?(\#\w{3,8}|rgba?\([\d, ]+?\)|hsla?\([\d%, ]+?\))( \d+%)?)([^\)]*gradienttype=[\'"]?(\d)[\'"]?)?[^\)]*\)#i';
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
        if (isset($parts[7]) && isset($parts[10])):
            $param[1] = $parts[7];
            $param[3] = $parts[10];
            ksort($param);
            return implode(':', $param);
        else: return $value;
        endif;
    }

    /*
     * decode_border
     * De-normalize CTC border syntax into separate properties.
     */
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

    /*
     * decode_gradient
     * Decode CTC gradient syntax into separate properties.
     */
    function decode_gradient($value) {
        $parts = explode(':', $value, 5);
        if (5 == count($parts)):        
            return array(
                'origin' => empty($parts[0]) ? '' : $parts[0],
                'color1' => empty($parts[1]) ? '' : $parts[1],
                'stop1'  => empty($parts[2]) ? '' : $parts[2],
                'color2' => empty($parts[3]) ? '' : $parts[3],
                'stop2'  => empty($parts[4]) ? '' : $parts[4],
            );
        endif;
        return FALSE;
    }

    /*
     * denorm_rule_val
     * Return array of unique values corresponding to specific rule
     */    
    function denorm_rule_val($ruleid) {
        $rule_sel_arr = array();
        $val_arr = array_flip($this->dict_val);
        foreach ($this->val_ndx as $selid => $rules):
            if (!isset($rules[$ruleid])) continue;
            foreach ($rules[$ruleid] as $theme => $val):
                if (!isset($val_arr[$val]) || '' === $val_arr[$val]) continue;
                $rule_sel_arr[$val] = $val_arr[$val];
            endforeach;
        endforeach;
        return $rule_sel_arr;
    }

    /*
     * denorm_val_query
     * Return array of queries, selectors, rules, and values corresponding to
     * specific rule/value combo grouped by query, selector
     */    
    function denorm_val_query($valid, $rule) {
        $value_query_arr = array();
        foreach ($this->val_ndx as $qsid => $rules):
            foreach ($rules as $ruleid => $values):
                if ($ruleid != $this->dict_rule[$rule]) continue;
                foreach ($values as $name => $val):
                    if ('i' == $name || $val != $valid) continue;
                    $selarr = $this->denorm_query_sel($qsid);
                    $valarr = $this->denorm_sel_val($qsid);
                    $value_query_arr[$rule][$selarr['query']][$qsid] = $valarr;
                endforeach;
            endforeach;
        endforeach;
        return $value_query_arr;
    }

    /*
     * denorm_query_sel
     * Return id, query and selector values of a specific qsid (query-selector ID)
     */    
    function denorm_query_sel($qsid) {
        $queryarr               = array_flip($this->dict_query);
        $selarr                 = array_flip($this->dict_sel);
        $this->dict_seq[$qsid]  = isset($this->dict_seq[$qsid]) ? $this->dict_seq[$qsid] : $qsid;
        return array(
            'id'        => $qsid,
            'query'     => $queryarr[$this->dict_qs[$qsid]['q']],
            'selector'  => $selarr[$this->dict_qs[$qsid]['s']],
            'seq'       => $this->dict_seq[$qsid],
        );
    }

    /*
     * denorm_sel_val
     * Return array of rules, and values matching specific qsid (query-selector ID)
     * grouped by query, selector
     */    
    function denorm_sel_val($qsid) {
        $selarr = $this->denorm_query_sel($qsid);
        $valarr = array_flip($this->dict_val);
        $rulearr = array_flip($this->dict_rule);
        if (isset($this->val_ndx[$qsid]) && is_array($this->val_ndx[$qsid])):
            foreach ($this->val_ndx[$qsid] as $ruleid => $values):
                foreach ($values as $name => $val):
                    if ('i_parnt' == $name || 'i_child' == $name):
                        $selarr['value'][$rulearr[$ruleid]][$name] = (empty($val) ? 0 : 1);
                    elseif (!isset($valarr[$val]) || '' === $valarr[$val]):
                        continue;
                    else:
                        $selarr['value'][$rulearr[$ruleid]][$name] = $valarr[$val];
                    endif;
                endforeach;
                // add load order
            endforeach;
        endif;
        return $selarr;
    }

    /*
     * denorm_sel_ndx
     * Return denormalized array containing query and selector heirarchy
     */    
    function denorm_sel_ndx($query = null) {
        $sel_ndx_norm = array();
        $queryarr = array_flip($this->dict_query);
        $selarr = array_flip($this->dict_sel);
        foreach($this->sel_ndx as $queryid => $sel):
            foreach($sel as $selid => $qsid):
                $sel_ndx_norm[$queryarr[$queryid]][$selarr[$selid]] = $qsid;
            endforeach;
        endforeach;
        return empty($query) ? $sel_ndx_norm : $sel_ndx_norm[$query];
    }
    
    /*
     * is_important
     * Strip important flag from value ref and return boolean
     * Value is updated because it is a ref
     */
    function is_important(&$value) {
        $important = 0;
        $value = trim(str_ireplace('!important', '', $value, $important));
        return $important;
    }
    
    /*
     * sort_queries
     * De-normalize query data and return array sorted as follows:
     * base
     * @media max-width queries in descending order
     * other @media queries in no particular order
     * @media min-width queries in ascending order
     */
    function sort_queries() {
        $queries = array();
        $queryarr = array_flip($this->dict_query);
        foreach (array_keys($this->sel_ndx) as $queryid):
            $query = $queryarr[$queryid];
            if ('base' == $query):
                $queries['base'] = -999999;
                continue;
            endif;
            if (preg_match("/((min|max)(\-device)?\-width)\s*:\s*(\d+)/", $query, $matches)):
                $queries[$query] = 'min-width' == $matches[1] ? $matches[4] : -$matches[4];
            else:
                $queries[$query] = $queryid - 10000;
            endif;
        endforeach;
        asort($queries);
        return $queries;
    }
    
    // sort selectors based on dict_seq if exists, otherwise qsid
    function cmp_seq($a, $b) {
        $cmpa = isset($this->dict_seq[$a])?$this->dict_seq[$a]:$a;
        $cmpb = isset($this->dict_seq[$b])?$this->dict_seq[$b]:$b;
        if ($cmpa == $cmpb) return 0;
        return ($cmpa < $cmpb) ? -1 : 1;
    }

    /*
     * obj_to_utf8
     * sets object data to UTF8
     * flattens to array
     * and stringifies NULLs
     */
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
    
    function to_ascii($matches) {
        return ord($matches[0]);
    }
    
    function from_ascii($matches) {
        return chr($matches[0]);
    }
    
    /* is_file_ok
     * verify file exists and is in valid location
     */
    function is_file_ok($stylesheet, $permission = 'read') {
        // remove any ../ manipulations
        $stylesheet = preg_replace("%\.\./%", '/', $stylesheet);
        if ('read' == $permission && !is_file($stylesheet)) return FALSE;
        if ('search' == $permission && !is_dir($stylesheet)) return FALSE;
        // sanity check for php files
        //if (!preg_match('%' . preg_quote($ext) . '$%', $stylesheet)) return FALSE;
        // check if in themes dir;
        if (preg_match('%^' . preg_quote(get_theme_root()) . '%', $stylesheet)) return $stylesheet;
        // check if in plugins dir
        if (preg_match('%^' . preg_quote(WP_PLUGIN_DIR) . '%', $stylesheet)) return $stylesheet;
        return FALSE;
    }

    function normalize_color($value) {
        $value = preg_replace_callback( "/#([0-9A-F]{3}([0-9A-F]{3})?)/i", array($this, 'tolower'), $value );
        $value = preg_replace( "/#([0-9A-F])\\1([0-9A-F])\\2([0-9A-F])\\3/i", "#$1$2$3", $value );
        return $value;
    }

    function tolower($matches) {
        return '#' . strtolower($matches[1]);
    }
}
?>