<?php
/**
 * Copyright (c) 2020 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg
 * GPLv3, see docs/LICENSE
 */

include_once("./Services/COPage/classes/class.ilPageComponentPluginGUI.php");

/**
 * Page Component External Content plugin GUI
 *
 * @author Jesus Copado <jesus.copado@fau.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilPCExternalContentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_Calls ilPCExternalContentPluginGUI: ilPropertyFormGUI, ilExternalContentType, ilObjExternalContentGUI
 */
class ilPCExternalContentPluginGUI extends ilPageComponentPluginGUI
{

	var $obj_gui;
	var $template;

	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();

		switch ($next_class) {
			default:
				// perform valid commands
				$cmd = $ilCtrl->getCmd();
				if (in_array($cmd, array("create", "save", "edit", "update", "cancel"))) {
					$this->$cmd();
				}
				break;
		}
	}


	/**
	 * Create
	 *
	 * @param
	 * @return
	 */
	public function insert()
	{
		global $tpl;

		$this->setTabs("edit");
		$form = $this->initForm("create");
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Edit
	 *
	 * @param
	 * @return
	 */
	public function edit()
	{
		global $tpl;

		$this->setTabs("edit");
		$form = $this->initForm("edit");
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Save new pc input
	 */
	public function create()
	{
		global $tpl, $lng;

		$form = $this->initForm(true);
		if ($form->checkInput()) {
			$properties = array('title' => $form->getInput('title'), 'description' => $form->getInput('description'), 'type_id' => $form->getInput('type_id'), 'settings_id' => $form->getInput('settings_id'), 'obj_id' => (int) $_GET['obj_id']);
			if ($this->createElement($properties)) {

				ilPCExternalContentPlugin::createInDB($properties);

				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				$this->returnToParent();
			}
		}
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	 * Init editing form, Uses the Obj Plugin function
	 *
	 * @param int $a_mode Edit Mode
	 */
	protected function initForm($a_mode)
	{
		global $ilCtrl, $lng;

		//Ensure the gui class has been initialized
		if(!is_a($this->getObjGui(),'ilObjExternalContentGUI')){
			$this->initObjGUI("form");
		}

		//Get the extra properties
		$properties = $this->getProperties();
		$type = new ilExternalContentType($this->getObjGui()->object->getTypeId());
		foreach($type->getInputFields() as $field){
			if(isset($_POST["field_".$field->field_name])){
				$properties["field_".$field->field_name] = $_POST["field_".$field->field_name];
			}
		}

		//Set as local properties to work easier with them
		$this->setProperties($properties);

		//Get the form from the RepObjGUI
		if (empty($this->getProperties())) {
			$this->getObjGui()->initForm("create", $this->getProperties(), TRUE);
		} else {
			$this->getObjGui()->initForm("edit", $this->getProperties(), TRUE);
		}

		$form = $this->getObjGui()->getForm();

		//Add Save and Cancel command from here.
		if ($a_mode == "create")
		{
			$form->addCommandButton("create", $lng->txt("create"));
			$form->addCommandButton("cancel", $lng->txt("cancel"));
		} else
		{
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("cancel", $lng->txt("cancel"));
		}

		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}



	/**
	 * Get HTML for element
	 *
	 * @param string    page mode (edit, presentation, print, preview, offline)
	 * @return string   html code
	 */
	public function getElementHTML($a_mode, array $a_properties, $a_plugin_version)
	{
		$this->initObjGUI("view", $a_properties);

		switch ($this->getObjGui()->object->typedef->getLaunchType())
		{
			case ilExternalContentType::LAUNCH_TYPE_LINK:
				$this->getObjGui()->object->trackAccess();
				$this->getObjGui()->object->getLaunchLink();
				return $this->getLaunchLink($this->getObjGui()->object->getLaunchLink());
			case ilExternalContentType::LAUNCH_TYPE_PAGE:
				return $this->getObjGui()->object->getPageCode();
			case ilExternalContentType::LAUNCH_TYPE_EMBED:
				if ($_GET['lti_msg'])
				{
					ilUtil::sendInfo(ilUtil::stripSlashes($_GET['lti_msg']), true);
				}
				if ($_GET['lti_errormsg'])
				{
					ilUtil::sendFailure(ilUtil::stripSlashes($_GET['lti_errormsg']), true);
				}
				if($a_mode == "edit"){
					$tpl = $this->getPlugin()->getTemplate("tpl.content.html");
					$tpl->setVariable("MESSAGE",$this->getPlugin()->txt("external_content"));
					$tpl->setVariable("CONTENT",$this->getObjGui()->object->getEmbedCode());
					return $tpl->get();
				}else{
					return $this->getObjGui()->object->getEmbedCode();
				}
			default:
				break;
		}
	}

	/**
	 * Set tabs
	 *
	 * @param
	 * @return
	 */
	public function setTabs($a_active)
	{
		global $ilTabs, $ilCtrl;

		$pl = $this->getPlugin();

		$ilTabs->addTab("edit", $pl->txt("external_content"), $ilCtrl->getLinkTarget($this, "edit"));

		$ilTabs->activateTab($a_active);
	}

	/*
	 * Adapter methods for repository object to page component
	 */

	public function update()
	{
		global $tpl, $lng;

		$form = $this->initForm("edit");
		if ($form->checkInput()) {
			//Extra properties has been already loaded in initForm()
			$existing_properties = $this->getProperties();

			if(isset($existing_properties['obj_id']) AND isset($existing_properties['settings_id'])) {
				//Update the Page Component Object
				$pc_element = $this->updateElement($existing_properties);

				//Update the Rep. Obj. DB.
				$this->getObjGui()->object->setId((int) $existing_properties['obj_id']);
				$this->getObjGui()->object->setSettingsId((int) $existing_properties['settings_id']);
				foreach ($this->getObjGui()->object->typedef->getInputFields("object") as $field) {
					$value = trim($form->getInput("field_" . $field->field_name));
					$this->getObjGui()->object->saveFieldValue($field->field_name, $value ? $value : $field->default);
				}

				if ($pc_element) {
					ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				}
			}
		}

		$form->setValuesByPost();
		$this->setTabs("edit");
		$tpl->setContent($form->getHtml());
	}

	/*
	 * UTILITY METHODS
	 */

	/**
	 * Get a plugin text
	 * @param $a_var
	 * @return mixed
	 */
	protected function txt($a_var)
	{
		return $this->getPlugin()->txt($a_var);
	}

	/**
	 * @return ilObjExternalContentGUI
	 */
	public function getObjGui()
	{
		return $this->obj_gui;
	}

	/**
	 * @param ilObjExternalContentGUI $obj_gui
	 */
	public function setObjGui($obj_gui)
	{
		$this->obj_gui = $obj_gui;
	}

	public function initObjGUI($a_mode, $a_properties = array()){

		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilObjExternalContentGUI.php");
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilObjExternalContent.php");
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilExternalContentType.php");

		switch ($a_mode){
			case 'form':
				//Initialize the Object and GUI Class to use their methods
				$obj_gui = new ilObjExternalContentGUI();
				$obj = new ilObjExternalContent();
				$obj->setTypeId((int) $this->getProperties()['type_id']);
				$obj_gui->object = $obj;

				$this->setObjGui($obj_gui);
				break;
			case 'view':
				$obj_gui = new ilObjExternalContentGUI();
				$obj = new ilObjExternalContent();

				$obj->setId((int) $a_properties['obj_id']);
				$obj->setSettingsId((int) $a_properties['settings_id']);
				$type = new ilExternalContentType((int) $a_properties["type_id"]);
				$obj->typedef = $type;

				$obj->setContext(array('id' => $a_properties['obj_id'], 'title' => $a_properties['title'], 'type' => $a_properties['type_id']));
				$obj->setRefId($a_properties['obj_id']);
				$obj->setTitle($a_properties['title']);
				$obj->setDescription($a_properties['description']);

				$obj_gui->object = $obj;
				$this->setObjGui($obj_gui);
				break;
		}

	}

	/**
	 * @return mixed
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @param mixed $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	public function getLaunchLink($a_link_url){
		$toolbar = new ilToolbarGUI();
		$toolbar->setFormAction($a_link_url);

		$link = ilSubmitButton::getInstance();
		$link->setCaption($this->getObjGui()->getTitle(), FALSE);
		$toolbar->addButtonInstance($link);

		$toolbar->setFormName($this->lng->txt("Link_form_name"));

		return $toolbar->getHTML();
	}



}

?>