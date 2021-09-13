<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */

require_once(__DIR__ . '/class.ilPCExternalContentPlugin.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentSettings.php');

/**
 * Importer class for the PCExternalContent Plugin
 */
class ilPCExternalContentImporter extends ilPageComponentPluginImporter
{
    /** @var ilPCExternalContentPlugin */
    protected $plugin;


	public function init()
	{
	    $this->plugin = ilPCExternalContentPlugin::getInstance();
	}


	/**
	 * Import xml representation
	 *
	 * @param	string			$a_entity
	 * @param	string			$a_id
	 * @param	string			$a_xml
	 * @param	ilImportMapping	$a_mapping
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$new_id = self::getPCMapping($a_id, $a_mapping);

		$properties = self::getPCProperties($new_id);
		$version = self::getPCVersion($new_id);

        $message = '';
        $settings = new ilExternalContentSettings();
        if ($settings->setXML($a_xml, $message)) {
            $settings->save();
            $properties['settings_id'] = $settings->getSettingsId();
        }
        else {
            $properties['error'] = $this->plugin->txt('error_at_import') . ' ' . $message;
            $properties['settings_id'] = null;
        }

		self::setPCProperties($new_id, $properties);
		self::setPCVersion($new_id, $version);
	}
}