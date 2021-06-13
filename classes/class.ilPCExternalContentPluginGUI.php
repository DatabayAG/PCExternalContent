<?php
/**
 * Copyright (c) 2021 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 * @author Cornel Musielak <cornel.musielak@fau.de>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentType.php');

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
		    // TODO: add here the selection of the type X
            /** @see \ilObjExternalContentGUI::initForm() */

            $item = new ilRadioGroupInputGUI($this->lng->txt('type'), 'type_id');
            $item->setRequired(true);
            $types = ilExternalContentType::_getTypesData(false, ilExternalContentType::AVAILABILITY_CREATE);
            foreach ($types as $type)
            {
                $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
                $item->addOption($option);
            }
            $form->addItem($item);

			$this->addCreationButton($form);
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
		}
		else
		{
		    // TODO: add here the form elements for title, description and the type X
            // TODO: leave out 'online' checkbox X
            // TODO: add the type specific form elements X
            /** @see \ilObjExternalContentGUI::initForm() */

            $item = new ilNonEditableValueGUI($this->lng->txt('type'), '');
            $item->setValue($this->object->getTypeDef()->getTitle());
            $item->setInfo($this->object->getTypeDef()->getDescription());
            $this->form->addItem($item);

            $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
            $item->setSize(40);
            $item->setMaxLength(128);
            $item->setRequired(true);
            //$item->setInfo($this->txt('xxco_title_info'));
            //$item->setValue($a_values['title']);
            $this->form->addItem($item);

            $item = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
            $item->setInfo($this->txt('xxco_description_info'));
            $item->setRows(2);
            //$item->setValue($a_values['description']);
            $this->form->addItem($item);

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
	    // TODO: get title and description from the properties X
        // TODO: get an ilExternalContentSettings object from the id in the properties X
        // TODO: get the input values from the settings X
        /** @see ilObjExternalContentGUI::loadFormValues() */
        $title = $properties['title'];
        $description = $properties['description'];

        $exco_settings = new ilExternalContentSettings($properties['settings_id']);

        $form->setTitle($title);
        $form->setDescription($description);
        $form->setValuesByArray($exco_settings->getInputValues());
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

            // TODO: set the title and description directly from the form in the properties X
            // TODO: get an ilExternalContentSettings object from the id in the properties (create and save it if the id is empty) X
            // TODO: save the form input into the settings X
            /** @see ilObjExternalContentGUI::saveFormValues() */

            $properties['title'] = $form->getTitle();
            $properties['descrption'] = $form->getDescription();
            if(!empty($properties['settings_id'])) {
                $exco_settings = new ilExternalContentSettings($properties['settings_id']);
            }
            else {
                $exco_settings = new ilExternalContentSettings();
				//CM TODO built up settings without settings-id? how?
                //$exco_settings->
                $exco_settings->save();
                $properties['settings_id'] = $exco_settings->getSettingsId();
            }

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
				//  !!at the moment only copied from above. don't know if it is correct
                $html = '<a href="' . $renderer->render() . ' target="_blank">' .  $this->plugin->txt('launch_content') . '</a>';
                break;

            case ilExternalContentType::LAUNCH_TYPE_EMBED:
            default:
                $html = $renderer->render();
                break;
        }

        //CM TODO: add title and description from the properties to the html ???
        //$a_properties['title'];
	    //$a_properties['description'];

		return $html;
	}

}