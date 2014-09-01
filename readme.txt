=== Child Theme Configurator ===
Contributors: lilaeamedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ
Tags: child theme, custom theme, CSS, responsive design, CSS editor, theme generator
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 1.4.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a Child Theme from any installed Theme. Each CSS selector, rule and value can then be searched, previewed and modified.

== Description ==

Created by Lilaea Media, the team that brought you IntelliWidget, the Child Theme Configurator provides a new approach to WordPress stylesheets. The Child Theme Configurator lets you identify and override only the Parent Theme style attributes you want to change. It gives you unlimited control over your WordPress look and feel while leaving your Parent Theme untouched.

= Take Control of Your Child Themes =

https://www.youtube.com/watch?v=xL2HkWQxgOA

The Child Theme Configurator parses and indexes a Theme's stylesheet so that every media query, selector, rule and value are at your fingertips. Second, it shows you how each change you make will look before you commit it to the Child Theme. Finally, it saves your work so that you can fine-tune your Child Theme without the risk of losing your edits. 

You can create any number of Child Themes from any existing Parent Theme. The Child Theme Configurator lets you choose from your installed themes (even existing Child Themes) and save the results in your Themes directory.

When you are ready, just activate the Child Theme and your WordPress site takes on the new look and feel automatically.

= Why create Child Themes using the Child Theme Configurator? =

* Apply changes in a Child Theme without touching the Parent Theme
* Identify and override exact selectors from the Parent Theme
* Change colors, backgrounds, font styles globally without changing other rules.
* Tweak individual style selectors 
* Automatically create and preview CSS3 gradients
* Automatically generate cross-browser and vendor-prefixed rules
* View style changes before commiting to them
* Add and modify individual @media queries
* Import web fonts and use them in place of Theme fonts
* Copy theme templates and edit them using Theme Editor
* Upload theme images for use with stylesheets
* Upload custom screenshot for your child theme
* Save hours of development time

= Now it works with plugins! =

We offer a premium extension to let you easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. 

https://www.youtube.com/watch?v=mJ3i6gsuL1E

Learn more at http://www.lilaeamedia.com/plugins/child-theme-plugin-styles

= Build your WordPress website exactly the way you want it. =

PageCells is a WordPress theme framework aimed at web developers and website designers who use WordPress to build multiple websites. Everything about the site is configurable through an innovative drag-and-drop interface, from the number and position of sidebar widget areas to the sequence and behavior of post meta data.

https://www.youtube.com/watch?v=q6g2Jm7bf3U

Learn more at http://www.lilaeamedia.com/total-wordpress-customization-pagecells-responsive-theme-framework

= IntelliWidget Responsive Menu =

Break free from your theme's built-in responsive menu options and take control over the mobile user experience with our premium menu plugin.

https://www.youtube.com/watch?v=JDbxvaEt7VE

Learn more at http://www.lilaeamedia.com/plugins/intelliwidget-responsive-menu

== Installation ==

1. To install from the Plugins repository:
    * In the WordPress Admin, go to "Plugins > Add New."
    * Type "child theme" in the "Search" box and click "Search Plugins."
    * Locate "Child Theme Configurator" in the list and click "Install Now."

2. To install manually:
    * Download the IntelliWidget plugin from http://wordpress.org/plugins/child-theme-configurator
    * In the WordPress Admin, go to "Plugins > Add New."
    * Click the "Upload" link at the top of the page.
    * Browse for the zip file, select and click "Install."

3. In the WordPress Admin, go to "Plugins > Installed Plugins." Locate "Child Theme Configurator" in the list and click "Activate."
   
== Frequently Asked Questions ==

= Does it work with plugins? =

We offer a premium extension to let you easily modify styles for any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and allows you to create custom stylesheets in your Child Theme. Learn more at http://www.lilaeamedia.com/plugins/child-theme-plugin-styles

= Is there a tutorial? =

https://www.youtube.com/watch?v=xL2HkWQxgOA

https://www.youtube.com/watch?v=DSfx2RbZobo

= Why doesn't this work with my (insert theme vendor here) theme? = 

Some themes (particularly commercial themes) do not adhere to the Theme Development guidelines set forth by WordPress.org, and do not automatically load child theme stylesheets or php files. This is unfortunate, because it effectively prohibits the webmaster from adding any customizations (other than those made through the admin theme options) that will survive past an upgrade. 

Contact the vendor directly to ask for this core functionality. It is our opinion that ALL themes (especially commercial ones) must pass the Theme Unit Tests outlined by WordPress.org.

= Why doesn't the Parent Theme have any styles when I "View Parent CSS"? = 

Your Parent theme is probably using a non-standard location for the stylesheets. Check "Scan Parent Theme for additional stylesheets" on the Parent/Child tab and load the Child Theme again.

= Where is it in the Admin? = 

The Child Theme Configurator can be found under the "Tools" menu in the WordPress Admin. Click "Child Themes" to get started.
Click the "Help" tab at the top right for a quick reference.

= Where are the styles? The configurator doesn't show anything! = 

All of the styles are loaded dynamically. You must start typing in the text boxes to select styles to edit.
"Base" is the query group that contains styles that are not associated with any particular "At-rule."

Start by clicking the "Query/Selector" tab and typing "base" in the first box. You can then start typing in the second box to retrieve the style selectors to edit.

= Why do the preview tabs return "Stylesheet could not be displayed"? = 

You have to load a child theme from the Parent/Child tab for the preview to display. This can also happen when your WP_CONTENT_URL is different than $bloginfo('site_url'). Ajax cannot make cross-domain requests by default. Check that your Settings > General > "WordPress Address (URL)" value is correct. (Often caused by missing "www" in the domain.)

= Can I edit the Child Theme stylesheet manually offline or by using the Editor or do I have to use the Configurator? = 

You can make any manual changes you wish to the stylesheet. Just make sure you import the revised stylesheet using the Parent/Child panel or the Configurator will overwrite your changes the next time you use it. Just follow the steps as usual but select the "Use Existing Child Theme" radio button as the "Child Theme" option. The Configurator will automatically update its internal data from the new stylesheet.

= Why are my menus displaying incorrectly when I activate the new child theme? = 

The child theme creates a new instance in the WordPress options data and the menus have to be assigned. Go to Appearance > Menus and assign locations to each of the menus for the new Child Theme. 

= If the parent theme changes (e.g., upgrade), do I have to update the child theme? = 

No. This is the point of using child themes. Changes to the parent theme are automatically inherited by the child theme.

A child theme is not a "copy" of the parent theme. It is a special feature of WordPress that let's you override specific styles and functions leaving the rest of the theme intact. The only time you need to make changes after an upgrade is if the parent removes or changes style or function names. Quality themes should identify any deprecated functions or styles in the upgrade notes so that child theme users can make adjustments accordingly.

= Where are the .php files? = 

The configurator automatically adds a blank functions.php file to the child theme directory. Other parent theme files can be copied using the "Files" tab. Theme images and a custom screenshot can be uploaded there as well.

= How do I change a specific color/font style/background? = 

You can override a specific value globally using the Rule/Value tab. See Rule/Value, above.

= How do I add styles that aren't in the Parent Theme? = 

You can add queries and selectors using the "Raw CSS" textarea on the Query/Selector tab. See Query/Selector, above.

= How do I remove a style from the Parent Theme? = 

You shouldn't really "remove" a style from the Parent. You can, however, set the rule to "inherit," "none," or zero (depending on the rule). This will negate the Parent value. Some experimentation may be necessary.

= How do I remove a style from the Child Theme? = 

Delete the value from the input for the rule you wish to remove. The Child Theme Configurator only adds overrides for rules that contain values.

= How do I set the !important flag? = 

We always recommend relying on good cascading design over global overrides. To that end, you have ability to change the load order of child theme styles by entering a value in the "Order" field. And yes, you can now set rules as important by checking the "!" box next to each input. Please use judiciously.

= How do I create cross-browser gradients? = 

The Child Theme Configurator uses a standardized syntax for gradients and only supports two-color gradients without intermediate stops. The inputs consist of origin (e.g., top, left, 135deg, etc.), start color and end color. The browser-specific syntax is generated automatically when you save these values. See Caveats, below, for more information.

= How do I make my Theme responsive? = 

The short answer is to use a responsive Parent Theme. Some common characteristics of responsive design are:

* Avoiding fixed width and height values. Using max- and min-height values and percentages are ways to make your designs respond to the viewer's browser size.
* Combining floats and clears with inline and relative positions allow the elements to adjust gracefully to their container's width.
* Showing and hiding content with Javascript.

For more information view "Make a Theme Responsive":

https://www.youtube.com/watch?v=iBiiAgsK4G4

= How do I add Web Fonts? = 

The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the font-family rule. Be sure you understand the license for any embedded fonts. 

You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab.

== Screenshots ==

1. Example of the Parent/Child Panel.
2. Example of the Query/Selector Panel.
3. Example of the Rule/Value Panel.
4. Example of the @imports Panel.
5. Example of the Preview CSS Panel.

== Changelog ==

= 1.4.8 =
* Removed backtrace in main CSS parser regex due to high memory usage.

= 1.4.7 =
* Fixed uninitialized variable in files UI.

= 1.4.6 =
* Feature: export child theme as zip archive
* Added transform to list of vendor rules
* Bug fixed: parser not loading multiple instances of same @media rulesets
* Refactored uploader to use wp core functions for compatibility and security
* Increased CHLD_THM_CFG_MAX_RECURSE_LOOPS to 1000 to accommodate complex parent frameworks

= 1.4.5.2 = 
* Fix: javascript bug

= 1.4.5.1 = 
* Fix: regression bug - sanitizing broke raw input selectors

= 1.4.5 = 
* Fix: escape quotes in text inputs. This has bugged me for a while now.
* Fix: Escape backslash for octal content values. Thanks Laurent for reporting this.
* Fix: Normalize colors to lowercase and short form when possible to prevent duplicate entries in the data

= 1.4.4 = 
* Refactored the way CTC caches updates and returns them to the UI controller to reduce memory consumption. 
* Prevent out of memory fatals when generating new child theme.
* Changed "Scan Parent for Additional Stylesheets" to individual checkbox options for each file with a toggle to show/hide in the Parent/Child tab.
* Added automatic update of form when Parent Theme is changed.
* Pre-populate Parent/Child form when parent slug is passed to CTC options.

= 1.4.3 = 
* updated parser to match selectors containing parentheses and empty media rulesets

= 1.4.2 =
* Tweaked the Files tab options and added check for DISALLOW_FILE_EDIT
* Removed automatic @import rules for additional stylesheets that are loaded.
* Fixed bug caused by new jQuery .css function handling of empty css values (preview swatch).

= 1.4.0 =
* New Feature: Theme Files tab: 
* Copy parent templates to child theme to be edited using the Theme Editor.
* Remove child theme templates. 
* Upload child theme images.
* Remove child theme images.
* Upload child theme screenshot.

= 1.3.5 =
* Fixes a bug with the way the @import data is stored that threw errors on php 5.3 and corrupted v1.3.2 @import data.

= 1.3.3 =
* New Feature: option to scan parent theme for additional stylesheets. This allows CTC to be used with themes such as "Responsive" by CyberChimps.
* New Feature: automatically copies parent theme screenshot to child. 

= 1.3.2 =
* Fixed unquoted regex pattern in file path security check function. Thanks to buzcuz for reporting this.

= 1.3.1 =
* Updated help tab content. Added additional sanitization of source and target file paths.

= 1.3.0 =
* Changed CSS preview to retrieve directly from WordPress Admin instead of remote http GET to prevent caching issues.
* Added loading icon for CSS preview.
* Fixed JS type error on backup toggle.
* Improved extensibility throughout.

= 1.2.3 =
* Replace PHP short tags with standard codes.

= 1.2.2 = 
* New Features: You can now rename selectors in place from the Query/Selector panel. Made stylesheet backup optional. Bugs fixed: Incorrect parsing of background position when '0', fixed type error when background image url value is removed.

= 1.2.1 =
* Bugs fixed: "star hack" rules no longer throwing js error. Important flag now works on borders and gradients.

= 1.2.0 =
* New features: Link to Query/Selector tab from specific Rule/Value selector, new rule focus on adding new rule. Bugs fixed: clear Query/Selector inputs when loaded selector is empty, use latest min.js script.

= 1.1.9 =
* Added check for writability before attempting to create child theme files to avoid fatal error on servers not running suEXEC. Fixed a bug in the ctc_update_cache function that was throwing a fatal JS error when new media queries were saved via the Raw CSS input. Configurator now adds functions.php file to child theme when it does not exist.

= 1.1.8 =
* Added reorder sequence and important flag functionality. Fixed bug where multiple inputs with same selector/rule combo were assigned the same id. Fixed bug in the shorthand encoding routine. 

= 1.1.7 =
* Added tutorial video to help tabs.

= 1.1.6 =
* Added call to reset_updates() before update_option() to prevent serialization errors.

= 1.1.5 =
* Query/Selector panel now defaults to 'base'
* Fixed bug causing background-image with full urls (http://) to be parsed as gradients
* Fixed bug causing rule menu to throw error when selector has no rules

= 1.1.4 =
* Fixed sort bug in shorthand parser that was returning rules in wrong order

= 1.1.3 = 
* Fixed bug that assumed lowercase only for theme slugs. (Thanks to timk)
* Fixed update redirect to execute on first run

= 1.1.2 =
* Small bug fix to javascript (casting number to string)

= 1.1.1 =
* Fixed major bug where inputs containing '0' were being ignored
* Removed "no leading digits" requirement for theme slug
* Change query sort function to keep parent order of queries without device width rules
* Fixed gettext calls to use static namespace parameter
* Auto populate child theme inputs when existing theme is selected
* Correctly remove border when values are blanked
* Fixed duplicate "new rule" bug on Query/Selector panel
* added timestamp to backup file 
* Added encode_shorthand function to recombine margin/padding values when all 4 sides are present

= 1.1.0 =
* Corrected parsing for certain backgrounds and gradients (e.g., supports hsla color syntax)
* Handle empty selectors
* Ajax load for menus and updates
* Clean up Parent/Child form UI and validation
* Streamlined UI overall

= 1.0.1 =
* Updates to Readme.txt

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

Removed backtrace in main CSS parser regex due to high memory usage. This should fix the 500 Server errors from large parent stylesheets

== Create Your Child Theme ==

The first step is to create a child theme and import your parent theme styles into the configurator.

1. Select an existing parent theme from the menu.
2. Select "New" or "Existing" child theme.
    * If creating a new theme, enter a "slug" (lower case, no spaces). This is used to name the theme directory and identify the theme to WordPress.
    * If using an existing theme, select a child theme from the menu.
3. Enter a Name for the child theme.
4. Enter an author for the child theme.
5. Enter the child theme version number.
6. If you check "Backup Stylesheet", The Child Theme Configurator will create a backup in the theme directory.
7. If your theme uses additional stylesheets they will appear as checkbox options. Select only the stylesheets you wish to customize to reduce overhead.
8. Click "Generate Child Theme Files."

== Override Parent Styles ==

There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by selector and by rule. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.

= Query/Selector =

The Query/Selector tab lets you find specific selectors and edit them. First, find the query that contains the selector you wish to edit by typing in the Query autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. Selectors are in the base query by default.
Next, find the selector by typing in the Selector autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.

This will load all of the rules for that selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the border and background-image get special treatment.

The "Order" field contains the original sequence of the selector in the parent theme stylesheet. You can change the selector order sequence by entering a lower or higher number in the "Order" field. You can also force style overrides (so called "!important" flag) by checking the "!" box next to each input. Please use judiciously.

Click "Save" to update the child stylesheet and save your changes to the WordPress admin.

= Rule/Value =

The Rule/Value tab lets you find specific values for a given rule and then edit that value for individual selectors that use that rule/value combination. First, find the rule you wish to override by typing in the Rule autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.

This will load all of the unique values that exist for that rule in the parent stylesheet with a Sample preview for that value. If there are values that exist in the child stylesheet that do not exist in the parent stylesheet, they will be displayed as well.

For each unique value, click the "Selectors" link to view a list of selectors that use that rule/value combination, grouped by query with a Sample preview of the value and inputs for the child value. Any existing child values will be automatically populated.

Click "Save" to update the child stylesheet and save your changes to the WordPress admin.

== Add New Styles ==

If you wish to add additional rules to a given selector, first load the selector using the Query/Selector tab. Then find the rule you wish to override by typing in the New Rule autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. This will add a new input row to the selector inputs.

If you wish to add completely new selectors, or even new @media queries, you can enter free-form CSS in the "New Selector" textarea. Be aware that your syntax must be correct (i.e., balanced curly braces, etc.) for the parser to load the new styles. You will know it is invalid because a red "X" will appear next to the save button.

If you prefer to use shorthand syntax for rules and values instead of the inputs provided by the Child Theme Configurator, you can enter them here as well. The parser will convert your input into normalized CSS code automatically.

== Add Imports ==

You can add additional stylesheets and web fonts by typing @import rules into the textarea on the @import tab. Important: The Child Theme Configurator adds the @import rule that loads the Parent Theme's stylesheet automatically. Do not need to add it here.

== Files ==

= Parent Templates =

You can copy PHP template files from the parent theme by checking the boxes. Click "Copy Selected to Child Theme" and the templates will be added to the child theme directory.

CAUTION: If your child theme is active, the child theme version of the file will be used instead of the parent immediately after it is copied. The functions.php file is generated separately and cannot be copied here.

= Child Templates = 

Templates copied from the parent are listed here. These can be edited using the Theme Editor in the Appearance Menu. Remove child theme images by checking the boxes and clicking "Remove Selected from Child Theme."</p>

= Child Theme Images = 

Theme images reside under the <code>images</code> directory in your child theme and are meant for stylesheet use only. Use the media gallery for content images. You can upload new images using the image upload form.

= Child Theme Screenshot = 

You can upload a custom screenshot for the child theme here. The theme screenshot should be a 4:3 ratio (eg., 880px x 660px) JPG, PNG or GIF. It will be renamed "screenshot".

== Preview and Activate ==

Click the Preview CSS tab to see your new masterpiece as CSS code. To preview the stylesheet as a WordPress theme follow these steps:

1. Navigate to Appearance > Themes in the WordPress Admin. You will now see the new Child Theme as one of the installed Themes.
2. Click "Live Preview" below the new Child Theme to see it in action.
3. When you are ready to take the Child Theme live, click "Activate."

== Caveats ==

* No web font preview. Look for live preview of imported fonts in a later release.
* No webkit-gradient. The Child Theme Configurator plugin does not support the legacy webkit gradient. If there is a demand, we will add it in a future release, but most Chrome and Safari users should have upgraded by now.
* Only two-color gradients. The Child Theme Configurator plugin is powerful, but we have simplified the gradient interface. You can use any gradient you want as long as it has two colors and no intermediate stops.
* No @font-face rules. The Child Theme Configurator plugin only supports @media and @import. If you need other @rules, put them in a separate stylesheet and import them into the Child Theme stylesheet.
* Menus may not include certain rules. The Child Theme Configurator plugin loads the rules that exist in the Parent stylesheet. If you find rules are missing from the menus, you can add them using a filter. Stay tuned for details.

== Documentation ==

Go to http://www.lilaeamedia.com/plugins/child-theme-configurator

Copyright: (C) 2014 Lilaea Media
