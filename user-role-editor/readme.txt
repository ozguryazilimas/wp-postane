=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (shinephp)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 3.5
Tested up to: 3.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

User Role Editor WordPress plugin makes the role capabilities changing easy. You can change any standard WordPress user role.

== Description ==

With User Role Editor WordPress plugin you can change user role (except Administrator) capabilities easy, with a few clicks.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

== Frequently Asked Questions ==
- Does it work with WordPress in multi-site environment?
Yes, it works with WordPress multi-site. By default plugin works for every blog from your multi-site network as for locally installed blog.
To update selected role globally for the Network you should turn on the "Apply to All Sites" checkbox. You should have superadmin privileges to use User Role Editor under WordPress multi-site.

To read full FAQ section visit [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/#faq) at [shinephp.com](shinephp.com).


== Changelog ==

= 4.9 =
* 18.01.2014
* New tab "Default Roles" was added to the User Role Editor settings page. It is possible to select multiple default roles to assign them automatically to the new registered user.
* CSS and dialog windows layout various enhancements
* 'members_get_capabilities' filter was applied to provide better compatibility with themes and plugins which may use it to add its own user capabilities.
* Pro version: Option was added to download jQuery UI CSS from the jQuery CDN.
* Pro version: Bug was fixed: Plugins activation assess restriction section was not shown for selected user under multi-site environment.


= 4.8 =
* 10.12.2013
* Role ID validation rule was added to prohibit numeric role ID - WordPress does not support them.
* HTML markup was updated to provide compatibility with upcoming WordPress 3.8 new administrator backend theme MP6
* It is possible to restrict access of single sites administrators to the selected user capabilities and Add/Delete role operations inside User Role Editor.
* Shortcode [user_role_editor roles="none"]text for not logged in users[/user_role_editor] is available
* Gravity Forms available at "Export Entries", "Export Forms" pages is under URE access restriction now, if such one was set for the user.
* Gravity Forms import could be set under "gravityforms_import" user capability control
* Option was added to show/hide help links (question signs) near the capabilities from single site administrators.
* Plugin "Options" page was divided into sections (tabs): General, Multisite, About.
* Author's information box was removed from URE plugin page.
* Restore previous blog 'switch_to_blog($old_blog_id)' call was replaced to 'restore_current_blog()' where it is possible to provide better compatibility with WordPress API. 
After use 'switch_to_blog()' in cycle, URE clears '_wp_switched_stack' global variable directly instead of call 'restore_current_blog()' inside the cycle to work faster.

= 4.7. =
* 04.11.2013
* "Delete Role" menu has "Delete All Unused Roles" menu item now.
* More detailed warning was added before fulfill "Reset" roles command in order to reduce accident use of this critical operation.
* Bug was fixed at Ure_Lib::reset_user_roles() method. Method did not work correctly for the rest sites of the network except the main blog.
* Post/Pages editing restriction could be setup for the user by one of two modes: 'Allow' or 'Prohibit'.
* Shortcode [user_role_editor roles="role1, role2, ..."]bla-bla[/user_role_editor] for posts and pages was added. 
You may restrict access to content inside this shortcode tags this way to the users only who have one of the roles noted at the "roles" attribute.
* If license key was installed it is shown as asterisks at the input field.
* In case site domain change you should input license key at the Settings page again.

= 4.6.0.2 =
* 27.10.2013
* Bug fix: Invalid notice "Unknown error: Roles import was failed" was shown after successful roles import to the single WordPress site.
* Update: Spaces in user capability are allowed for import to provide compatibility with other plugins, which use spaces in user capabilities, e.g. NextGen Gallery's "NextGEN Change options", etc.

= 4.6.0.1 =
* 26.10.2013
* Bug fix: PHP error prevented to view Gravity Forms entries and WooCommerce coupons after turning on the "Activate user access management to editing selected posts and pages" option.

= 4.6 =
* 23.10.2013
* Content editing restriction: It is possible to differentiate permissions for posts/pages creation and editing. Use the "Activate "Create Post/Page" capability" option for that.
* Content editing restriction: Restrict user to edit just selected posts and pages. Use the "Activate user access management to editing selected posts and pages" option for that.
* Multi-site: Assign roles and capabilities to the users from one point at the Network Admin. Add user with his permissions together to all sites of your network with one click.
* Multi-site: unfiltered_html capability marked as deprecated one. Read this post for more information (http://shinephp.com/is-unfiltered_html-capability-deprecated/).
* Multi-site: 'manage_network%' capabilities were included into WordPress core capabilities list.
* On screen help was added to the "User Role Editor Options" page - click "Help" at the top right corner to read it.
* 'wp-content/uploads' folder is used now instead of plugin's own one to process file with importing roles data.
* Bug fix: Nonexistent method was called to notify user about folder write permission error during roles import.
* Bug fix: turning off capability at the Administrator role fully removed that capability from capabilities list.
* Various internal code enhancements.
* Information about GPLv2 license was added to show apparently – “User Role Editor Pro” are licensed under GPLv2 or later.

Click [here](http://role-editor.com/changelog)</a> to look at [the full list of changes](http://role-editor.com/changelog) of User Role Editor plugin.
