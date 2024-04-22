
Copyright (c) 2022 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
GPLv3, see LICENSE

**Further maintenance is provided by [Databay AG](https://www.databay.de).**

This plugin allows the use of external contents (e.g. via LTI 1.1) on ILIAS content pages.
NOTE: You must install the [ExternalContent](https://github.com/DatabayAG/ExternalContent) plugin before you can use this plugin.

# INSTALLATION

1. Put the content of the plugin directory in a subdirectory under your ILIAS main directory:
Customizing/global/plugins/Services/COPage/PageComponent/PCExternalContent
2. Run `composer du` in the main directory of your ILIAS installation
3. Go to Administration > Extending ILIAS > Plugins
4. Install and activate the plugin.

# Usage

* Configure at least one external content type in the ExternalContent plugin.
* Create a learning module, a content page or edit the content page of a course.
* Add an element to the page and chose "External Content".
* Select the content type that should be included.
* Fill out the form of the content type.
* View the presentation of the page.

Depending on the settings of the chosen external content type the page will present a link to the content or embed it directly.
