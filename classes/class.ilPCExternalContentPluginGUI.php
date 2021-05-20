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
 * External Content Page Component GUI
 *
 * @ilCtrl_isCalledBy ilPCExternalContentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_isCalledBy ilPCExternalContentPluginGUI: ilUIPluginRouterGUI
 */
class ilPCExternalContentPluginGUI extends ilPageComponentPluginGUI
{
	/** @var  ilCtrl $ctrl */
	protected $ctrl;

	/** @var  ilTemplate $tpl */
	protected $tpl;

	/** @var ilPCExternalContentPlugin */
	protected $plugin;

	/**
	 * ilPCExternalContentPluginGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

		parent::__construct();
	}


	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		switch($next_class)
		{
			default:
				// perform valid commands
				$cmd = $this->ctrl->getCmd();
				if (in_array($cmd, array("create", "save", "edit", "update", "cancel", "downloadFile")))
				{
					$this->$cmd();
				}
				break;
		}
	}
	
	
	/**
	 * Create
	 */
	public function insert()
	{
		$form = $this->initForm(true);
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Save new pc example element
	 */
	public function create()
	{
		$form = $this->initForm(true);
		if ($this->saveForm($form, true))
		{
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->returnToParent();
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}
	
	/**
	 * Init the properties form and load the stored values
	 */
	public function edit()
	{
        $form = $this->initForm();


		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Update
	 */
	public function update()
	{
		$form = $this->initForm(false);
		if ($this->saveForm($form, false))
		{
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->returnToParent();
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}


	/**
	 * Init creation editing form
	 * @param  bool        $a_create        true: create component, false: edit component
	 */
	protected function initForm($a_create = false)
	{
		$form = new ilPropertyFormGUI();

		// save and cancel commands
		if ($a_create)
		{
		    // TODO: add here the selection of the type
            /** @see \ilObjExternalContentGUI::initForm() */

			$this->addCreationButton($form);
			$form->addCommandButton("cancel", $this->lng->txt("cancel"));
			$form->setTitle($this->plugin->getPluginName());
		}
		else
		{
		    // TODO: add here the form elements for title, description and the type
            // TODO: leave out 'online' checkbox
            // TODO: add the type specific form elements
            /** @see \ilObjExternalContentGUI::initForm() */

			$form->addCommandButton("update", $this->lng->txt("save"));
			$form->addCommandButton("cancel", $this->lng->txt("cancel"));
			$form->setTitle($this->plugin->getPluginName());
		}
		
		$form->setFormAction($this->ctrl->getFormAction($this));
		return $form;
	}

    /**
     * Load the form values for editing
     * @param ilPropertyFormGUI $form
     */
	protected function loadForm($form)
    {
        $properties = $this->getProperties();
	    // TODO: get title and description from the properties
        // TODO: get an ilExternalContentSettings object from the id in the properties
        // TODO: get the input values from the settings
        /** @see ilObjExternalContentGUI::loadFormValues() */
    }

    /**
     * Save the form values
     * @param ilPropertyFormGUI $form
     * @param bool $a_create
     * @return bool success
     */
	protected function saveForm($form, $a_create)
	{
		if ($form->checkInput())
		{
			$properties = $this->getProperties();

            // TODO: set the title and description directly from the form in the properties
            // TODO: get an ilExternalContentSettings object from the id in the properties (create and save it if the id is empty)
            // TODO: save the form input into the settings
            /** @see ilObjExternalContentGUI::saveFormValues() */


			if ($a_create)
			{
				return $this->createElement($properties);
			}
			else
			{
				return $this->updateElement($properties);
			}
		}

		return false;
	}


	/**
	 * Cancel
	 */
	public function cancel()
	{
		$this->returnToParent();
	}


	/**
	 * Get HTML for element
	 *
	 * @param string    page mode (edit, presentation, print, preview, offline)
	 * @return string   html code
	 */
	public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
	{
	    require_once (__DIR__ . '/class.PCExternalContent.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentRenderer.php');

	    $content = new ilPCExternalContent($this->plugin, $a_properties['settings_id']);
        $renderer = new ilExternalContentRenderer($content);

	    $settings = $content->getSettings();
	    switch ($settings->getTypeDef()->getLaunchType())
        {
            case ilExternalContentType::LAUNCH_TYPE_LINK:
                $html = '<a href="' . $renderer->render() . '">' .  $this->plugin->txt('launch_content') . '</a>';
                break;

            case ilExternalContentType::LAUNCH_TYPE_PAGE:
                // TODO: create link to a new page that renders the content
                break;

            case ilExternalContentType::LAUNCH_TYPE_EMBED:
            default:
                $html = $renderer->render();
                break;
        }

        // TODO: add title and description from the properties to the html

		return $html;
	}

}