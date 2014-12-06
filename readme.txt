=== Child Theme Configurator ===
Contributors: lilaeamedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ
Tags: child theme, customize, CSS, responsive, css editor, theme generator, stylesheet, customizer
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a Child Theme from any installed theme. Search, preview and customize any selector, rule or value using this fast CSS editor.

== Description ==

Child Theme Configurator is a fast and easy to use CSS editor that allows you to create Child Themes and customize them well beyond the Theme Customizer. The Child Theme Configurator lets you identify and override only the Parent Theme CSS attributes you want to change. It gives you unlimited control over your WordPress look and feel while leaving your Parent Theme untouched.

= Take Control of Your Child Themes =

https://www.youtube.com/watch?v=53M7RVxDYEY

The Child Theme Configurator parses and indexes a Theme's stylesheets so that every CSS media query, selector, rule and value are at your fingertips. Second, it shows you how each change you make will look before you commit it to the Child Theme. Finally, it saves your work so that you can fine-tune your Child Theme without the risk of losing your edits. 

You can create any number of Child Themes from any existing Parent Theme. The Child Theme Configurator lets you choose from your installed themes (even existing Child Themes) and save the results in your Themes directory.

When you are ready, just activate the Child Theme and your WordPress site takes on the new look and feel automatically.

= Why create Child Themes using the Child Theme Configurator? =

* Update themes without losing customizations
* Save hours of development time
* Make modifications above and beyond the theme Customizer
* Load parent theme stylesheet with <link> instead of @import
* Export Child Theme as Zip Archive
* Import web fonts and use them in place of theme fonts
* Apply changes in a child theme without touching the parent theme
* Identify and override exact selectors from the parent theme
* Change specific colors, backgrounds, font styles, etc., without changing other elements
* Automatically create and preview CSS3 gradients
* Automatically generate cross-browser and vendor-prefixed rules
* Preview style changes before committing to them
* Add and modify individual @media queries
* Uses WP Filesystem API – will not create files you cannot remove
* Nag-free, no-strings-attached user experience

= Not just for themes but plugins too! =

We offer a premium extension that brings the CSS editing power of Child Theme Configurator to any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and creates custom CSS in your Child Theme. 

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
   
= 10 Easy Steps to Create a Child Theme =

1. Select the theme you want to configure from the "Parent Theme" menu.

2. Select "new" or "existing".

3. Enter a Name, Author and Version for the child theme. 

4. Copy Parent Theme Menus, Widgets and other Options (optional)

5. Save Backup (optional)

6. Choose parent stylesheet handling
    * Select <link> if the parent theme uses the main 'style.css' stylesheet and correctly enqueues it for child themes (default).
    * Select @import for older themes that do not enqueue the stylesheet. If the parent styles do not appear when you activate the child theme, you probably need to use this option. NOTE: this was the only method used in previous versions of Child Theme Configurator.
    * Select "None" if the parent theme does not use the main 'style.css' for its core styles but enqueues it for child themes. This is a common practice with more recent themes. 
    * If you do not know which option to use, select <link>.

7. Restore from backup (optional).

8. Choose additional stylesheets (optional).

9. Click "Generate/Rebuild Child Theme Files."

10. IMPORTANT: Always test your child theme with Live Preview before activating!

== Frequently Asked Questions ==

= HELP! I changed a file and now I am unable to access my website or login to wp-admin to fix it! =

To back out of a broken child theme you have to manually rename the offending theme directory name (via FTP, SSH or your web host control panel file manager) so that WordPress can’t find it. WordPress will then throw an error and revert back to the default theme (currently twenty-fourteen).

The child theme is in your themes folder, usually

[wordpress]/wp-content/themes/[child-theme]

To prevent this in the future, always test your child theme with Live Preview before activating.

= Why are my menus displaying incorrectly when I activate the new child theme? =
...or...
= Why is my custom header missing when I activate the new child theme? =
...or...
= Why does my custom background go back to the default when I activate the new child theme? =
...or...
= Why do my theme options disappear when I activate the new child theme? =

These options are specific to each theme and are saved separately in the database. When you create a new child theme, its options are blank.

Many of these options can be copied over to the child theme by checking "Copy Parent Theme Menus, Widgets and other Options" when you generate the child theme files on the Parent/Child tab.

If you want to set different options you can either apply them after you activate the child theme, or by using the "Live Preview" under Appearance > Themes.

* Menus: Go to Appearance > Menus and click the "Locations" tab. By default, the primary menu will generate the links automatically from the existing pages. Select your customized Menu from the dropdown and click "Use New Menu." This will replace the default menu and you will see the correct links.
* Header: Go to Appearance > Header. Some themes will show the "Title" and "Tagline" from your "General Settings" by default. Click "Choose Image" and find the header from the Media Library or upload a new image. This will replace default with your custom image.
* Background: Go to Appearance > Background and choose a new background color or image.
* Options: Every theme handles options in its own way. Most often, they will create a set of options and store them in the WordPress database. Some options are specific to the active theme (or child theme), and some are specific to the parent theme only (meaning the child theme CANNOT override them). You will have to find out from the theme author which are which.

= How do I add Web Fonts? =

The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the font-family rule. Be sure you understand the license for any embedded fonts.

You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab. 

= Does it work with Multi site? =

Using with WordPress Network sites requires additional steps:

1. Install as Network Admin and Network Enable the Plugin.
2. Go to the site you want to customize.
3. Go to Tools > Child Themes and configure a child theme for the parent theme you want to use
4. Go back to Network Admin and Network Enable the new Child theme.
5. Go back the site and activate the child theme.

Now you can edit your child theme from Tools > Child Themes. NOTE: Only users with "edit_theme_options" capability will have access to the Child Theme Configurator.

= Does it work with plugins? =

We offer a premium extension that brings the CSS editing power of Child Theme Configurator to any WordPress Plugin installed on your website. The Child Theme Configurator Plugin Extension scans your plugins and creates custom CSS in your Child Theme. Learn more at http://www.lilaeamedia.com/plugins/child-theme-plugin-styles

= Is there a tutorial? =

https://www.youtube.com/watch?v=53M7RVxDYEY

= Why doesn't this work with my (insert theme vendor here) theme? = 

Some themes (particularly commercial themes) do not correctly load parent template files or automatically load child theme stylesheets or php files.

This is unfortunate, because in the best case they effectively prohibit the webmaster from adding any customizations (other than those made through the admin theme options) that will survive past an upgrade. **In the worst case they will break your website when you activate the child theme.** 

Contact the vendor directly to ask for this core functionality. It is our opinion that ALL themes (especially commercial ones) must pass the Theme Unit Tests outlined by WordPress.org and ALWAYS TEST YOUR CHILD THEME BEFORE ACTIVATING (See "Preview and Activate").

= Will this slow down my site? =

The plugin only loads the bulk of the code in the admin when you are using the tool. The biggest performance hit occurs when you generate the Child Theme files from the Parent/Child tab.

Once the child theme stylesheet is created, CTC adds very little overhead to the front-end since all of the functionality is in the admin.

= Why doesn't the Parent Theme have any styles when I "View Parent CSS"? = 

Check the appropriate additional stylesheets under "Scan Parent Theme for additional stylesheets" on the Parent/Child tab and load the Child Theme again. CTC tries to identify these files by fetching a page from the parent theme, but you may need to set them manually.

= Where is it in the Admin? = 

The Child Theme Configurator can be found under the "Tools" menu in the WordPress Admin. Click "Child Themes" to get started. NOTE: Only users with "edit_theme_options" capability will have access to the Child Theme Configurator.

Click the "Help" tab at the top right for a quick reference.

= Where are the styles? The configurator doesn't show anything! = 

All of the styles are loaded dynamically. You must start typing in the text boxes to select styles to edit.
"Base" is the query group that contains styles that are not associated with any particular "At-rule."

Start by clicking the "Query/Selector" tab and typing "base" in the first box. You can then start typing in the second box to retrieve the style selectors to edit.

= Why do the preview tabs return "Stylesheet could not be displayed"? = 

You have to load a child theme from the Parent/Child tab for the preview to display. This can also happen when your WP_CONTENT_URL is different than $bloginfo('site_url'). Ajax cannot make cross-domain requests by default. Check that your Settings > General > "WordPress Address (URL)" value is correct. (Often caused by missing "www" in the domain.)

= Can I edit the Child Theme stylesheet manually offline or by using the Editor or do I have to use the Configurator? = 

You can make any manual changes you wish to the stylesheet. Just make sure you import the revised stylesheet using the Parent/Child panel or the Configurator will overwrite your changes the next time you use it. Just follow the steps as usual but select the "Use Existing Child Theme" radio button as the "Child Theme" option. The Configurator will automatically update its internal data from the new stylesheet.

= If the parent theme changes (e.g., upgrade), do I have to update the child theme? = 

No. This is the point of using child themes. Changes to the parent theme are automatically inherited by the child theme.

A child theme is not a "copy" of the parent theme. It is a special feature of WordPress that let's you override specific styles and functions leaving the rest of the theme intact. The only time you need to make changes after an upgrade is if the parent removes or changes style or function names. Quality themes should identify any deprecated functions or styles in the upgrade notes so that child theme users can make adjustments accordingly.

= Where are the .php files? = 

The Child Theme Configurator automatically adds a blank functions.php file to the child theme directory. You can copy parent theme template files using the Files tab. If you want to create new templates and directories you will have to create/upload them manually via FTP or SSH. Remember that a child theme will automatically inherit the parent theme's templates unless they also exist in the child theme directory. Only copy templates that you intend to customize.

= How do I change a specific color/font style/background? = 

You can override a specific CSS value globally using the Rule/Value tab. See Rule/Value, above.

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


== Screenshots ==

1. Parent/Child tab
2. Parent/Child tab with parent theme menu open
3. Query/Selector tab
4. Rule/Value tab
5. @import tab
6. Parent CSS tab
7. Files tab

== Changelog ==

= 1.6.1 = 
* Fix: add check if theme uses hard-wired stylesheet link and alert to use @import instead of link option
* Fix: conflicts with using jQuery UI from CDN - using local version of 1.11.2 Widget/Menu/Selectmenu instead
* Fix: using wp-color-picker handle instead of iris as dependency to make sure wpColorPicker() methods are loaded
* Fix: copy parent theme widgets logic different if child or parent are active theme

= 1.6.0 = 
* New Feature: option to load parent stylesheet using wp_enqueue_style (link), @import or none. Thanks to cmwwebfx and Shapeshifter3 for pushing me on this 
* New Feature: automatically-generated child theme slug and name
* New Feature: restore from backup and reset options
* New Feature: backup files to "Child Theme Files" on Files Tab so they can be deleted
* New Feature: Added new theme chooser select menu with screenshot, theme info and link to live preview.
* Fix: Admin scripts now only load when CTC page is being viewed.
* Fix: parent CSS preview to correctly display all parsed parent stylesheets in sequence
* Fix: Refactored throughout for maintainability

= 1.5.4 =
* New Feature: Load imported stylesheets into the CTC admin so web fonts can be previewed.
* Set preview swatch to z-index -1 to prevent it from covering up the controls
* Spread config data across multiple option records to prevent out of memory errors with large stylesheets.
* Do not automatically select Bootstrap CSS files as additional stylesheets to (greatly) reduce overhead.
* Add jQuery UI styles that are no longer being loaded by default in the WP admin (autoselect menus).

= 1.5.3 =
* Fixed a bug in the way zero values are handled that was breaking css output in certain situations
* Added regex filter for non-printable (e.g., null) characters in input strings

= 1.5.2.2 =
* Fixed a bug introduced in v1.5.2(.1) that copied all of the parent styles to the child stylesheet. This should only be an issue for 'background-image' styles that reference images in the parent theme and do not have child theme overrides. If you need to remove all styles generated by this bug, install the development version, otherwise delete the redundant child values or just wait for the v1.5.3 release.
* Rolled back changes to the javascript controller that introduced a number of type errors.
* Tweaked preview ajax call to handle ssl.

= 1.5.2.1 =
* Automatically set additional stylesheets to parse based on parent theme links in head.
* Render parent CSS including additional stylesheets 

= 1.5.1 =
* Added copy option to Parent/Child tab to assign menu locations, sidebars/widgets, custom header, background, and other options to the new Child Theme. 

= 1.5.0 =
* We have completely refactored CTC to use the WP_Filesystem API. 
* If your web host is configured to use suExec (meaning it runs under the user of the web account being accessed), the changes will be completely transparent. 
* Other configurations will now require user credentials to add, remove or update Child Theme files. 
* To make things easier we added the ability for you to make the files writable while editing and then make them read-only when you are done.
* You can also set your credentials in wp-config.php: http://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants
* Contact us at http://www.lilaeamedia.com/about/contact if you have any questions.

= 1.4.8 =
* Removed backreference in main CSS parser regex due to high memory usage.

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

v.1.6.1 Fixes for bugs that arose due to jQuery conflicts with new features. Corrected copying of widgets to/from active theme.

== Override Parent Styles ==

There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by CSS selector and by rule. If you wish to change a specific CSS selector (e.g., h1), use the "Query/Selector" tab. If you have a specific CSS value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.

= Query/Selector Tab =

The Query/Selector tab lets you find specific CSS selectors and edit them. First, find the query that contains the CSS selector you wish to edit by typing in the Query autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. CSS selectors are in the base query by default.
Next, find the CSS selector by typing in the "Selector" autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.

This will load all of the rules for that CSS selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the border and background-image get special treatment.

The "Order" field contains the original sequence of the CSS selector in the parent theme stylesheet. You can change the CSS selector order sequence by entering a lower or higher number in the "Order" field. You can also force style overrides (so called "!important" flag) by checking the "!" box next to each input. Please use judiciously.

Click "Save" to update the child stylesheet and save your changes to the WordPress admin.

== Adding New Styles ==

If you wish to add additional rules to a given CSS selector, first load the selector using the Query/Selector tab. Then find the rule you wish to override by typing in the New Rule autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. This will add a new input row to the selector inputs.

If you wish to add completely new CSS selectors, or even new @media queries, you can enter free-form CSS in the "Raw CSS" textarea. Be aware that your syntax must be correct (i.e., balanced curly braces, etc.) for the parser to load the new styles. You will know it is invalid because a red "X" will appear next to the save button.

If you prefer to use shorthand syntax for rules and values instead of the inputs provided by the Child Theme Configurator, you can enter them here as well. The parser will convert your input into normalized CSS code automatically.

= Rule/Value Tab =

The Rule/Value tab lets you find specific values for a given rule and then edit that value for individual CSS selectors that use that rule/value combination. First, find the rule you wish to override by typing in the Rule autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.

This will load all of the unique values that exist for that rule in the parent stylesheet with a Sample preview for that value. If there are values that exist in the child stylesheet that do not exist in the parent stylesheet, they will be displayed as well.

For each unique value, click the "Selectors" link to view a list of CSS selectors that use that rule/value combination, grouped by query with a Sample preview of the value and inputs for the child value. Any existing child values will be automatically populated.

Click "Save" to update the child stylesheet and save your changes to the WordPress admin.

If you want to edit all of the rules for the CSS selector you can click the “Edit” link and the CSS selector will automatically load in the Query/Selector Tab.

== @import Tab and Web Fonts ==

You can add additional stylesheets and web fonts by typing @import rules into the textarea on the @import tab. **Important: do not import the parent theme stylesheet here.** Use the &quot;Parent stylesheet handling&quot; option from the Parent/Child tab.

== Files Tab ==

= Parent Templates =

You can copy PHP template files from the parent theme by checking the boxes. Click "Copy Selected to Child Theme" and the templates will be added to the child theme directory.

CAUTION: If your child theme is active, the child theme version of the file will be used instead of the parent immediately after it is copied. The functions.php file is generated separately and cannot be copied here.

= Child Theme Files = 

Templates copied from the parent are listed here. These can be edited using the Theme Editor in the Appearance Menu. Remove child theme images by checking the boxes and clicking "Delete Selected."</p>

= Child Theme Images = 

Theme images reside under the <code>images</code> directory in your child theme and are meant for stylesheet use only. Use the media gallery for content images. You can upload new images using the image upload form.

= Child Theme Screenshot = 

You can upload a custom screenshot for the child theme here. The theme screenshot should be a 4:3 ratio (eg., 880px x 660px) JPG, PNG or GIF. It will be renamed "screenshot".

= Export Child Theme as Zip Archive =

You can download your child theme for use on another WordPress site by clicking "Export".

== Preview and Activate ==

**IMPORTANT: Test your child theme before activating!**

Some themes (particularly commercial themes) do not correctly load parent template files or automatically load child theme stylesheets or php files.

**In the worst cases they will break your website when you activate the child theme.**

1. Navigate to Appearance > Themes in the WordPress Admin. You will now see the new Child Theme as one of the installed Themes.
2. Click "Live Preview" below the new Child Theme to see it in action.
3. When you are ready to take the Child Theme live, click "Activate."

== Caveats ==

* No legacy webkit-gradient. The Child Theme Configurator plugin does not support the legacy webkit gradient. If there is a demand, we will add it in a future release, but most Chrome and Safari users should have upgraded by now.
* Only two-color gradients. The Child Theme Configurator plugin is powerful, but we have simplified the gradient interface. You can use any gradient you want as long as it has two colors and no intermediate stops.
* No @font-face rules. The Child Theme Configurator plugin only supports @media and @import. If you need other @rules, put them in a separate stylesheet and import them into the Child Theme stylesheet.
* Not all CSS rules are supported. The Child Theme Configurator plugin works with the vast majority of CSS rules, however we’ve left out some of the more obscure options.
* Multiple versions of the same rule in a single selector are not supported, with a few exceptions. The Child Theme Configurator plugin will automatically generate vendor-prefix variations for background-image, border-radius, transform, transition, and others.

== Documentation ==

Go to http://www.lilaeamedia.com/plugins/child-theme-configurator

Copyright: (C) 2014 Lilaea Media
