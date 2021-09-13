<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentSettings.php');

/**
 * Exporter class for the PCExternalContent Plugin
 */
class ilPCExternalContentExporter extends ilPageComponentPluginExporter
{
	public function init()
	{
	}

	/**
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{
	    // nothing to do
		return array();
	}


	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		schema version
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		$properties = self::getPCProperties($a_id);
        if (isset($properties['settings_id'])) {
            $settings = new ilExternalContentSettings($properties['settings_id']);
            return $settings->getXML();
        }
        else {
            return '';
        }
	}

	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
	    // nothing to do
		return array();
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top. Example:
	 *
	 * 		return array (
	 *		"4.1.0" => array(
	 *			"namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
	 *			"xsd_file" => "ilias_md_4_1.xsd",
	 *			"min" => "4.1.0",
	 *			"max" => "")
	 *		);
	 *
	 *
	 * @return		array
	 */
	public function getValidSchemaVersions($a_entity)
	{
		return array(
			'5.3.0' => array(
				'namespace'    => 'http://www.ilias.de/',
				//'xsd_file'     => 'pctpc_5_3.xsd',
				'uses_dataset' => false,
				'min'          => '0.0.0',
				'max'          => '1.0.0'
			)
		);
	}
}