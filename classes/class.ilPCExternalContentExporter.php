<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */

/**
 * Exporter class for the PCExternalContent Plugin
 */
class ilPCExternalContentExporter extends ilPageComponentPluginExporter
{
    public function init(): void
    {
    }

    public function getXmlExportHeadDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        // nothing to do
        return [];
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        $properties = self::getPCProperties($a_id);
        if (isset($properties['settings_id'])) {
            $settings = new ilExternalContentSettings($properties['settings_id']);
            return $settings->getXML();
        } else {
            return '';
        }
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        return [];
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            '5.3.0' => [                                    // schema version
                'namespace' => 'http://www.ilias.de/',
                //'xsd_file'     => 'pctpc_5_3.xsd',
                'uses_dataset' => false,
                'min' => '9.0.0',                           // min. ILIAS version
                'max' => '9.999.0'                          // max. ILIAS version
            ]
        ];
    }
}
