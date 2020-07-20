=== Counterespionage Firewall ===
Contributors: floodspark
Tags: espionage, recon, reconnaissance, intelligence, intel, security, defense, bots, fraud
Requires at least: 5.3.2
Tested up to: 5.4.2
Requires PHP: 7.0.33
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

The Floodspark Counterespionage Firewall (CEF) WordPress plugin helps you block reconnaissance or otherwise illegitimate traffic including hackers, bots, and scrapers.

== Description ==

Floodspark Counterespionage Firewall (CEF) helps you block reconnaissance or otherwise illegitimate traffic. CEF is like a web application firewall (WAF) but protects against reconnaissance. CEF focuses on pre-attack protection and is designed to complement security plugins such as Wordfence or Sucuri.

Using Intent Indicators, CEF can detect and protect earlier than an Indicators of Compromise (IoCs) or IP blacklist-based solution. Increase performance, reduce fraud, thwart attacks, and serve your real customers.

So far CEF for WordPress can detect and block:
* Tor browser, with minor delay
* Chrome Incognito, with minor delay, over HTTPS
* Firefox Private Browsing, with minor delay
* Chrome-Selenium in its default configuration, with minor delay
* cURL in its default configuration
* Wget in its default configuration
* HTTP methods other than GET, POST, and HEAD
* Proxy probing

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

== Changelog ==
 
= 1.0 =
* Initial public release

== Upgrade Notice ==
 
= 1.0 =
Initial public release

== Cyber Intent Blog ==
The [Floodspark Cyber Intent Blog](http://floodspark.com/blog/) uses this plugin and is all about just that, cyber intent. Here we will cover the art and science of it and the developments in the Counterespionage Firewall (CEF) portfolio (CEF for WordPress and CEF Full) that turn these ideas into reality.

Thank you for reading.

– GS, Founder
