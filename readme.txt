=== Enscrypt ===
Contributors: faazshift
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=selfacclaimedgenius%40gmail%2ecom&lc=US&item_name=WP%20Plugin%20Development%20Fund&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: authentication, auth, hash, login
Requires at least: 4.0
Tested up to: 4.2.1
Stable tag: trunk
License: MIT
License URI: http://opensource.org/licenses/MIT

Enscrypt replaces WordPress authentication protocols with a mechanism that uses the far more secure scrypt hashing algorithm.

== Description ==

Enscrypt replaces wordpress authentication protocols with a mechanism that uses the far more secure scrypt hashing algorithm.

Scrypt is a superior hashing algorithm with configurable CPU and memory parameters. As such, when properly configured, it takes long enough and is secure enough to be highly resistant to brute-force attacks.

Enscrypt can be enabled and disabled at-will without interfering with the built-in WordPress authentication mechanism.

== Installation ==

Outside of having an appropriate WordPress version, the only necessary dependency to use Enscrypt is to install the scrypt PECL extension for PHP.

Assuming you're using Ubuntu Linux and already have the `pecl` PHP package manager installed, perform the following *as root*:

1. Install the scrypt package by running: `pecl install scrypt`
1. Create the module file for PHP by running: `echo "extension=scrypt.so" > /etc/php5/mods-available/scrypt.ini`
1. Enable the module by running: `php5enmod -s ALL scrypt`

If you are using another operating system, you will need to adjust these instructions to accomplish the same result for your OS. If you do not have `pecl` installed, refer to this answer: [http://askubuntu.com/questions/403327/install-pecl-packages-on-ubuntu#answer-403348](http://askubuntu.com/questions/403327/install-pecl-packages-on-ubuntu)

== Changelog ==

= 1.0 =
* Basic functional implementation of Enscrypt working and tested on single site WordPress.