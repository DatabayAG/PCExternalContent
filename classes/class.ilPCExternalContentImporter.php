<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */


/**
 * Importer class for the PCExternalContent Plugin
 */
class ilPCExternalContentImporter extends ilPageComponentPluginImporter
{
	public function init()
	{
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

		// TODO: analyze the XMLm create settings, set the settings_id in the properties

		self::setPCProperties($new_id, $properties);
		self::setPCVersion($new_id, $version);
	}
}