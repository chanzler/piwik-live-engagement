# Piwik Traffic Sources

## Description

This is a plugin for the Open Source Web Analytics platform Piwik. If enabled, it will add a new widget that you can add to your dashboard.

The widget will show you the actual traffic sources of a site. The widget auto-refreshes every x seconds. 

**This plugin should run fine with installations with up to 10.000.000 page impressions per day. If you run a very large piwik installation and have performance issues with this plugin, please contact me - there is a solution for this. I have it up and running in an installation with more than 15 million visits per day.**

(Tested with piwik 2.8.3, but supposed to run with older versions)

## Installation

Install it via Piwik Marketplace OR install manually:

1. Clone the plugin into the plugins directory of your Piwik installation.

   ```
   cd plugins/
   git clone https://github.com/chanzler/piwik-traffic-sources.git LiveEngagement
   ```

2. Login as superuser into your Piwik installation and activate the plugin under Settings -> Plugins

3. You will now find the widget under the Live! section.

## FAQ

###Features
Here is a list of features that are included in this project:

* New live widget that displays the actual traffic sources as bar charts

###Configuration
*Refresh interval*: Defines how often the widgets will be updated. Every 30 seconds is a good value to choose.

## Changelog

### 0.1.0 First Beta
* initial release

## License

GPL v3 or later

## Support

* Please direct any feedback to [frank@intersolve.de](mailto:frank@intersolve.de)

## Contribute

If you are interested in contributing to this plugin, feel free to send pull requests!

