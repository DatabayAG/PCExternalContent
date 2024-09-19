<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Representation of the page content for the ExternalContent plugin
 * This object is delivered to ilExternalContentRenderer
 */
class ilPCExternalContent implements ilExternalContent
{
    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilPCExternalContentPlugin $plugin;
    protected ilExternalContentSettings $settings;

    /**
     * Array for context information, will be setup in getContext()
     * @var array{id: int, title: string, type: string}|null
     */
    protected ?array $context = null;

    public function __construct(ilPCExternalContentPlugin $plugin, int $settings_id)
    {
        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->settings = new ilExternalContentSettings($settings_id);
        $this->plugin = $plugin;
    }

    /**
     * Get the settings object
     */
    public function getSettings(): ilExternalContentSettings
    {
        return $this->settings;
    }

    /**
     * Get the object id of the parent object (learning module, content page)
     */
    public function getId(): int
    {
        return $this->plugin->getParentId();
    }

    /**
     * Get the reference id of the parent object (learning module, content page)
     */
    public function getRefId(): int
    {
        if ($this->http->wrapper()->query()->has('ref_id')) {
            return $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        }

        // take root as default, just in case. This is used to create a return URL for the tool
        return 1;
    }

    /**
     * Get the title of the parent object (learning module, content page)
     */
    public function getTitle(): string
    {
        return ilObject::_lookupTitle($this->getId());
    }

    /**
     * Get the description of the parent object (learning module, content page)
     */
    public function getDescription(): string
    {
        return ilObject::_lookupDescription($this->getId());
    }

    /**
     * Get the higher context (course or group of the parent object)
     * @return array{id: int, title: string, type: string}
     */
    public function getContext(): array
    {
        /** @see ilObjExternalContent::getContext() */
        $valid_types = array('crs', 'grp', 'cat', 'root');
        if ($this->context === null) {
            $this->context = [];
            $path = array_reverse($this->tree->getPathFull($this->getRefId()));
            foreach ($path as $key => $row) {
                if (in_array($row['type'], $valid_types)) {
                    if (in_array($row['type'], array('cat', 'root')) && !empty($this->context)) {
                        break;
                    }
                    $this->context['id'] = (int) $row['child'];
                    $this->context['title'] = $row['title'];
                    $this->context['type'] = $row['type'];
                }
            }
        }
        return $this->context;
    }

    /**
     * Get a suffix (e.g. 'autostart' provided with a goto link
     * not relevant for page content
     */
    public function getGotoSuffix(): string
    {
        return '';
    }

    /**
     * Get the goto link of the page
     * An external content opened in the same browser window will return to this page
     */
    public function getReturnUrl(): string
    {
        return ilLink::_getStaticLink($this->getRefId());
    }

    /**
     * Get the url for receiving results
     */
    public function getResultUrl(): string
    {
        // Page components don't have a learning progress
        // Therefore receiving results is not supported
        return '';
    }
}
