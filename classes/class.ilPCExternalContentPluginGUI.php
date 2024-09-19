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
use ILIAS\Filesystem\Stream\Streams;

/**
 * External Content Page Component GUI
 *
 * @ilCtrl_isCalledBy ilPCExternalContentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_isCalledBy ilPCExternalContentPluginGUI: ilUIPluginRouterGUI
 */
class ilPCExternalContentPluginGUI extends ilPageComponentPluginGUI
{
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    /** @var ilPCExternalContentPlugin */
    protected ilPageComponentPlugin $plugin;

    public function __construct()
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

        parent::__construct();
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        // perform valid commands
        $cmd = $this->ctrl->getCmd();
        if (in_array($cmd, ["create", "save", "edit", "update", "cancel", "viewPage"])) {
            $this->$cmd();
        }
    }

    /**
     * Show the form add a new page content
     */
    public function insert(): void
    {
        $form = $this->initForm(true);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save the new page content
     */
    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            if ($this->http->wrapper()->post()->has('type_details')) {
                if ($this->saveForm($form, true)) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_created"), true);
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
    public function edit(): void
    {
        $form = $this->initForm(false);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show the form to update the page content
     */
    public function update()
    {
        $form = $this->initForm(false);
        if ($form->checkInput()) {
            if ($this->saveForm($form, false)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->returnToParent();
            }
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Init the properties form
     * @see ilObjExternalContentGUI::initForm()
     */
    protected function initForm(bool $a_create = false): ilPropertyFormGUI
    {
        // external content settings values
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
        } elseif ($this->http->wrapper()->post()->has('type_id')) {
            $type_id = $this->http->wrapper()->post()->retrieve('type_id', $this->refinery->kindlyTo()->int());
            if (!empty($type_id)) {
                // type is already chosen in first create step, content is not yet saved
                $type = new ilExternalContentType((int) $_POST['type_id']);
            }
        }

        $form = new ilPropertyFormGUI();

        if (!isset($type)) {
            // type has to be chosen first before anything is saved
            $item = new ilRadioGroupInputGUI($this->lng->txt('type'), 'type_id');
            $item->setRequired(true);
            $types = ilExternalContentType::_getTypesData(false, ilExternalContentType::AVAILABILITY_CREATE);
            foreach ($types as $type) {
                $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
                $item->addOption($option);
            }
            $form->addItem($item);
        } else {
            $item = new ilHiddenInputGUI('type_id');
            $item->setValue($type->getTypeId());
            $form->addItem($item);

            // content is saved if this item exists in the posted values
            $item = new ilNonEditableValueGUI($this->lng->txt('type'), 'type_details');
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
        } else {
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->txt('edit_input_field'));
            $form->setFormAction($this->ctrl->getFormAction($this, 'update'));
        }

        return $form;
    }

    /**
     * Save the entered form values
     */
    protected function saveForm(ilPropertyFormGUI $form, bool $a_create): bool
    {
        $properties = $this->getProperties();
        $properties['title'] = $form->getInput('title');
        $properties['description'] = $form->getInput('description');

        if ($a_create) {
            $settings = new ilExternalContentSettings();
            $settings->setTypeId($form->getInput('type_id'));
            $settings->setObjId($this->plugin->getParentId());
            foreach ($settings->getTypeDef()->getFormValues($form) as $field_name => $field_value) {
                $settings->setInputValue($field_name, $field_value);
            }
            $settings->save(); // this creates the settings id

            $properties['settings_id'] = $settings->getSettingsId();
            return $this->createElement($properties);
        } else {
            $settings = new ilExternalContentSettings($properties['settings_id']);
            $settings->setInputValues([]);
            foreach ($settings->getTypeDef()->getFormValues($form) as $field_name => $field_value) {
                $settings->setInputValue($field_name, $field_value);
            }
            $settings->save();

            return $this->updateElement($properties);
        }
    }

    /**
     * Cancel the editing
     */
    public function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * Get the HTML code of the page content
     *
     * @param string  $a_mode  page mode (edit, presentation, print, preview, offline)
     */
    public function getElementHTML(
        string $a_mode,
        array $a_properties,
        string $plugin_version
    ): string {
        $html = '';

        if (!empty($a_properties['title'])) {
            $html .= "<h3>" . $a_properties['title'] . "</h3>";
        }

        if (!empty($a_properties['settings_id'])) {
            $content = new ilPCExternalContent($this->plugin, $a_properties['settings_id']);
            $renderer = new ilExternalContentRenderer($content);
            $settings = $content->getSettings();

            switch ($settings->getTypeDef()->getLaunchType()) {
                case ilExternalContentType::LAUNCH_TYPE_LINK:
                    $html .= '<p><a href="' . $renderer->render() . '">' . $this->plugin->txt('launch_content') . '</a></p>';
                    break;

                case ilExternalContentType::LAUNCH_TYPE_PAGE:
                    $this->ctrl->saveParameterByClass('ilPCExternalContentPluginGUI', 'ref_id');
                    $this->ctrl->setParameterByClass('ilPCExternalContentPluginGUI', 'settings_id', $settings->getSettingsId());
                    $url = $this->ctrl->getLinkTargetByClass(['ilUIPluginRouterGUI', 'ilPCExternalContentPluginGUI'], 'viewPage');

                    $html .= '<p><a href="' . $url . '">' . $this->plugin->txt('launch_content') . '</a></p>';
                    break;

                case ilExternalContentType::LAUNCH_TYPE_EMBED:
                default:
                    $html .= $renderer->render();
                    break;
            }
        } elseif (!empty($a_properties['error'])) {
            // fallback if import failed with an error message
            $html .= '<p>' . $a_properties['error'] . '</p>';
        }

        if (!empty($a_properties['description'])) {
            // style taken from media object
            $html .= '<figcaption><strong>' . $a_properties['description'] . "</strong></figcaption>";
        }

        return $html;
    }

    /**
     * View the external content page (called for LAUNCH_TYPE_PAGE)
     */
    public function viewPage(): void
    {
        $settings_id = $this->http->wrapper()->query()->retrieve('settings_id', $this->refinery->kindlyTo()->int());
        $content = new ilPCExternalContent(ilPCExternalContentPlugin::getInstance(), $settings_id);
        $renderer = new ilExternalContentRenderer($content);
        $renderer->render();

        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($renderer->render())
        ));
        $this->http->sendResponse();
        $this->http->close();
    }
}
