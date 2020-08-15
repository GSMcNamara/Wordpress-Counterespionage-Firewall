=== Counterespionage Firewall ===
Contributors: floodspark
Donate link: http://floodspark.com/donate.html
Tags: espionage, recon, reconnaissance, intelligence, intel, security, defense, bots, fraud
Requires at least: 5.3.2
Tested up to: 5.5
Requires PHP: 7.0.33
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

The Floodspark Counterespionage Firewall (CEF) WordPress plugin helps you block reconnaissance or otherwise illegitimate traffic including hackers, bots, and scrapers.

== Description ==

Floodspark Counterespionage Firewall (CEF) helps you block reconnaissance or otherwise illegitimate traffic. CEF is like a web application firewall (WAF) but protects against reconnaissance. CEF focuses on pre-attack protection and is designed to complement security plugins such as Wordfence or Sucuri.

Using Intent Indicators, CEF can detect and protect earlier than an Indicators of Compromise (IoCs) or IP blacklist-based solution. Our goal is to help you increase performance, reduce fraud, thwart attacks, and serve your real customers.

So far CEF for WordPress can:
* Fake out WPScan and bots by hiding your real usernames, instead supplying them with fake ones they will never be able to log in with.

...and detect and block:
* Tor browser, with minor delay
* Chrome Incognito, with minor delay, over HTTPS
* Firefox Private Browsing, with minor delay
* Chrome-Selenium in its default configuration, with minor delay
* cURL in its default configuration
* Wget in its default configuration
* HTTP methods other than GET, POST, and HEAD
* Proxy probing

Feedback is greatly appreciated as we continue to shape Floodspark and expand what it can do. Email us anytime - gs@floodspark.com. 

Stay up to date with developments in the Floodspark portfolio: [http://floodspark.com/uptodate.html](http://floodspark.com/uptodate.html)

== Frequently Asked Questions ==

= Does CEF replace a Web Application Firewall (WAF)? = 

No. CEF and was specifically designed to leave protection against active web attacks to WAFs, which do it best.

= Does CEF replace a host firewall? =

No. CEF specializes in web-type intelligence and leaves the protection of other services to the host firewall.

= Should I keep my WAF and host firewall? =

Yes.

= Why would I use CEF? =

CEF helps you earlier in the cyber-attack chain, during the Reconnaissance stage, to disrupt malicious research efforts. Remember, attacks do not necessarily correlate with the research origin(s).

= What is an Intent Indicator? =

An Intent Indicator is a trait derived from cyber threat intelligence that with high confidence indicates malicious intent. You do not need to activate every Intent Indicator powering CEF if for some reason one or more break your business traffic. E.g. A bank may want to block visitors using Tor to reduce fraud, while an online newspaper may recognize that readers and journalists have an interest in using Tor to avoid censorship and retribution.

= How is an Intent Indicator different than an Indicator of Compromise (IoC)? =

BLUF: An Intent Indicator is earlier than an IoC. You can see why this is beneficial.

An IOC indicates that a breach already took place, allowing you only to respond after the fact. Intent Indicators are the attacker’s traits, or Tactics, Techniques, and Procedures (TTPs), observable during the recon phase--traits, that with high confidence, would not belong to legitimate visitor traffic and behavior.

== Screenshots ==
1. Faking WPScan's username hunting. Real usernames were "admin", "admin2", and "admin3". No hacker can log in with these faked usernames because they don't actually exist.
2. Error message the visitor will receive for banned behavior or devices.
3. Defeating hackertarget.com's WordPress username enumeration scan

== Changelog ==

= 1.4.0 =
* CEF now disrupts hacker attempts at username gathering/harvesting/enumeration

= 1.3.0 =
* Fakes most current version of PHP

= 1.2.0 =
* Permitted HTTP methods safelisting
* Block proxy probes
* Blocked message appears for bad visitors
* General fixes

= 1.1.0 = 
* Added Wget detection
* Commented out debugging/localhost settings

= 1.0 =
* Initial public release

== Upgrade Notice ==

= 1.4.0 =
CEF now hides your real usernames from hackers. [Read about this unique approach on our blog](https://floodspark.com/blog/information-warfare-vs-security-through-obscurity/)

= 1.3.0 = 
CEF now fakes the most current version of PHP to throw off attacker intelligence gathering.

= 1.2.0 = 
Additional detections are included in this release. Also a message will appear for blocked users.

= 1.1.0 = 
Additional detection implemented and a bug fix.

= 1.0 =
Initial public release

== Cyber Intent Blog ==
The [Floodspark Cyber Intent Blog](http://floodspark.com/blog/) uses this plugin and is all about just that, cyber intent. Here we will cover the art and science of it and the developments in the Counterespionage Firewall (CEF) portfolio (CEF for WordPress and CEF Full) that turn these ideas into reality.

Thank you for reading.

GS McNamara, Founder