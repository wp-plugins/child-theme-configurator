<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Class: Child_Theme_Configurator_UI
    Plugin URI: http://www.lilaeamedia.com/plugins/child-theme-configurator/
    Description: Handles the plugin User Interface
    Version: 1.0
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: chld_thm_cfg
    Domain Path: /lang
    License: GPLv2
    Copyright (C) 2013 Lilaea Media
*/
class Child_Theme_Configurator_UI {
    var $swatch_text;
    function __construct() {
        $this->swatch_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    }
    
    function render_options() { 
        global $chld_thm_cfg; 
        $css    = $chld_thm_cfg->css;
        $parent = $css->get_property('parent_theme');
        $child  = $css->get_property('child_theme');
        $imports= $css->get_property('imports');
        $id     = 0;
    ?>

<div class="wrap">
  <div id="icon-tools" class="icon32"></div>
  <h2><?php echo $chld_thm_cfg->pluginName; ?></h2>
  <?php $this->settings_errors(); ?>
  <?php  
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'parent_child_options';
            ?>
  <h2 class="nav-tab-wrapper">
    <a id="parent_child_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=parent_child_options" 
                    class="nav-tab<?php echo 'parent_child_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('Parent/Child', $chld_thm_cfg->ns); ?>
    </a>
    <a id="query_selector_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=query_selector_options" 
                    class="nav-tab<?php echo 'query_selector_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('Query/Selector', $chld_thm_cfg->ns); ?>
    </a>
    <a id="rule_value_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=rule_value_options" 
                    class="nav-tab<?php echo 'rule_value_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('Rule/Value', $chld_thm_cfg->ns); ?>
    </a>
    <a id="import_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=import_options" 
                    class="nav-tab<?php echo 'import_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('@import', $chld_thm_cfg->ns); ?>
    </a>
    <a id="preview_options" href="?page=<?php echo $chld_thm_cfg->menuName; ?>&amp;tab=preview_options" 
                    class="nav-tab<?php echo 'preview_options' == $active_tab ? ' nav-tab-active' : ''; ?>">
    <?php _e('Preview CSS', $chld_thm_cfg->ns); ?>
    </a> 
  </h2>
  <div class="ctc-option-panel-container">
    <div id="parent_child_options_panel" class="ctc-option-panel<?php echo 'parent_child_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
      <form id="ctc_load_form" method="post" action="">
        <?php wp_nonce_field( 'ctc_update' ); ?>
        <div class="ctc-input-row clearfix" id="input_row_parent_theme">
          <div class="ctc-input-cell">
            <label>
              <?php _e('Parent Theme', $chld_thm_cfg->ns); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <?php $this->render_theme_dropdown(); ?>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_theme">
          <div class="ctc-input-cell">
            <label>
              <?php _e('Child Theme', $chld_thm_cfg->ns); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <?php $this->render_theme_dropdown(true); ?>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell">
            <label>
              <?php _e('Author', $chld_thm_cfg->ns); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_theme_author" name="ctc_theme_author" type="text" value="<? echo $css->get_property('author'); ?>"/>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_name">
          <div class="ctc-input-cell">
            <label>
              <?php _e('New Child Theme Name', $chld_thm_cfg->ns); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_child_name" name="ctc_child_name"  type="text"/>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell">
            <label>
              <?php _e('New Child Theme Slug', $chld_thm_cfg->ns); ?>
            </label>
          </div>
          <div class="ctc-input-cell">
            <input class="ctc_text" id="ctc_child_template" name="ctc_child_template" type="text"/>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_child_template">
          <div class="ctc-input-cell">
            <label>&nbsp;</label>
          </div>
          <div class="ctc-input-cell">
        <input class="ctc_submit button button-primary" id="ctc_load_styles" name="ctc_load_styles"  type="submit" 
                value="<?php _e('Load Styles', $chld_thm_cfg->ns); ?>" />
          </div>
        </div>
      </form>
    </div>
    <div id="rule_value_options_panel" class="ctc-option-panel<?php echo 'rule_value_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
      <form id="ctc_rule_value_form" method="post" action="">
        <?php wp_nonce_field( 'ctc_update' ); ?>
        <div class="ctc-input-row clearfix" id="ctc_input_row_rule_menu">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Rule', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell" id="ctc_rule_menu_selected">&nbsp;</div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_rule_menu"/>
            </div>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="ctc_input_row_rule_header" style="display:none">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Value', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Sample', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell"> <strong>
            <?php _e('Selectors', $chld_thm_cfg->ns); ?>
            </strong> </div>
        </div>
        <div class="ctc-rule-value-input-container clearfix" id="ctc_rule_value_inputs" style="display:none"> </div>
      </form>
    </div>
    <div id="query_selector_options_panel" class="ctc-option-panel<?php echo 'query_selector_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
      <form id="ctc_query_selector_form" method="post" action="">
        <div class="ctc-input-row clearfix" id="input_row_query">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Query', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell" id="ctc_sel_ovrd_query_selected">&nbsp;</div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_sel_ovrd_query"/>
            </div>
          </div>
        </div>
        <div class="ctc-input-row clearfix" id="input_row_selector">
          <div class="ctc-input-cell"> <strong>
            <?php _e('Selector', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell" id="ctc_sel_ovrd_selector_selected">&nbsp;</div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_sel_ovrd_selector"/>
            </div>
          </div>
        </div>
        <div class="ctc-selector-row clearfix" id="ctc_sel_ovrd_rule_inputs_container" style="display:none">
          <div class="ctc-input-row clearfix">
            <div class="ctc-input-cell"><strong>
              <?php _e('Sample', $chld_thm_cfg->ns); ?>
              </strong></div>
            <div class="ctc-input-cell clearfix" style="max-height:150px;overflow:hidden">
              <div class="ctc-swatch" id="ctc_child_all_0_swatch"><?php echo $this->swatch_text; ?></div>
            </div>
            <div class="ctc-input-cell ctc-button-cell" id="ctc_save_query_selector_cell">
              <input type="button" class="button ctc-save-input" id="ctc_save_query_selector" 
            name="ctc_save_query_selector" value="Save" />
              <input type="hidden" id="ctc_sel_ovrd_selnum" 
            name="ctc_sel_ovrd_selnum" value="" />
            </div>
          </div>
          <div class="ctc-input-row clearfix" id="ctc_sel_ovrd_rule_header" style="display:none">
            <div class="ctc-input-cell"> <strong>
              <?php _e('Rule', $chld_thm_cfg->ns); ?>
              </strong> </div>
            <div class="ctc-input-cell"> <strong>
              <?php _e('Parent Value', $chld_thm_cfg->ns); ?>
              </strong> </div>
            <div class="ctc-input-cell"> <strong>
              <?php _e('Child Value', $chld_thm_cfg->ns); ?>
              </strong> </div>
          </div>
          <div id="ctc_sel_ovrd_rule_inputs" style="display:none"> </div>
          <div class="ctc-input-row clearfix" id="ctc_sel_ovrd_new_rule" style="display:none">
          <div class="ctc-input-cell"> <strong>
            <?php _e('New Rule', $chld_thm_cfg->ns); ?>
            </strong> </div>
          <div class="ctc-input-cell">
            <div class="ui-widget">
              <input id="ctc_new_rule_menu"/>
            </div>
          </div>
        </div>
        </div>
          <div class="ctc-selector-row clearfix" id="ctc_new_selector_row">
            <div class="ctc-input-cell"> <strong>
              <?php _e('New Selector(s)', $chld_thm_cfg->ns); ?>
              </strong> 
              <div class="ctc-textarea-button-cell" id="ctc_save_query_selector_cell">
                <input type="button" class="button ctc-save-input" id="ctc_save_new_selectors" 
            name="ctc_save_new_selectors" value="Save" />
              </div>
              </div>
            <div class="ctc-input-cell-wide"> <textarea id="ctc_new_selectors" name="ctc_new_selectors" wrap="off"></textarea> </div>
          </div>
      </form>
    </div>
    <div id="import_options_panel" class="ctc-option-panel<?php echo 'import_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
      <form id="ctc_import_form" method="post" action="">
        <?php wp_nonce_field( 'ctc_update' ); ?>
          <div class="ctc-input-row clearfix" id="ctc_child_imports_row">
            <div class="ctc-input-cell"> <strong>
              <?php _e('@import Statements', $chld_thm_cfg->ns); ?>
              </strong> 
              <div class="ctc-textarea-button-cell" id="ctc_save_imports_cell">
                <input type="button" class="button ctc-save-input" id="ctc_save_imports" 
            name="ctc_save_imports" value="Save" />
              </div>              
            </div>
            <div class="ctc-input-cell-wide"> <textarea id="ctc_child_imports" name="ctc_child_imports" wrap="off">
<?php if (!empty($imports[$child])):
        foreach ($imports[$child] as $import):
            echo esc_textarea($import . ';' . LF);
        endforeach; endif;?></textarea> </div>
            
          </div>
      </form>
    </div>
    <div id="preview_options_panel" class="ctc-option-panel<?php echo 'preview_options' == $active_tab ? ' ctc-option-panel-active' : ''; ?>">
    </div>
  </div>
</div>
<style type="text/css">
.ctc-status-icon.success {
    display: block;
    float:right;
    position: relative;
    height: 16px;
    width: 16px;
    margin:4px;
    background:url(<?php echo admin_url( 'images/yes.png' ); ?>) no-repeat;
}
.ctc-status-icon.failure {
    display: block;
    float:right;
    position: relative;
    height: 16px;
    width: 16px;
    margin:4px;
    background:url(<?php echo admin_url( 'images/no.png') ; ?>) no-repeat;
}
</style>
<?php  
    } 
    
    function render_theme_dropdown($child = false) {
        global $chld_thm_cfg;
        echo '<select class="chld-thm-cfg-select" id="ctc_theme_' . ($child?'child':'parent') . '" name="ctc_theme_' . ($child?'child':'parent') . '">' . LF;
        if ($child):
                echo '<option value="new">New Theme</option>' . LF;
        endif;
        foreach (wp_get_themes() as $theme):
            $parent = $theme->parent();
            if ($child !== empty($parent)):
                $template = $child ? $theme->get_stylesheet() : $theme->get_template();
                echo '<option value="' . $template . '"' 
                    . ($template == $chld_thm_cfg->css->get_property($child?'child_theme':'parent_theme') ? ' selected' : '') 
                    . '>' . $theme->get('Name') . '</option>' . LF;
            endif;
        endforeach;
        echo '</select>' . LF;
    }
    
    function settings_errors() {
        global $chld_thm_cfg;
        if (count($chld_thm_cfg->errors)):
            echo '<div class="error"><ul>' . LF;
            foreach ($chld_thm_cfg->errors as $err):
                echo '<li>' . $err . '</li>' . LF;
            endforeach;
            echo '</ul></div>' . LF;
        elseif (isset($_GET['updated'])):
            echo '<div class="updated"><p>' . LF
                . __('Child Theme', $chld_thm_cfg->ns) . ' <strong>' . $chld_thm_cfg->css->get_property('child_theme_name') 
                . '</strong> ' . __('has been updated.', $chld_thm_cfg->ns) . LF
                . '</p></div>' . LF;
        endif;
    }
    
    function render_help_tabs() {
	    global $wp_version, $chld_thm_cfg;
	    if ( version_compare( $wp_version, '3.3') >= 0 ) {
	
		    $screen = get_current_screen();

            if ( $screen->id != $chld_thm_cfg->hook )
			    return;
    		// Add help tabs
	    	$screen->add_help_tab( array(
		    	'id'	=> 'ctc_getting_started',
			    'title'	=> __( 'Start Here', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>The first step is to create a child theme and import your parent theme styles into the configurator.</p>
<ol><li>Select an existing parent theme from the menu.</li>
<li>Select an existing child theme from the menu, or "New Theme" if you are creating one from scratch.</li>
<li>Enter an author for the child theme.</li>
<li>If this is a new theme, enter a Name.</li>
<li>If this is a new theme, enter a "slug" (lower case, no spaces). This is used to name the theme directory and identify the theme to WordPress.</li>
<li>Click "Load Styles." If you are loading an existing child theme, The Child Theme Configurator will create a backup of your existing stylesheet in the theme directory.</li></ol>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_query_selector',
			    'title'	=> __( 'Query/Selector', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Query/Selector tab lets you find specific selectors and edit them. First, find the query that contains the selector you wish to edit by typing in the <strong>Query</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. Selectors are in the <strong>base</strong> query by default.</p>
<p>Next, find the selector by typing in the <strong>Selector</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the rules for that selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the <strong>border</strong> and <strong>background-image</strong> get special treatment.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_rule_value',
			    'title'	=> __( 'Rule/Value', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Rule/Value tab lets you find specific values for a given rule and then edit that value for individual selectors that use that rule/value combination. First, find the rule you wish to override by typing in the <strong>Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the unique values that exist for that rule in the parent stylesheet with a Sample preview for that value. If there are values that exist in the child stylesheet that do not exist in the parent stylesheet, they will be displayed as well.</p>
<p>For each unique value, click the "Selectors" link to view a list of selectors that use that rule/value combination, grouped by query with a Sample preview of the value and inputs for the child value. Any existing child values will be automatically populated.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_new_styles',
			    'title'	=> __( 'Add New Styles', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>If you wish to add additional rules to a given selector, first load the selector using the Query/Selector tab. Then find the rule you wish to override by typing in the <strong>New Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. This will add a new input row to the selector inputs.</p>
<p>If you wish to add completely new selectors, or even new @media queries, you can enter free-form CSS in the "New Selector" textarea. Be aware that your syntax must be correct (i.e., balanced curly braces, etc.) for the parser to load the new styles. You will know it is invalid because a red "X" will appear next to the save button.</p>
<p>If you prefer to use shorthand syntax for rules and values instead of the inputs provided by the Child Theme Configurator, you can enter them here as well. The parser will convert your input into normalized CSS code automatically.</p>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_imports',
			    'title'	=> __( '@imports', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>You can add additional stylesheets and web fonts by typing @import rules into the textarea on the @import tab. <strong>Important: The Child Theme Configurator adds the @import rule that loads the Parent Theme\'s stylesheet automatically. Do not need to add it here.</strong></p>
<p>Below is an example that loads a local custom stylesheet (you would have to add the "fonts" directory and stylesheet) as well as the web font "Open Sans" from Google Web Fonts:</p>
<blockquote><pre><code>
@import url(fonts/stylesheet.css);
@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic);
</code></pre></blockquote>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_preview',
			    'title'	=> __( 'Preview and Activate', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<p>Click the Preview CSS tab to see your new masterpiece as CSS code. To preview the stylesheet as a WordPress theme follow these steps:</p>
<ol><li>Navigate to Appearance > Themes in the WordPress Admin. You will now see the new Child Theme as one of the installed Themes.</li>
<li>Click "Live Preview" below the new Child Theme to see it in action.</li>
<li>When you are ready to take the Child Theme live, click "Activate."</li></ol>
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_faq',
			    'title'	=> __( 'FAQs', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<h5 id="specific_color">How do I change a specific color/font style/background?</h5>
<p>You can override a specific value globally using the Rule/Value tab. See Rule/Value, above.</p>
<h5 id="add_styles">How do I add styles that aren\'t in the Parent Theme?</h5>
<p>You can add queries and selectors using the "New Selector(s)" textarea on the Query/Selector tab. See Query/Selector, above.</p>
<h5 id="add_styles">How do I remove a style from the Parent Theme?</h5>
<p>You shouldn\'t really "remove" a style from the Parent. You can, however, set the rule to "inherit," "none," or zero (depending on the rule). This will negate the Parent value. Some experimentation may be necessary.</p>
<h5 id="remove_styles">How do I remove a style from the Child Theme?</h5>
<p>Delete the value from the input for the rule you wish to remove. The Child Theme Configurator only adds overrides for rules that contain values.</p>
<h5 id="gradients">How do I create cross-browser gradients?</h5>
<p>The Child Theme Configurator automatically generates the vendor prefixes and filters to display gradients across most browsers. It uses a normalized syntax and only supports two colors without intermediate stops. The inputs consist of origin (e.g., top, left, 135deg, etc.), start color and end color. The browser-specific syntax is generated automatically when you save these values. <strong>Note:</strong> For Internet Explorer, a filter rule approximates the gradient but can only be horizontal (origin top) or vertical (origin left). The legacy webkit-gradient syntax is not supported.</p>
<h5 id="responsive">How do I make my Theme responsive?</h5>
<p>This topic is beyond the scope of this document. The short answer is to use a responsive Parent Theme. Some common characteristics of responsive design are:
<ul><li>Avoiding fixed width and height values. Using max- and min-height values and percentages are ways to make your designs respond to the viewer\'s browser size.</li>
<li>Combining floats and clears with inline and relative positions allow the elements to adjust gracefully to their container\'s width.</li>
<li>Showing and hiding content with Javascript.</li></ul>
<h5 id="web_fonts">How do I add Web Fonts?</h5>
<p>The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the <strong>font-family</strong> rule. Be sure you understand the license for any embedded fonts.</p>
<p>You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab. <strong>Note:</strong> Currently the Child Theme Configurator does not generate previews of imported web fonts, but will in a later release.</p>
<h5 id="functions_file">Where is the functions.php file?</h5>
<p>You can add your own functions.php file, and any other files and directories you need for your Child Theme. The Child Theme Configurator helps you identify and override the Parent stylesheet without touching the other files.</p>
                    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    $screen->add_help_tab( array(
		    	'id'	=> 'ctc_glossary',
			    'title'	=> __( 'Glossary', $chld_thm_cfg->ns ),
			    'content'	=> __( '
<h3 id="terms">Glossary</h3>
<ul><li id="parent_theme"><strong>Parent Theme</strong> The WordPress Theme you wish to edit. WordPress first loads the Child Theme, then loads the Parent Theme. If a style exists in the Child Theme, it overrides the Parent Theme.</li>
 <li id="child_theme"><strong>Child Theme</strong> New Theme based on Parent Theme. You can create any number of Child Themes from a single Parent Theme.</li>
 <li id="class"><strong>Class</strong> A term used to organize objects. For example, a &lt;div&gt; might be assigned the "blue-text" class. The stylesheet might then assign the "color: blue;" rule to members of the "blue-text" class. Thus, the &lt;div&gt; would display text as blue in the browser.</li>
 <li id="selector"><strong>Selector</strong> One or more html elements, classes, ids or other terms used to identify groups of objects.</li>
 <li id="rule"><strong>Rule</strong> One of many standardized attributes used to tell the browser how to display objects matching a given selector. Examples are <strong>color</strong>, <strong>background-image</strong> and <strong>font-size</strong>.</li>
<li id="at-rule"><strong>At-rule</strong> CSS browser instruction to extend default functionality. The Child Theme Configurator supports two At-rules:
<ul> <li id="import"><strong>@import</strong> Instructs the browser to load additional CSS information from an external source.</li>
 <li id="query"><strong>@media (Media Query)</strong> Identifies blocks of styles that are used only when certain browser characteristics are true. Examples are max-width, screen and print.</li>
</ul></li>
 <li id="override"><strong>Override</strong> When a selector exists in both the Child Theme and the Parent Theme, the Child Theme takes priority over the Parent theme. This is where the Child Theme Configurator stands out: it helps you create <strong>exact overrides</strong> of selectors from the Parent Theme, eliminating hours of trial and error.</li>
 </ul> 
				    ', $chld_thm_cfg->ns
			    ),
		    ) );

		    // Set help sidebar
		    $screen->set_help_sidebar(
			    '
			    <ul>
                    <li><a href="http://www.lilaeamedia.com/about/contact/">' . __( 'Contact us', $chld_thm_cfg->ns ) . '</a></li>
				    <li><a href="http://www.lilaeamedia.com/plugins/child-theme-configurator">' . __( 'Plugin Website', $chld_thm_cfg->ns ) . '</a></li>
				    <li><a href="http://codex.wordpress.org/Child_Themes">' . __( 'WordPress Codex', $chld_thm_cfg->ns ) . '</a></li>
				    <li><a href="http://wordpress.stackexchange.com/">' . __( 'WordPress Answers', $chld_thm_cfg->ns ) . '</a></li>
			    </ul>
			    '
		    );
        }
    }
}
?>
