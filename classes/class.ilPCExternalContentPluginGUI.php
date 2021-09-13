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
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentRenderer.php');
require_once(__DIR__ . '/class.ilPCExternalContent.php');
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
				if (in_array($cmd, array("create", "save", "edit", "update", "cancel", "viewPage")))
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

		if ($form->checkInput()) {
            if (!empty($_POST['type_details'])) {
                if ($this->saveForm($form, true)) {
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_created"), true);
                    $this->returnToParent();
                }
                $form->setValuesByPost();
            }
        }
		$this->tpl->setContent($form->getHtml());
	}
	
	/**
	 * Init the properties form and load the stored values
	 */
	public function edit()
	{
        $form = $this->initForm(false);
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Update
	 */
	public function update()
	{
		$form = $this->initForm(false);
		if ($form->checkInput()) {
		    if ($this->saveForm($form, false)) {
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->returnToParent();
            }
		}
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHtml());
	}

    /**
     * View Page
     */
    public function viewPage()
    {
        $properties = $this->getProperties();
        $content = new ilPCExternalContent($this->plugin, $properties['settings_id']);
        $renderer = new ilExternalContentRenderer($content);
        $renderer->render();
    }


	/**
	 * Init creation editing form
	 * @param  bool        $a_create        true: create component, false: edit component
     * @see \ilObjExternalContentGUI::initForm()
	 */
	protected function initForm($a_create = false)
	{
	    // settings values
        $values = [];

        // page content properties
        $properties = $this->getProperties();

        if (!empty($properties['settings_id'])) {
            // content is already saved, type and settings data exist

            $settings = new ilExternalContentSettings($properties['settings_id']);
            $type = $settings->getTypeDef();
            foreach ($settings->getInputValues() as $field_name => $field_value) {
                $values['field_' . $field_name] = $field_value;
            }
        }
        elseif(!empty($_POST['type_id'])) {
            // type is already chosen in first create step, content is not yet saved
            $type = new ilExternalContentType((int) $_POST['type_id']);
        }

	    $form = new ilPropertyFormGUI();

		if (!isset($type)) {
            // type has to be chosen first before anything is saved
            $item = new ilRadioGroupInputGUI($this->lng->txt('type'), 'type_id');
            $item->setRequired(true);
            $types = ilExternalContentType::_getTypesData(false, ilExternalContentType::AVAILABILITY_CREATE);
            foreach ($types as $type)
            {
                $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
                $item->addOption($option);
            }
            $form->addItem($item);
		}
		else {
		    $item = new ilHiddenInputGUI('type_id');
		    $item->setValue($type->getTypeId());
            $form->addItem($item);

            // content is saved if this item exists in the posted values
            $item = new ilNonEditableValueGUI($this->lng->txt('type'), 'type_details');
            //$item->setDisabled(true);
            $item->setValue($type->getTitle());
            $item->setInfo($type->getDescription());
            $form->addItem($item);

            // enter title and description after type is known
            $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
            $item->setSize(40);
            $item->setMaxLength(128);
            if (isset($properties['title'])) {
                $item->setValue($properties['title']);
            }
            $form->addItem($item);

            $item = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
            $item->setInfo($this->plugin->txt('description_info'));
            $item->setRows(2);
            if (isset($properties['description'])) {
                $item->setValue($properties['description']);
            }
            $form->addItem($item);

            // add the type specific form element
            $type->addFormElements($form, $values);
        }

		if ($a_create) {
            $this->addCreationButton($form);
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->txt('cmd_insert'));
            $form->setFormAction($this->ctrl->getFormAction($this, 'create'));
        }
		else {
			$form->addCommandButton("update", $this->lng->txt("save"));
			$form->addCommandButton("cancel", $this->lng->txt("cancel"));
			$form->setTitle($this->plugin->txt('edit_input_field'));
            $form->setFormAction($this->ctrl->getFormAction($this, 'update'));
		}

		return $form;
	}


    /**
     * Save the form values
     * @param ilPropertyFormGUI $form
     * @param bool $a_create
     * @return bool success
     */
	protected function saveForm($form, $a_create)
	{
        $properties = $this->getProperties();
        $properties['title'] = $form->getInput('title');
        $properties['description'] = $form->getInput('description');

        if ($a_create) {
            $exco_settings = new ilExternalContentSettings();
            $exco_settings->setTypeId($form->getInput('type_id'));
            $exco_settings->setObjId($this->plugin->getParentId());
            foreach ($exco_settings->getTypeDef()->getFormValues($form) as $field_name => $field_value) {
                $exco_settings->setInputValue($field_name, $field_value);
            }
            $exco_settings->save(); // this creates the settings id

            $properties['settings_id'] = $exco_settings->getSettingsId();
            return $this->createElement($properties);
        }
        else {
            $exco_settings = new ilExternalContentSettings($properties['settings_id']);
            $exco_settings->setInputValues([]);
            foreach ($exco_settings->getTypeDef()->getFormValues($form) as $field_name => $field_value) {
                $exco_settings->setInputValue($field_name, $field_value);
            }
            $exco_settings->save();

            return $this->updateElement($properties);
        }
	}


	/**
	 * Cancel
	 */
	public function cancel()
	{
		$this->returnToParent();
	}

    /**
     * setup css-style string for 'response'-iframe display
     */
    protected function getIFrameStyle() {
        return "<style type='text/css'>
                        .embed-container {
                        position: relative; 
                        padding-bottom: 56.25%; /* ratio 16x9 */
                        height: 0; 
                        overflow: hidden; 
                        width: 60%;
                        height: auto;
                        }
                        .embed-container iframe {
                        position: absolute; 
                        top: 0; 
                        left: 0; 
                        width: 100%; 
                        height: 100%;
                        }
                     </style>";
    }

	/**
	 * Get HTML for element
	 *
	 * @param string    page mode (edit, presentation, print, preview, offline)
	 * @return string   html code
	 */
	public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
	{
	    $content = new ilPCExternalContent($this->plugin, $a_properties['settings_id']);
        $renderer = new ilExternalContentRenderer($content);

	    $settings = $content->getSettings();
	    $title = $a_properties['title'];
	    $description = $a_properties['description'];

	    $html = '';

		if(!empty($title)) {
		    // todo: use accesible style
			$html .= "<h3>".$title."</h3>";
		}

	    switch ($settings->getTypeDef()->getLaunchType())
        {
            case ilExternalContentType::LAUNCH_TYPE_LINK:
                $html .= '<p><a href="' . $renderer->render() . '">' .  $this->plugin->txt('launch_content') . '</a></p>';
                break;

            case ilExternalContentType::LAUNCH_TYPE_PAGE:
                $this->ctrl->setParameterByClass('ilPCExternalContentGUI', 'settings_id', $settings->getSettingsId());
                $url = $this->ctrl->getLinkTargetByClass(['ilUIPluginRouterGUI', 'ilPCExternalContentGUI'], 'viewPage');

                $html .= '<p><a href="' . $url . ' target="_blank">' .  $this->plugin->txt('launch_content') . '</a></p>';
                break;

            case ilExternalContentType::LAUNCH_TYPE_EMBED:
            default:
                $html .= $renderer->render();
                break;
        }

		if(!empty($description)) {
		    // style taken from media object
        	$html .= '<figcaption><strong>'.$description."</strong></figcaption>";
		}

		return $html;
	}

}