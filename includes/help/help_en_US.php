<?php  
if (!defined('ABSPATH')) exit;
// Help Content
?>
<!-- BEGIN tab --><h3 id="ctc_getting_started">Start Here</h3>
<p>The first step is to create a child theme and import your parent theme styles into the configurator.</p>
<ol>
  <li>Select an existing parent theme from the menu.</li>
  <li>Select "New" or "Existing" child theme.
    <ul>
      <li>If creating a new theme, enter a "slug" (lower case, no spaces). This is used to name the theme directory and identify the theme to WordPress.</li>
      <li>If using an existing theme, select a child theme from the menu.</li>
    </ul>
  </li>
  <li>Enter a Name for the child theme.</li>
  <li>Enter an author for the child theme.</li>
  <li>Enter the child theme version number.</li>
  <li>Check "Copy Parent Theme Menus, Widgets and other Options", to copy parent theme modifications to the child theme. Depending on the theme, some options may need to be applied using separate theme option controls. <strong>IMPORTANT: This will overwrite existing child theme options.</strong></li>
  <li>Check "Backup Stylesheet", to create a backup of the child theme stylesheet in the child theme directory.</li>
  <li>If your theme uses additional stylesheets they will appear as checkbox options when you open the toggle arrow. Select only the stylesheets you wish to customize to reduce overhead. Remember to select them again if you reload your configuration.</li>
  <li>Click "Generate Child Theme."</li>
  <li><strong>ALWAYS <a target="_blank" href="http://www.lilaeamedia.com/plugins/child-theme-configurator/#preview_activate" title="Test your child theme before activating!">TEST YOUR CHILD THEME</a> BEFORE ACTIVATING!</strong></li>
</ol>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_tutorial">Tutorial Video</h3>
<iframe width="480" height="270" src="//www.youtube.com/embed/xL2HkWQxgOA?rel=0&modestbranding=1" frameborder="0" allowfullscreen></iframe>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_query_selector">Query/Selector</h3>
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Query/Selector tab lets you find specific selectors and edit them. First, find the query that contains the selector you wish to edit by typing in the <strong>Query</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. Selectors are in the <strong>base</strong> query by default.</p>
<p>Next, find the selector by typing in the <strong>Selector</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the rules for that selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the <strong>border</strong> and <strong>background-image</strong> get special treatment.</p>
<p>The "Order" field contains the original sequence of the selector in the parent theme stylesheet. You can change the selector order sequence by entering a lower or higher number in the "Order" field. You can also force style overrides (so called "!important" flag) by checking the "!" box next to each input. Please use judiciously.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_rule_value">Rule/Value</h3>
<p>There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by <strong>selector</strong> and by <strong>rule</strong>. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.</p>
<p>The Rule/Value tab lets you find specific values for a given rule and then edit that value for individual selectors that use that rule/value combination. First, find the rule you wish to override by typing in the <strong>Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.</p>
<p>This will load all of the unique values that exist for that rule in the parent stylesheet with a Sample preview for that value. If there are values that exist in the child stylesheet that do not exist in the parent stylesheet, they will be displayed as well.</p>
<p>For each unique value, click the "Selectors" link to view a list of selectors that use that rule/value combination, grouped by query with a Sample preview of the value and inputs for the child value. Any existing child values will be automatically populated.</p>
<p>Click "Save" to update the child stylesheet and save your changes to the WordPress admin.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_new_styles">Add New Styles</h3>
<p>If you wish to add additional rules to a given selector, first load the selector using the Query/Selector tab. Then find the rule you wish to override by typing in the <strong>New Rule</strong> autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. This will add a new input row to the selector inputs.</p>
<p>If you wish to add completely new selectors, or even new @media queries, you can enter free-form CSS in the "New Selector" textarea. Be aware that your syntax must be correct (i.e., balanced curly braces, etc.) for the parser to load the new styles. You will know it is invalid because a red "X" will appear next to the save button.</p>
<p>If you prefer to use shorthand syntax for rules and values instead of the inputs provided by the Child Theme Configurator, you can enter them here as well. The parser will convert your input into normalized CSS code automatically.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_imports">@imports and Web Fonts</h3>
<p>You can add additional stylesheets and web fonts by typing @import rules into the textarea on the @import tab. <strong>Important: The Child Theme Configurator adds the @import rule that loads the Parent Theme\'s stylesheet automatically. Do not need to add it here.</strong></p>
<p>Below is an example that loads a local custom stylesheet (you would have to add the "fonts" directory and stylesheet) as well as the web font "Open Sans" from Google Web Fonts:</p>
<blockquote>
  <pre><code>
@import url(fonts/stylesheet.css);
@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,700italic);
</code></pre>
</blockquote>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_files">Files</h3>
<h5>Parent Templates</h5>
<p>Copy PHP template files from the parent theme by checking the boxes and clicking "Copy Selected to Child Theme" and the templates will be added to the child theme directory.</p>
<p><strong>CAUTION: If your child theme is active, the child theme version of the file will be used instead of the parent immediately after it is copied.</strong></p>
<p>The <code>functions.php</code> file is generated separately and cannot be copied here.</p>
<h5>Child Templates</h5>
<p>Templates copied from the parent are listed here. These can be edited using the Theme Editor in the Appearance Menu.</p>
<p>Remove child theme images by checking the boxes and clicking "Remove Selected from Child Theme."</p>
<h5>Child Theme Images</h5>
<p>Theme images reside under the <code>images</code> directory in your child theme and are meant for stylesheet use only. Use the media gallery for content images.</p>
<p>You can upload new images using the image upload form.</p>
<h5>Child Theme Screenshot</h5>
<p>You can upload a custom screenshot for the child theme here.</p>
<p>The theme screenshot should be a 4:3 ratio (eg., 880px x 660px) JPG, PNG or GIF. It will be renamed <code>screenshot</code>.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_preview">Preview and Activate</h3>
<p><strong>IMPORTANT: <a target="_blank" href="http://www.lilaeamedia.com/plugins/child-theme-configurator/#preview_activate" title="Test your child theme before activating!">Test your child theme before activating!</a></strong> Some themes (particularly commercial themes) do not adhere to the Theme Development guidelines set forth by WordPress.org, and do not correctly load parent template files or automatically load child theme stylesheets or php files. <strong>In the worst cases they will break your website when you activate the child theme.</strong></p>
<ol>
  <li>Navigate to Appearance > Themes in the WordPress Admin. You will now see the new Child Theme as one of the installed Themes.</li>
  <li>Click "Live Preview" below the new Child Theme to see it in action.</li>
  <li>When you are ready to take the Child Theme live, click "Activate."</li>
</ol>
<p>You can also click the Child or Parent CSS tab to reference the stylesheet code.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_permissions">File Permissions</h3>
<p>WordPress was designed to work on a number of server configurations. Child Theme Configurator uses the WordPress Filesystem API to allow changes to sites that require user permission to edit files.</p>
<p>However, because most of the functionality occurs via AJAX (background) requests, the child theme stylesheet must be writable by the web server.</p>
<p>The plugin will automatically detect your configuration and provide a number of options to resolve this requirement. Use the links provided to find out more about the options available, including:</p>
<ol>
  <li>Temporarily making the stylesheet writable through the plugin.</li>
  <li>Adding your FTP/SSH credentials to the WordPress config file.</li>
  <li>Setting the stylesheet write permissions on the server manually</li>
  <li>Configuring your web server to allow write access in certain situations.</li>
</ol>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_faq">FAQs</h3>
<h5>Does it work with Plugins?</h5>
<p>We offer a premium extension to let you easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. <a href="http://www.lilaeamedia.com/plugins/child-theme-plugin-styles" title="Child Theme Configurator Extension">Learn more</a></p>
<h5 id="doesnt_work">Why doesn’t this work with my (insert theme vendor here) theme?</h5>
<p>Some themes (particularly commercial themes) do not adhere to the Theme Development guidelines set forth by WordPress.org, and do not correctly load parent template files or automatically load child theme stylesheets or php files.</p>
<p>This is unfortunate, because in the best case they effectively prohibit the webmaster from adding any customizations (other than those made through the admin theme options) that will survive past an upgrade. <strong>In the worst case they will break your website when you activate the child theme.</strong></p>
<p>Contact the vendor directly to ask for this core functionality. It is our opinion that ALL themes (especially commercial ones) must pass the Theme Unit Tests outlined by WordPress.org.</p>
<h5>Can I edit the Child Theme stylesheet manually offline or by using the Editor or do I have to use the Configurator?</h5>
<p>You can make any manual changes you wish to the stylesheet. Just make sure you import the revised stylesheet using the Parent/Child panel or the Configurator will overwrite your changes the next time you use it. Just follow the steps as usual but select the "Use Existing Child Theme" radio button as the "Child Theme" option. The Configurator will automatically update its internal data from the new stylesheet.</p>
<h5>Why doesn\'t the Parent Theme have any styles when I "View Parent CSS"?</h5>
<p>Your Parent theme is probably using a separate location for the stylesheets. Select individual stylesheets from the "Parse Additional Stylesheets" section of the Parent/Child tab and click "Generate Child Theme Files" again.</p>
<h5 id="menus-broken">Why are my menus displaying incorrectly when I activate the new child theme?</h5>
<p>The child theme creates a new instance in the WordPress options data and the menus have to be assigned. Go to Appearance &gt; Menus and assign locations to each of the menus for the new Child Theme.</p>
<h5 "preview-not-loading">Why do the preview tabs return "Stylesheet could not be displayed"?</h5>
<p>You have to configure at least one child theme from the Parent/Child tab for the preview to display.</p>
<h5 id="specific_color">How do I change a specific color/font style/background?</h5>
<p>You can override a specific value globally using the Rule/Value tab. See Rule/Value, above.</p>
<h5 id="add_styles">How do I add styles that aren\'t in the Parent Theme?</h5>
<p>You can add queries and selectors using the "New Selector(s)" textarea on the Query/Selector tab. See Query/Selector, above.</p>
<h5 id="add_styles">How do I remove a style from the Parent Theme?</h5>
<p>You shouldn\'t really "remove" a style from the Parent. You can, however, set the rule to "inherit," "none," or zero (depending on the rule). This will negate the Parent value. Some experimentation may be necessary.</p>
<h5 id="remove_styles">How do I remove a style from the Child Theme?</h5>
<p>Delete the value from the input for the rule you wish to remove. The Child Theme Configurator only adds overrides for rules that contain values.</p>
<h5 id="important_flag">How do I set the !important flag?</h5>
<p>We always recommend relying on good cascading design over global overrides. To that end, you have ability to change the load order of child theme styles by entering a value in the "Order" field. And yes, you can now set rules as important by checking the "!" box next to each input. Please use judiciously.</p>
<h5 id="gradients">How do I create cross-browser gradients?</h5>
<p>The Child Theme Configurator automatically generates the vendor prefixes and filters to display gradients across most browsers. It uses a normalized syntax and only supports two colors without intermediate stops. The inputs consist of origin (e.g., top, left, 135deg, etc.), start color and end color. The browser-specific syntax is generated automatically when you save these values. <strong>Note:</strong> For Internet Explorer, a filter rule approximates the gradient but can only be horizontal (origin top) or vertical (origin left). The legacy webkit-gradient syntax is not supported.</p>
<h5 id="responsive">How do I make my Theme responsive?</h5>
<p>The short answer is to use a responsive Parent Theme. Some common characteristics of responsive design are:</p>
<ul>
  <li>Avoiding fixed width and height values. Using max- and min-height values and percentages are ways to make your designs respond to the viewer\'s browser size.</li>
  <li>Combining floats and clears with inline and relative positions allow the elements to adjust gracefully to their container\'s width.</li>
  <li>Showing and hiding content with Javascript.</li>
</ul>
<iframe width="480" height="270" src="//www.youtube.com/embed/iBiiAgsK4G4?rel=0&modestbranding=1" frameborder="0" allowfullscreen></iframe>
<h5 id="web_fonts">How do I add Web Fonts?</h5>
<p>The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the <strong>font-family</strong> rule. Be sure you understand the license for any embedded fonts.</p>
<p>You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab. <strong>Note:</strong> Currently the Child Theme Configurator does not generate previews of imported web fonts, but will in a later release.</p>
<h5 id="functions_file">Where are the PHP files?</h5>
<p>The configurator automatically adds a blank functions.php file to the child theme directory. Other parent theme files can be copied using the "Files" tab. Theme images and a custom screenshot can be uploaded there as well.</p>
<!-- END tab --> 
<!-- BEGIN tab --><h3 id="ctc_glossary">Glossary</h3>
<ul>
  <li id="parent_theme"><strong>Parent Theme</strong> The WordPress Theme you wish to edit. WordPress first loads the Child Theme, then loads the Parent Theme. If a style exists in the Child Theme, it overrides the Parent Theme.</li>
  <li id="child_theme"><strong>Child Theme</strong> New Theme based on Parent Theme. You can create any number of Child Themes from a single Parent Theme.</li>
  <li id="class"><strong>Class</strong> A term used to organize objects. For example, a &lt;div&gt; might be assigned the "blue-text" class. The stylesheet might then assign the "color: blue;" rule to members of the "blue-text" class. Thus, the &lt;div&gt; would display text as blue in the browser.</li>
  <li id="selector"><strong>Selector</strong> One or more html elements, classes, ids or other terms used to identify groups of objects.</li>
  <li id="rule"><strong>Rule</strong> One of many standardized attributes used to tell the browser how to display objects matching a given selector. Examples are <strong>color</strong>, <strong>background-image</strong> and <strong>font-size</strong>.</li>
  <li id="at-rule"><strong>At-rule</strong> CSS browser instruction to extend default functionality. The Child Theme Configurator supports two At-rules:
    <ul>
      <li id="import"><strong>@import</strong> Instructs the browser to load additional CSS information from an external source.</li>
      <li id="query"><strong>@media (Media Query)</strong> Identifies blocks of styles that are used only when certain browser characteristics are true. Examples are max-width, screen and print.</li>
    </ul>
  </li>
  <li id="override"><strong>Override</strong> When a selector exists in both the Child Theme and the Parent Theme, the Child Theme takes priority over the Parent theme. This is where the Child Theme Configurator stands out: it helps you create <strong>exact overrides</strong> of selectors from the Parent Theme, eliminating hours of trial and error.</li>
</ul>
<!-- END tab --> 
<h3 id="ctc_help_sidebar">Links</h3>
<!-- BEGIN sidebar -->
<h4>Now it works with plugins, too!</h4>
<p style="font-size:smaller">Easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. <a href="http://www.lilaeamedia.com/plugins/child-theme-plugin-styles" title="Child Theme Configurator Extension">Learn more</a></p>
<ul>
  <li><a href="http://www.lilaeamedia.com/about/contact/">Contact us</a></li>
  <li><a href="http://www.lilaeamedia.com/plugins/child-theme-configurator">Plugin Website</a></li>
  <li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ">Donate</a></li>
  <li><a href="http://wordpress.org/support/view/plugin-reviews/child-theme-configurator?rate=5#postform">Give Us 5 Stars</a></li>
  <li><a href="http://codex.wordpress.org/Child_Themes">WordPress Codex</a></li>
  <li><a href="http://wordpress.stackexchange.com/">WordPress Answers</a></li>
</ul>
<!-- END sidebar -->