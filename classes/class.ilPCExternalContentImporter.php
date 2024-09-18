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
    protected ilPCExternalContentPlugin $plugin;

    public function init(): void
    {
        $this->plugin = ilPCExternalContentPlugin::getInstance();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $new_id = self::getPCMapping($a_id, $a_mapping);
        $properties = self::getPCProperties($new_id);
        $version = self::getPCVersion($new_id);

        $message = '';
        $settings = new ilExternalContentSettings();
        if ($settings->setXML($a_xml, $message)) {
            $settings->save();
            $properties['settings_id'] = $settings->getSettingsId();
        } else {
            $properties['error'] = $this->plugin->txt('error_at_import') . ' ' . $message;
            $properties['settings_id'] = null;
        }

        self::setPCProperties($new_id, $properties);
        self::setPCVersion($new_id, $version);
    }
}
