=== Child Theme Configurator ===
Contributors: lilaeamedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ
Tags: child theme, custom theme, CSS, responsive design, CSS editor, theme generator
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a Child Theme from any installed Theme. Each CSS selector, rule and value can then be searched, previewed and modified.

== Description ==

Created by Lilaea Media, the team that brought you IntelliWidget, the Child Theme Configurator provides a new approach to WordPress stylesheets. The Child Theme Configurator lets you identify and override only the Parent Theme style attributes you want to change. It gives you unlimited control over your WordPress look and feel while leaving your Parent Theme untouched.

The Child Theme Configurator attacks this challenge from a new angle. First, it parses and indexes a Theme's stylesheet so that every media query, selector, rule and value are at your fingertips. Second, it shows you how each change you make will look before you commit it to the Child Theme.Finally, it saves your work so that you can fine-tune your Child Theme without the risk of losing your edits. 
You can create any number of Child Themes from any existing Parent Theme. The Child Theme Configurator lets you choose from your installed themes (even existing Child Themes) and save the results in your Themes directory.

When you are ready, just activate the Child Theme and your WordPress site takes on the new look and feel automatically.

Why create Child Themes using the Child Theme Configurator?

* Apply changes in a Child Theme without touching the Parent Theme
* Identify and override exact selectors from the Parent Theme
* Change colors, backgrounds, font styles globally without changing other rules.
* Tweak individual style selectors 
* Automatically create and preview CSS3 gradients
* Automatically generate cross-browser and vendor-prefixed rules
* View style changes before commiting to them
* Add and modify individual @media queries
* Import web fonts and use them in place of Theme fonts
* Save hours of development time


== Installation ==

1. Download the Child Theme Configurator plugin archive and unzip it.
2. Upload the child-theme-configurator directory to your WordPress plugins directory (e.g., /path/to/wordpress/wp-content/plugins/)
3. Activate the plugin through the 'Plugins' menu in WordPress
   
== Frequently Asked Questions ==

= Where is the functions.php file? =

You can add your own functions.php file, and any other files and directories you need for your Child Theme. The Child Theme Configurator helps you identify and override selectors in the Parent stylesheet without touching the other files.

= How Do I change a specific color/font style/background? =

You can override a specific value globally using the Rule/Value tab. See "Rule/Value," below.

= How Do I add styles that aren't in the Parent Theme? =

You can add queries and selectors using the "New Selector(s)" textarea on the Query/Selector tab. See "Query/Selector," below.

= How Do I remove a style from the Parent Theme? =

You can't really "remove" a style from the Parent. You can, however, set the rule to "inherit," "none," or zero (depending on the rule). This will negate the Parent value. Some experimentation may be necessary.

= How Do I remove a style from the Child Theme? =

Delete the value from the input for the rule you wish to remove. The Child Theme Configurator only adds overrides for rules that contain values.

= How Do I create cross-browser gradients? =

The Child Theme Configurator uses a standardized syntax for gradients and only supports two-color gradients without intermediate stops. The inputs consist of origin (e.g., top, left, 135deg, etc.), start color and end color. The browser-specific syntax is generated automatically when you save these values. See Caveats, below, for more information.

= How Do I make my Theme responsive? =

This topic is beyond the scope of this document. The short answer is to use a responsive Parent Theme. Some common characteristics of responsive design are:

* Avoiding fixed width and height values. Using max- and min-height values and percentages are ways to make your designs respond to the viewer's browser size.
* Combining floats and clears with inline and relative positions allow the elements to adjust gracefully to their container's width.
* Showing and hiding content with Javascript.

= How Do I add Web Fonts? =

The easiest method is to paste the @import code provided by Google, Font Squirrel or any other Web Font site into the @import tab. The fonts will then be available to use as a value of the font-family rule. Be sure you understand the license for any embedded fonts.

You can also create a secondary stylesheet that contains @font-face rules and import it using the @import tab.

== Screenshots ==

1. Example of the Parent/Child Panel.
2. Example of the Query/Selector Panel.
3. Example of the Rule/Value Panel.
4. Example of the @imports Panel.
5. Example of the Preview CSS Panel.

== Changelog ==

== Changelog ==

= 1.0.1 =
* Updates to Readme.txt

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* Initial release.

== Create Your Child Theme ==

The first step is to create a child theme and import your parent theme styles into the configurator.

1. Select an existing parent theme from the menu.
2. Select an existing child theme from the menu, or "New Theme" if you are creating one from scratch.
3. Enter an author for the child theme.
4. If this is a new theme, enter a Name.
5. If this is a new theme, enter a "slug" (lower case, no spaces). This is used to name the theme directory and identify the theme to WordPress.
6. Click "Load Styles." If you are loading an existing child theme, The Child Theme Configurator will create a backup of your existing stylesheet in the theme directory.

== Override Parent Styles ==

There are two ways to identify and override parent styles. The Child Theme Configurator lets you search styles by selector and by rule. If you wish to change a specific selector (e.g., h1), use the "Query/Selector" tab. If you have a specific value you wish to change site-wide (e.g., the color of the type), use the "Rule/Value" tab.

= Query/Selector =

The Query/Selector tab lets you find specific selectors and edit them. First, find the query that contains the selector you wish to edit by typing in the Query autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys. Selectors are in the base query by default.
Next, find the selector by typing in the Selector autoselect box. Select by clicking with the mouse or by pressing the "Enter" or "Tab" keys.

This will load all of the rules for that selector with the Parent values on the left and the Child values inputs on the right. Any existing child values will be automatically populated. There is also a Sample preview that displays the combination of Parent and Child overrides. Note that the border and background-image get special treatment.

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
