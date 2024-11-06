=== QuickPost Summary ===
Contributors: wp-autoplugin
Donate link: https://wp-autoplugin.com/donate
Tags: summary, post summary, OpenAI, content generation, WordPress
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.2
Stable tag: 1.00
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

QuickPost Summary is a WordPress plugin that generates and displays summaries of post content using OpenAI's API in a responsive, user-friendly modal.

== Description ==

QuickPost Summary enhances your WordPress posts by automatically generating a concise summary of each post’s content. Using OpenAI’s API, this plugin delivers accurate and well-structured summaries that readers can view in a modal. The modal is designed to be responsive and accessible across different screen sizes.

= Key Features =

* Automatically generates summaries for WordPress posts using OpenAI’s API.
* Provides a user-friendly, responsive modal display for viewing summaries.
* Customizable max word count for summaries.
* AJAX integration for generating and saving summaries without page reloads.
* Works seamlessly with the WordPress block editor and classic editor.

= How It Works =

QuickPost Summary includes a "Show Summary" button that displays the post summary in a centered, fixed modal overlay. The plugin integrates with the OpenAI API to generate accurate summaries based on the post content, which can be customized in length based on user preferences. Admins can easily configure API settings from the plugin settings page.

= Usage Instructions =

1. Install and activate the plugin.
2. Configure OpenAI API settings in the plugin settings page.
3. Open any post in the editor and use the "Generate Summary" button in the sidebar meta box to create a summary.
4. Click "Show Summary" on the front end to view the generated summary in a responsive modal.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/quickpost-summary` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > QuickPost Summary** to configure your OpenAI API key and model.
4. Open a post and use the "Generate Summary" button in the sidebar to create summaries for individual posts.

== Frequently Asked Questions ==

= Does this plugin require an OpenAI API key? =

Yes, you need an OpenAI API key to generate summaries. You can obtain a key from the OpenAI website.

= How do I customize the length of the summaries? =

In the sidebar of each post, there’s an input field for "Max Word Count" where you can specify the maximum length for each summary.

= Can I customize the styling of the modal? =

Yes, the modal’s CSS can be customized by editing the provided styles in your theme’s custom CSS file or overriding the plugin’s CSS.

== Screenshots ==

1. **Settings Page** - Configure the OpenAI API key and model for generating summaries.
2. **Post Editor Sidebar** - Generate summaries and specify word count directly within the editor.
3. **Front-End Summary Modal** - View the summary in a responsive, centered modal overlay.

== Changelog ==

= 1.11 =
* Enhanced modal styling for better mobile compatibility.
* Improved API error handling and messaging.
* Updated tested WordPress version to 6.3.

= 1.10 =
* Added support for Gutenberg editor integration.
* Implemented AJAX for generating summaries without page reloads.

= 1.0 =
* Initial release of QuickPost Summary.

== Upgrade Notice ==

= 1.11 =
This update improves mobile styling and API error handling. Recommended for users who want a more responsive display on smaller screens.

== License ==

This plugin is licensed under the GPLv2 or later. For more details, see the [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).
