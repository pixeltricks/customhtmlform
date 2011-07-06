<?php
/**
 * Copyright 2010, 2011 pixeltricks GmbH
 *
 * This file is part of CustomHtmlForms.
 *
 * CustomHtmlForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CustomHtmlForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with CustomHtmlForms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomHtmlForm
 */

/**
 * Provides additional methods for Page.php used by the CustomHtmlForms module
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormPage_Controller extends DataObjectDecorator {

    /**
     * defines allowed methods
     *
     * defines the event to be jumped after form submission
     *
     * Hier wird das zentrale Event fuer die CustomHtmlForm definiert, das
     * nach dem Absenden eines Formulars angesprungen wird.
     *
     * @var array
     */
    public static $allowed_actions = array(
        'customHtmlFormSubmit',
        'uploadifyUpload',
        'uploadifyRefresh',
        'uploadifyRemoveFile'
    );

    /**
     * Contains all JS blocks to be added to the onload-event
     *
     * @var array
     */
    protected $JavascriptOnloadSnippets = array();

    /**
     * Contains all JS blocks NOT to be added to the onload-event
     *
     * @var array
     */
    protected $JavascriptSnippets = array();

    /**
     * contains a list of registerd custom html forms
     *
     * @var array
     */
    protected $registeredCustomHtmlForms = array();

    /**
     * adds a snippet to the list of JS onload events
     * Fuegt ein Snippet in die Liste der Javascript Onload-Events ein.
     *
     * @param string $snippet text block with JS statements
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptOnloadSnippet($snippet) {
        $this->JavascriptOnloadSnippets[] = $snippet;
    }

    /**
     * adds a snippet to the JS list to be added in the documents header
     *
     * @param string $snippet Textblock mit Javascript-Anweisungen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptSnippet($snippet) {
        $this->JavascriptSnippets[] = $snippet;
    }

    /**
     * registers a form object
     *
     * @param string         $formIdentifier unique form name which can be called via template
     * @param CustomHtmlForm $formObj        The form object with field definitions and preocessing methods
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function registerCustomHtmlForm($formIdentifier, CustomHtmlForm $formObj) {
        $this->registeredCustomHtmlForms[$formIdentifier] = $formObj;
    }

    /**
     * Returns the CustomHtmlForm object with the given identifier; if it's not
     * found a boolean false is returned.
     *
     * @param string $formIdentifier The identifier of the form
     *
     * @return mixed CustomHtmlForm|bool false
     */
    public function getRegisteredCustomHtmlForm($formIdentifier) {
        $formObj = false;

        if (isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            $formObj = $this->registeredCustomHtmlForms[$formIdentifier];
        }

        return $formObj;
    }

    /**
     * returns HTML markup for the requested form
     *
     * @param string $formIdentifier   unique form name which can be called via template
     * @param Object $renderWithObject object array; in those objects context the forms shall be created
     *
     * @return CustomHtmlForm
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function InsertCustomHtmlForm($formIdentifier, $renderWithObject = null) {

        if (!isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            throw new Exception(
                printf(
                    'The requested CustomHtmlForm "%s" is not registered.',
                    $formIdentifier
                )
            );
        }

        // Inject controller
        $customFields = array(
            'Controller' => $this->owner
        );

        if ($renderWithObject !== null) {
            if (is_array($renderWithObject)) {
                foreach ($renderWithObject as $renderWithSingleObject) {
                    if ($renderWithSingleObject instanceof DataObject) {
                        if (isset($customFields)) {
                            $customFields = array_merge($customFields, $renderWithSingleObject->getAllFields());
                        } else {
                            $customFields = $renderWithSingleObject->getAllFields();
                        }
                        unset($customFields['ClassName']);
                        unset($customFields['RecordClassName']);
                    }
                }
            } else {
                if ($renderWithObject instanceof DataObject) {
                    $customFields = $renderWithObject->getAllFields();
                    unset($customFields['ClassName']);
                    unset($customFields['RecordClassName']);
                }
            }
        }

        $outputForm = $this->registeredCustomHtmlForms[$formIdentifier]->customise($customFields)->renderWith(
            array(
                $this->registeredCustomHtmlForms[$formIdentifier]->class,
            )
        );

        return $outputForm;
    }

    /**
     * load some requirements first
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function onBeforeInit() {
        Validator::set_javascript_validation_handler('none');

        // -------------------------------------------------------------------
        // load scripts
        // -------------------------------------------------------------------
        Requirements::javascript('customhtmlform/script/jquery.js');
        Requirements::javascript('customhtmlform/script/jquery.scrollTo.min.js');
        Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.checkFormData.js');
        Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.events.js');
        Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.validator.js');
        Requirements::add_i18n_javascript('customhtmlform/javascript/lang');

        $this->owner->isFrontendPage = true;
    }

    /**
     * The onload and other javascript instructions are generated here.
     *
     * If you want a onload snippet to be loaded at the very end of the
     * definition you have to define it as array and provide the string
     * 'loadInTheEnd' as second parameter:
     *
     * $controller->addJavascriptOnloadSnippet(
     *     'var yourJavascriptSnippet;',
     *     'loadInTheEnd'
     * );
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function onAfterInit() {
        $onLoadSnippetStr           = '';
        $onLoadInTheEndSnippetStr   = '';
        $snippetStr                 = '';

        foreach ($this->JavascriptOnloadSnippets as $snippet) {
            if (is_array($snippet)) {
                if (isset($snippet[1]) &&
                    $snippet[1] == 'loadInTheEnd') {

                    $onLoadInTheEndSnippetStr .= $snippet[0];
                } else {
                    $onLoadSnippetStr .= $snippet[0];
                }
            } else {
                $onLoadSnippetStr .= $snippet;
            }
        }

        foreach ($this->JavascriptSnippets as $snippet) {
            $snippetStr .= $snippet;
        }

        if (!empty($snippetStr) ||
            !empty($onLoadSnippetStr)) {

            Requirements::customScript('

                '.$snippetStr.'

                $(document).ready(function() {
                    '.$onLoadSnippetStr.'
                    '.$onLoadInTheEndSnippetStr.'
                });
            ');
        }
    }

    /**
     * processor method for all customhtmlform forms
     *
     * @param Form $form the submitting form object
     *
     * @return mixed depends on processing form method
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function customHtmlFormSubmit($form) {
        $formName                    = $this->owner->request->postVar('CustomHtmlFormName');
        $registeredCustomHtmlFormObj = false;

        foreach ($this->registeredCustomHtmlForms as $registeredCustomHtmlForm) {
            if ($formName === $registeredCustomHtmlForm->name) {
                $registeredCustomHtmlFormObj = $registeredCustomHtmlForm;
                break;
            }

            foreach ($registeredCustomHtmlForm->registeredCustomHtmlForms as $customHtmlFormRegisteredCustomHtmlForm) {
                if ($formName === $customHtmlFormRegisteredCustomHtmlForm->name) {
                    $registeredCustomHtmlFormObj = $customHtmlFormRegisteredCustomHtmlForm;
                    break(2);
                }
            }
        }

        if ($registeredCustomHtmlFormObj instanceof CustomHtmlForm) {
            return $registeredCustomHtmlFormObj->submit($form, null);
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyUpload() {

        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            $result = $fieldReference->upload();
            return $result;
        } else {
            return -1;
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @param SS_HTTPRequest $request the request parameter
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyRefresh(SS_HTTPRequest $request) {
        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            return $fieldReference->refresh($request);
        } else {
            return -1;
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyRemoveFile() {
        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            return $fieldReference->removefile();
        } else {
            return -1;
        }
    }

    /**
     * Method Description
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    protected function getFieldObject() {
        $formIdentifier = 'CreateAuctionFormStep5';
        $fieldName      = 'UploadImages';
        $fieldReference = '';

        foreach ($this->registeredCustomHtmlForms as $registeredFormIdentifier => $registeredCustomHtmlForm) {
            if ($formIdentifier == $registeredFormIdentifier) {
                break;
            }
        }

        if ($registeredCustomHtmlForm instanceof CustomHtmlForm) {
            foreach ($registeredCustomHtmlForm->SSformFields['fields'] as $field) {
                if ($field instanceof MultipleImageUploadField) {
                    $fieldReference = $field;
                }
            }
        }

        return $fieldReference;
    }
}
