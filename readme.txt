=== Paid Memberships Pro - MailPoet Add On ===
Contributors: strangerstudios, andrewza, dlparker1005, paidmembershipspro
Tags: mailpoet, paid newsletter, private newsletter, pmpro, paid memberships pro, restrict content
Requires at least: 5.2
Tested up to: 6.0.2
Stable tag: 3.0
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

Paid newsletters for WordPress. Automatically subscribe members to MailPoet lists or allow them to opt-in to specific MailPoet newsletters.

== Description ==

This integration plugin is designed to support membership sites that use MailPoet to distribute free or paid email newsletters.

### How the MailPoet Integration Works

The MailPoet Integration for Paid Memberships Pro automatically subscribes members to assigned MailPoet lists by level. Members can also opt-in to specific lists as part of the membership checkout process.

When a user’s membership level is cancelled or expired, they are automatically added to your selected non-member list (for members who have cancelled or expired) and are appropriately tagged in MailPoet. This allows you to send premium email content only to your active members, by membership level.

> This plugin requires [MailPoet](https://wordpress.org/plugins/mailpoet/) and [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/).

[MailPoet](https://wordpress.org/plugins/mailpoet/) is an email marketing plugin and service that you can use directly inside your WordPress site. You can use the MailPoet drag-and-drop builder to design custom email newsletters, select subscribers, and distribute emails through the WordPress admin.

### About Paid Memberships Pro

[Paid Memberships Pro is a WordPress membership plugin](https://www.paidmembershipspro.com/?utm_source=wordpress-org&utm_medium=readme&utm_campaign=pmpro-mailpoet) that puts you in control. Create what you want and release in whatever format works best for your business.

* Courses & E-Learning
* Private Podcasts
* Premium Newsletters
* Private Communities
* Sell Physical & Digital Goods

Paid Memberships Pro allows anyone to build a membership site—for free. Restrict content, accept payment, and manage subscriptions right from your WordPress admin.

Paid Memberships Pro is built "the WordPress way" with a lean core plugin and over 75 Add Ons to enhance every aspect of your membership site. Each business is different and we encourage customization. For our members we have a library of 300+ recipes to personalize your membership site.

Paid Memberships Pro is the flagship product of Stranger Studios. We are a bootstrapped company which grows when membership sites like yours grow. That means we focus our entire company towards helping you succeed.

[Try Paid Memberships Pro entirely for free on WordPress.org](https://wordpress.org/plugins/paid-memberships-pro/) and see why 100,000+ sites trust us to help them #GetPaid.

### Read More

Want more information on paid email newsletters and members-only emails with Paid Memberships Pro, MailPoet, and WordPress membership sites? Have a look at:

* The [Paid Memberships Pro](https://www.paidmembershipspro.com/?utm_source=wordpress-org&utm_medium=readme&utm_campaign=pmpro-mailpoet) official homepage.
* The [MailPoet Integration for PMPro documentation page](https://www.paidmembershipspro.com/add-ons/mailpoet-integration/?utm_source=wordpress-org&utm_medium=readme&utm_campaign=pmpro-mailpoet).
* Also follow PMPro on [Twitter](https://twitter.com/pmproplugin), [YouTube](https://www.youtube.com/channel/UCFtMIeYJ4_YVidi1aq9kl5g) & [Facebook](https://www.facebook.com/PaidMembershipsPro/).

== Installation ==

Note: You must have [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/) installed and activated on your site.

### Install PMPro MailPoet from within WordPress

1. Visit the plugins page within your dashboard and select "Add New"
1. Search for "PMPro MailPoet"
1. Locate this plugin and click "Install"
1. Activate "Paid Memberships Pro - MailPoet Add On" through the "Plugins" menu in WordPress
1. Go to "after activation" below.

### Install PMPro MailPoet Manually

1. Upload the `MailPoet-Paid-Memberships-Pro-Add-on` folder to the `/wp-content/plugins/` directory
1. Activate "Paid Memberships Pro - MailPoet Integration" through the "Plugins" menu in WordPress
1. Go to "after activation" below.

### After Activation: Configure Membership Settings for MailPoet
Below is a description of the various settings available in the plugin. Navigate to Memberships > MailPoet in the WordPress admin to configure the plugin for your site

* Non-Members List: Users are automatically subscribed to non-member lists when they register without a membership level or when their membership level is removed.
* Opt-In List: Give users the option to subscribe to additional lists at checkout and on their profile page.
* Membership Lists: Assign MailPoet subscriptions to each membership. Members are automatically subscribed to these lists during checkout or after membership level changes. You can add members to a single list or multiple lists depending on your needs.
* Unsubscribe On Level Change? Choose whether subscribers should be removed from their level lists when their membership changes. This setting unsubscribes members from their current level’s list when they cancel, expire, or otherwise change their membership level.

### Import Current Members to MailPoet
This integration plugin does not perform a retroactive update of all membership levels > subscriber list settings. If you want to start using MailPoet and you already have members, you must complete a one-time import to sync MailPoet subscribers and members.

[View the Guide: How to Import Your PMPro Members List to MailPoet Subscriber Lists »](https://www.paidmembershipspro.com/import-members-to-mailpoet-lists/)

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the issues section of GitHub and we'll fix it as soon as we can. Thanks for helping. [https://github.com/strangerstudios/MailPoet-Paid-Memberships-Pro-Add-on/issues](https://github.com/strangerstudios/MailPoet-Paid-Memberships-Pro-Add-on/issues)

= I need help installing, configuring, or customizing the plugin. =

Please visit [our support site at https://www.paidmembershipspro.com](http://www.paidmembershipspro.com/) for more documentation and our support forums.

== Changelog ==
= 3.0 - 2022-09-05 =
* FEATURE: Complete overhaul to support MailPoet v3+

## Upgrade Notice ##
### 3.0 ###
This is a complete overhaul of the plugin to work with MailPoet v3. It will no longer work with MailPoet v2 or earlier.