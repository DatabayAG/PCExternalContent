<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageComponentPluginImporter.php");

/**
 * Exporter class for the ilPCExternalContent Plugin
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
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
		/** @var ilPCExternalContentPlugin $plugin */
		$plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'COPage', 'pgcp', 'PCExternalContent');

		$new_id = self::getPCMapping($a_id, $a_mapping);

		$properties = self::getPCProperties($new_id);
		$version = self::getPCVersion($new_id);

		// save the data from the imported xml and write its id to the properties
		if ($additional_data_id = $properties['additional_data_id'])
		{
			$data = html_entity_decode(substr($a_xml, 6, -7));
			$id = $plugin->saveData($data);
			$properties['additional_data_id'] = $id;
		}

		self::setPCProperties($new_id, $properties);
		self::setPCVersion($new_id, $version);
	}
}