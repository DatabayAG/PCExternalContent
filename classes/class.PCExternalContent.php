<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/interface.ilExternalContent.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentSettings.php');
/**
 * Representation of the page content for the ExternalContent plugin
 * This object is delivered ilExternalContentRenderer
 */
class ilPCExternalContent implements ilExternalContent
{
    /** @var ilPCExternalContentPlugin */
    protected $plugin;

    /** @var ilExternalContentSettings */
    protected $settings;

    /** @var ilExternalContentSettings */
    protected $type;

    /** @var string */
    protected $return_url;

    /**
     * array for context information, will be setup in getContext()
     * @var array
     */
    protected $context = array();

    /**
     * ilPCExternalContent constructor
     * @param ilPCExternalContentPlugin $plugin;
     * @param int $settings_id
     */
    public function __construct($plugin, $settings_id)
    {
        // TODO: initialize the settings property base on the settings id X
        $this->settings = new ilExternalContentSettings($settings_id);
        $this->plugin = $plugin;
    }

    /**
     * Get the settings object
     * @return ilExternalContentSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get the object id of the parent object (learning module, content page)
     * @return int
     */
    public function getId()
    {
        return $this->plugin->getParentId();
    }


    /**
     * Get the reference id of the parent object (learning module, content page)
     * @return int
     */
    public function getRefId()
    {
        return $_GET['ref_id'];
    }

    /**
     * Get the title of the parent object (learning module, content page)
     * @return int
     */
    public function getTitle()
    {
        // TODO: Implement getTitle() method. Lookup via object id X
        $obj_id = $this->settings->getObjId();
        $type = new ilExternalContentType($obj_id);
        return $type->getTitle();
    }

    /**
     * Get the description of the parent object (learning module, content page)
     * @return int
     */
    public function getDescription()
    {
        // TODO: Implement getDescription() method. Lookup via object id X
        // doubled code? -> getTitle
        $obj_id = $this->settings->getObjId();
        $type = new ilExternalContentType($obj_id);
        return $type->getDescription();
    }

    /**
     * Get the higher context (course or group of the parent object)
     * @return array
     */
    public function getContext()
    {
        // TODO: Implement getContext() method. X
        /** @see ilObjExternalContent::getContext() */
        $valid_types = array('crs', 'grp', 'cat', 'root');
        global $DIC;
        $tree = $DIC->repositoryTree();
        if (!isset($this->context)) {
            $this->context = array();
            $path = array_reverse($tree->getPathFull($this->getRefId()));
            foreach ($path as $key => $row)
            {
                if (in_array($row['type'], $valid_types))
                {
                    if (in_array($row['type'], array('cat', 'root')) && !empty($this->context))
                        break;

                    $this->context['id'] = $row['child'];
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
     * @return string
     */
    public function getGotoSuffix()
    {
        return '';
    }

    /**
     * Get the goto link of the page
     * An external content opened in the same browser window will return to this page
     * @return string
     */
    public function getReturnUrl()
    {
        // TODO: Implement getReturnUrl() method. ???
        // don't know how to, do i need a setReturnUrl? inherit from ilObjExternalContent
    }

    /**
     * Get the url for receiving results
     * @return string
     */
    public function getResultUrl()
    {
        // Page components don't have an learning progress
        // Therefore receiving results is not supported
        return '';
    }
}