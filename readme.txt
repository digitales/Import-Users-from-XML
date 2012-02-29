=== Import Users from XML ===
Contributors: ross_tweedie
Tags: user, users, xml, batch, import, importer, admin
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 0.1

Import users from an XML file into WordPress

== Description ==

We needed to import user details from a XML file exported from Drupal

This was originally based on the 'import users from csv' plugin

= Features =


== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'Import Users from XML'
1. Click 'Install Now' and activate the plugin
1. Upload your XML file in the 'Users' menu, under 'Import From XML'



For a manual installation via FTP:

1. Upload the `import-users-from-xml` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area
1. Upload your XML file in the 'Users' menu, under 'Import From XML'


To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.
1. Upload your XML file in the 'Users' menu, under 'Import From XML'

== Frequently Asked Questions ==

= How to use? =

Click on the 'Import From XML' link in the 'Users' menu, choose your XML file, choose if you want to send a notification email to new users and if you want the password nag to be displayed when they login, then click 'Import'.

Each element in your XML file should represent a user.
If a column name matches a field in the user table, data from this column is imported in that field; if not, data is imported in a user meta field with the name of the column.


= How to Update Existing Users? =


== Screenshots ==



== Changelog ==

= 0.1 =
First release.
