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
 * page type that must be instanciated in the backend for a multi step form
 *
 * A base name (field "basename" for the form object and the template files of
 * the form must be defined
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormStepPage extends Page {

    /**
     * Definiert die Datenfelder.
     *
     * @var array
     */
    public static $db = array(
        'basename'          => 'Varchar(255)',
        'showCancelLink'    => 'Boolean(1)',
        'cancelPageID'      => 'Varchar(255)'
    );

    /**
     * list of URL from which the step form can be call without resetting the form
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 19.11.2010
     */
    public $allowedOutsideReferers = array(
        '/de/cgi-bin/webscr',
        '/auktion-erstellen/customHtmlFormSubmit',
        '/auktion-erstellen/uploadifyUpload',
        '/auktion-erstellen/uploadifyRefresh',
        '/auktion-erstellen/uploadifyRemoveFile',
        '/checkout/customHtmlFormSubmit'
    );
    
    /**
     * defines the CMS interface for $this
     * 
     * @return FieldSet
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getCMSFields() {

        $basenameField       = new TextField('basename', _t('CustomHtmlFormStepPage.BASE_NAME', 'base name for form object and template files: ', null, 'Basisname für Formular Objekt- und Templatedateien: '));
        $showCancelLinkField = new CheckboxField('showCancelLink', _t('CustomHtmlFormStepPage.SHOW_CANCEL', 'show cancel link'));
        $cancelLinkField     = new TreeDropdownField('cancelPageID', _t('CustomHtmlFormStepPage.CANCEL_TARGET', 'To which page should the cancel link direct: ', null, 'Auf welche Seite soll der Abbrechen-Link fuehren: '), 'SiteTree');

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $basenameField);
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $showCancelLinkField);
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $cancelLinkField);

        return $fields;
    }
}

/**
 * corresponding controller
 *
 * a base name (field "basename") must be specified
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormStepPage_Controller extends Page_Controller {

    /**
     * number of form objects; set by init()
     * 
     * @var integer
     */
    protected $nrOfSteps = -1;

    /**
     * Contains the list of steps as DataObjectSet.
     *
     * @var DataObjectSet
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 06.04.2011
     */
    protected $stepList;

    /**
     * step to be shown if no step is specified
     *
     * @var integer
     */
    protected $defaultStartStep = 1;
    
    /**
     * number of current step
     * 
     * @var integer
     */
    protected $currentStep;

    /**
     * Contains the current form instance.
     * 
     * @var CustomHtmlForm
     */
    protected $currentFormInstance;
    
    /**
     * preferences for the step form
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 17.11.2010
     */
    protected $basePreferences = array(
        // directory where to search for the templates
        'templateDir' => '' // Das Verzeichnis, in dem die Templates fuer die
                            // Formularreihe gesucht werden sollen
    );

    /**
     * Contains a list of all steps, their titles, visibility, etc.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 04.04.2011
     */
    protected $stepMapping = array();

    /**
     * Contains the output of a CustomHtmlForm object that was rendered by
     * this controller.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 06.04.2011
     */
    protected $initOutput = '';

    /**
     * initializes the step form
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function init() {
        if ($this->isStepPageCalledFromOutside()) {
            $this->deleteSessionData(false);
        }

        $this->initialiseSessionData();
        $this->generateStepMapping();

        $this->nrOfSteps            = $this->getNumberOfSteps();
        $this->currentFormInstance  = $this->registerCurrentFormStep();
        $this->initOutput           = $this->callMethodOnCurrentFormStep($this->currentFormInstance, 'init');
        $this->callMethodOnCurrentFormStep($this->currentFormInstance, 'process');
        
        parent::init();
    }

    /**
     * Returns the output of a form that was initialised by a
     * CustomHtmlFormStepPage object.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 06.04.2011
     */
    public function getInitOutput() {
        return $this->initOutput;
    }

    /**
     * Returns the current form instance object
     *
     * @return CustomHtmlForm
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 24.02.2011
     */
    public function getCurrentFormInstance() {
        return $this->currentFormInstance;
    }

    /**
     * returns the id of the current step
     *
     * @return int
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getCurrentStep() {
        return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'];
    }

    /**
     * returns the completed steps as a numeric array
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getCompletedSteps() {
        return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'];
    }

    /**
     * records a step to be completed
     *
     * @param int $stepNr id of the step; if not defined the current step will
     *                    be chosen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addCompletedStep($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }
        
        if (!$this->isStepCompleted($stepNr)) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'][] = $stepNr;
        }
    }

    /**
     * call to the parent method; the corresponding parameters will be set
     * Ruft die gleichnamige Methode der Elternseite auf und erstellt den
     * passenden Parameter.
     *
     * @param string $formIdentifier the forms unique id
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function InsertCustomHtmlForm($formIdentifier = null) {
        global $project;

        if ($formIdentifier === null) {
            $formIdentifier = $this->stepMapping[$this->getCurrentStep()]['class'];
        }

        $projectPrefix          = ucfirst($project);
        $extendedFormIdentifier = $projectPrefix.$formIdentifier;

        if (class_exists($extendedFormIdentifier)) {
            return parent::InsertCustomHtmlForm($extendedFormIdentifier);
        } else {
            return parent::InsertCustomHtmlForm($formIdentifier);
        }
    }

    /**
     * saves form data of the present step
     *
     * @param array $formData form data for this step
     * @param int   $stepNr   id of the step; if not defined the current step will
     *                        be chosen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function setStepData($formData, $stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr] = $formData;
    }

    /**
     * returns the data of the current step as an associative array;
     * if there is no data false will be returned
     *
     * @param int $stepNr id of the step; if not defined the current step will
     *                    be chosen
     *
     * @return array | boolean false
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getStepData($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        if (isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr])) {
            return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr];
        } else {
            return false;
        }
    }

    /**
     * returns all session data as an associative array
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getCombinedStepData() {

        $combinedData = array();

        for ($idx = $this->defaultStartStep; $idx < $this->getNumberOfSteps(); $idx++) {
            $stepData = $this->getStepData($idx);

            if (is_array($stepData)) {
                $combinedData = array_merge($combinedData, $stepData);
            }
        }

        return $combinedData;
    }

    /**
     * fills in the form fields with available session data
     * 
     * @param array &$fields Die zu befuellenden Felder
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function fillFormFields(&$fields) {

        $formSessionData    = $this->getStepData();
        $fieldIdx           = 0;

        foreach ($fields as $fieldName => $fieldData) {
            if (isset($formSessionData[$fieldName])) {
                if ($fieldData['type'] == 'OptionsetField' ||
                    $fieldData['type'] == 'DropdownField' ||
                    $fieldData['type'] == 'ListboxField' ||
                    in_array('OptionsetField', class_parents($fieldData['type'])) ||
                    in_array('DropdownField', class_parents($fieldData['type'])) ||
                    in_array('ListboxField', class_parents($fieldData['type']))) {
                    $valueParam = 'selectedValue';
                } else {
                    $valueParam = 'value';
                }
                
                $fields[$fieldName][$valueParam] = $formSessionData[$fieldName];
            }
        }
    }

    /**
     * returns the id of the previous step
     *
     * @return integer
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getPreviousStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep - 1;
    }

    /**
     * returns the id of the next step
     *
     * @return integer
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getNextStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep + 1;
    }

    /**
     * sets the id of the current step
     *
     * @param integer $stepNr id to be assigned
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function setCurrentStep($stepNr) {
        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'] = $stepNr;
    }

    /**
     * returns the link to the previous step
     *
     * @return string | boolean false
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkPrev() {
        $link = false;

        if ($this->getPreviousStep() > 0 &&
            $this->isStepCompleted($this->getPreviousStep()) ) {

            $link = $this->Link('PreviousStep');
        }

        return $link;
    }

    /**
     * returns the link to the next step
     *
     * @return string | boolean false
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkNext() {
        $link = false;

        if ($this->getNextStep() <= $this->getNumberOfSteps() &&
            $this->isStepCompleted()) {
            
            $link = $this->Link('NextStep');
        }

        return $link;
    }

    /**
     * returns the canel link
     *
     * @return string | boolean false
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkCancel() {
        $link = false;

        if ($this->showCancelLink) {
            $link = $this->Link('Cancel');
        }

        return $link;
    }

    /**
     * increments the present step and reloads page
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function NextStep() {
        if ($this->getNextStep() <= $this->getNumberOfSteps()) {
            $this->setCurrentStep($this->getNextStep());
        }
        Director::redirect($this->Link(), 302);
    }

    /**
     * decrements the current step an reloads the page
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function PreviousStep() {
        if ($this->getPreviousStep() > 0 &&
            $this->isStepCompleted($this->getPreviousStep()) ) {

            $this->setCurrentStep($this->getPreviousStep());
        }
        Director::redirect($this->Link(), 302);
    }

    /**
     * jumps to the defined step if it is compleated and relods the page
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 07.12.2010
     */
    public function GotoStep() {
        $stepNr = $this->urlParams['ID'];

        if ($this->isPreviousStepCompleted($stepNr)) {
            $this->setCurrentStep($stepNr);
        }
        
        Director::redirect($this->Link(), 302);
    }

    /**
     * cancels all form data an redirects to the first step
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function Cancel() {
        $this->setCurrentStep($this->defaultStartStep);
        $this->deleteSessionData(false);

        if ($this->cancelPageID) {
            $link = DataObject::get_by_id('Page', $this->cancelPageID)->Link();
        } else {
            $link = $this->Link();
        }

        if (!Director::redirected_to($link)) {
            Director::redirect($link, 302);
        }
    }

    /**
     *deletes all step data from session
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function deleteSessionData() {
        if (isset($_SESSION['CustomHtmlFormStep']) &&
            is_array($_SESSION['CustomHtmlFormStep'])) {

            if (isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID])) {
                unset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]);
            }
        }
    }

    /**
     * returns the defined steps title
     *
     * @param int $stepNr step index
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 29.11.2010
     */
    public function getStepName($stepNr) {
        $stepName = '';

        if (isset($this->stepMapping[$stepNr]) &&
            isset($this->stepMapping[$stepNr]['name'])) {
            $stepName = $this->stepMapping[$stepNr]['name'];
        }

        return $stepName;
    }

    /**
     * returns all steps as DataObjectSet
     *
     * @return DataObjectSet
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 29.11.2010
     */
    public function getStepList() {
        
        if (empty($this->stepList)) {
            $stepList           = array();
            $nrOfVisibleSteps   = 0;

            for ($stepIdx = 1; $stepIdx <= $this->getNumberOfSteps(); $stepIdx++) {

                if ($stepIdx == $this->getCurrentStep()) {
                    $isCurrentStep = true;
                } else {
                    $isCurrentStep = false;
                }

                if (isset($this->stepMapping[$stepIdx])) {
                    $stepClassName = $this->stepMapping[$stepIdx]['class'];

                    $stepList['step'.$stepIdx] = array(
                        'title'           => $this->stepMapping[$stepIdx]['name'],
                        'stepIsVisible'   => $this->stepMapping[$stepIdx]['visibility'],
                        'stepIsCompleted' => $this->isStepCompleted($stepIdx),
                        'isCurrentStep'   => $isCurrentStep,
                        'stepNr'          => $stepIdx,
                        'step'            => new $stepClassName($this, null, null ,false)
                    );
                    
                    if ($this->stepMapping[$stepIdx]['visibility']) {
                        $nrOfVisibleSteps++;
                    }
                }
            }
            
            // Set the number of visible steps and a tag for the last visible step
            foreach ($stepList as $stepNr => $stepListEntry) {
                if ($stepListEntry['stepIsVisible'] &&
                    ($stepListEntry['stepNr'] - 1) == $nrOfVisibleSteps) {
                    
                    $stepList[$stepNr]['isLastVisibleStep'] = true;
                } else {
                    $stepList[$stepNr]['isLastVisibleStep'] = false;
                }
            }
            
            $this->stepList = new DataObjectSet($stepList);
        }
        
        return $this->stepList;
    }

    /**
     * Is the current or defined step completed?
     *
     * @param bool $stepIdx Optional: index of step to be checked
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.12.2010
     */
    public function isStepCompleted($stepIdx = false) {
        
        $completed = false;

        if ($stepIdx === false) {
            $stepIdx = $this->getCurrentStep();
        }

        if (in_array($stepIdx, $this->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * has the previous step been completed?
     *
     * @param bool $stepIdx Optional: index of step to be checked
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 23.12.2010
     */
    public function isPreviousStepCompleted($stepIdx = false) {
        
        $completed = false;

        if ($stepIdx === false) {
            $stepIdx = $this->getCurrentStep() - 1;
        } else {
            $stepIdx -= 1;
        }

        if ($stepIdx === 0 ||
            in_array($stepIdx, $this->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * registers form for the current step
     *
     * @return Object
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function registerCurrentFormStep() {
        $formClassName = $this->stepMapping[$this->getCurrentStep()]['class'];
        $formInstance  = new $formClassName($this);
        $this->registerCustomHtmlForm($formClassName, $formInstance);
        
        return $formInstance;
    }

    /**
     * Calls a method on the given form instance.
     *
     * @param CustomHtmlForm $formInstance instance of current form
     * @param string         $methodName   The name of the method to call
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 16.11.2010
     */
    protected function callMethodOnCurrentFormStep($formInstance, $methodName) {
        $formClassName  = $this->stepMapping[$this->getCurrentStep()]['class'];
        $checkClass     = new ReflectionClass($formClassName);
        $output         = '';
        
        if ($checkClass->hasMethod($methodName)) {
            $output = $formInstance->$methodName();
        }
        
        return $output;
    }

    /**
     * stes the data structure for the CustomHtmlFormStep in the session
     *
     * $_SESSION
     *   CustomHtmlFormStep
     *     {PageClass}{PageID}
     *       currentStep    => Int          Default: 1
     *       completedSteps => array()      Default: empty
     *       steps          => array()      Default: empty
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function initialiseSessionData() {
        if (!isset($_SESSION['CustomHtmlFormStep'])) {
            $_SESSION['CustomHtmlFormStep'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'] = $this->defaultStartStep;
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'] = array();
        }
    }

    /**
     * returns the number of form steps
     * it will be determined like this:
     * - does a template with name scheme {basename}{step}.ss exist?
     * - does a class with name scheme {basename}{step}.php exist?
     * the steps get counted by a loop. if one of those two conditions not true
     * the loop will be aborted
     *
     * @return integer
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function getNumberOfSteps() {
        return count($this->stepMapping);
    }

    /**
     * Generates a map of all steps with links, names, etc.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 04.04.2011
     */
    public function generateStepMapping() {
        // --------------------------------------------------------------------
        // Get steps from theme- or moduledirectories
        // --------------------------------------------------------------------
        $this->getStepsFromModuleOrThemeDirectory();

        // --------------------------------------------------------------------
        // Get Steps from additional directories
        // --------------------------------------------------------------------
        $this->getStepsFromAdditionalDirectories();
    }

    /**
     * Clears the step mapping variable.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 04.04.2011
     */
    public function resetStepMapping() {
        $this->stepMapping = array();
        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'] = array();
    }

    /**
     * Fill class variable $stepMapping with steps from the module- or
     * themedirectory.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 04.04.2011
     */
    private function getStepsFromModuleOrThemeDirectory() {
        global $project;

        $themePath          = $this->getTemplateDir();
        $projectPrefix      = ucfirst($project);
        $increaseStep       = true;
        $stepIdx            = 1;
        $includedStepIdx    = 1;

        while ($increaseStep) {
            $includeStep            = true;
            $stepClassName          = $this->basename.$stepIdx;
            $extendedStepClassName  = $projectPrefix.$this->basename.$stepIdx;

            if (!Director::fileExists($themePath.$extendedStepClassName.'.ss') &&
                !Director::fileExists($themePath.$stepClassName.'.ss')) {
                $increaseStep = false;
                continue;
            }
            if (!class_exists($extendedStepClassName) &&
                !class_exists($stepClassName)) {
                $increaseStep = false;
                continue;
            }

            if (class_exists($extendedStepClassName)) {
                $stepClass = new $extendedStepClassName($this, null, null, true);
                $stepClassNameUnified = $extendedStepClassName;
            } else {
                $stepClass = new $stepClassName($this, null, null, true);
                $stepClassNameUnified = $stepClassName;
            }

            // Check if we have a conditional step and the condition is met
            if ($stepClass->getIsConditionalStep()) {
                if ($stepClass->hasMethod('isConditionForDisplayFulfilled')) {
                    if ($stepClass->isConditionForDisplayFulfilled() === false) {
                        $includeStep = false;
                    }
                }
            }

            if ($includeStep) {
                $this->stepMapping[$includedStepIdx] = array(
                    'name'        => $stepClass->getStepTitle(),
                    'class'       => $stepClassNameUnified,
                    'visibility'  => $stepClass->getStepIsVisible()
                );
                $includedStepIdx++;
            }
            
            $stepIdx++;
        }
    }

    /**
     * Fill class variable $stepMapping with steps from the additional
     * directories.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 04.04.2011
     */
    private function getStepsFromAdditionalDirectories() {
        $mappingIdx         = count($this->stepMapping);
        $stepIdx            = $mappingIdx + 1;
        $includedStepIdx    = $stepIdx;
        
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'])) {
            return false;
        }

        foreach ($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'] as $directory) {
            $prefix = $this->basename;
            
            if (is_array($directory)) {
                list($directory, $definition) = each($directory);

                if (isset($definition['prefix'])) {
                    $prefix = $definition['prefix'];
                }
            }
            
            // ----------------------------------------------------------------
            // Get steps from each directory
            // ----------------------------------------------------------------
            $increaseStep   = true;
            $moduleStepIdx  = 1;

            while ($increaseStep) {
                $includeStep   = true;
                $stepClassName = $prefix.$moduleStepIdx;

                if (!Director::fileExists($directory.$stepClassName.'.ss')) {
                    $increaseStep = false;
                    continue;
                }
                if (!class_exists($stepClassName)) {
                    $increaseStep = false;
                    continue;
                }

                $stepClass = new $stepClassName($this, null, null, true);

                if ($includeStep) {
                    $this->stepMapping[$includedStepIdx] = array(
                        'name'        => $stepClass->getStepTitle(),
                        'class'       => $stepClassName,
                        'visibility'  => $stepClass->getStepIsVisible()
                    );
                    $includedStepIdx++;
                }
                $moduleStepIdx++;
                $stepIdx++;
            }
        }
    }

    /**
     * Register an additional directory where CustomHtmlFormStepForms are
     * located.
     *
     * @param string $templateDirectory The directory where the additional
     *                                  StepForm templates are located.
     * 
     * @return boolean Indicates wether the given directory has been added
     *                 to the directory list. Returns also true, if the
     *                 directory has already been in the list.
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pxieltricks GmbH
     * @since 04.04.2011
     */
    public function registerStepDirectory($templateDirectory) {
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'] = array();
        }

        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['stepDirectories'][] = $templateDirectory;

        return true;
    }
    
    /**
     * if the template directory is defined via preferences it will be returned
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 17.11.2010
     */
    protected function getTemplateDir() {
        $templateDir = '';
        
        if (isset($this->preferences['templateDir']) &&
            !empty($this->preferences['templateDir'])) {
            $templateDir = $this->preferences['templateDir'];
        } else {
            $templateDir = THEMES_DIR.'/'.SSViewer::current_theme().'/templates/Layout/';
        }
        
        return $templateDir;
    }

    /**
     * Has the step page been called by itself or from the outside?
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 19.11.2010
     */
    protected function isStepPageCalledFromOutside() {
        $callFromOutside = true;
        $requestUri      = $_SERVER['REQUEST_URI'];

        if (isset($_SERVER['HTTP_REFERER'])) {
            $parsedRefererUrl = parse_url($_SERVER['HTTP_REFERER']);
            $refererUri       = $parsedRefererUrl['path'];

            if (strpos($requestUri, '?') !== false) {
                $requestUriElems = explode('?', $requestUri);
                $requestUri      = $requestUriElems[0];
            }

            if (substr($refererUri, -1) != '/') {
                $refererUri .= '/';
            }

            if ($refererUri === substr($requestUri, 0, strlen($refererUri))) {
                $callFromOutside = false;
            }

            // did a member of the whitelist make this call?
            // Pruefen, ob der Aufruf durch ein Whitelist-Mitglied durchgefuehrt
            // wurde.
            if ($callFromOutside) {
                foreach ($this->allowedOutsideReferers as $allowedOutsideReferer) {
                    $allowedRefererUrl = parse_url($allowedOutsideReferer);
                    $allowedRefererUri = $allowedRefererUrl['path'];

                    if (substr($refererUri, -1) == '/') {
                        if (substr($allowedRefererUri, -1) != '/') {
                            $allowedRefererUri .= '/';
                        }
                    }

                    if ($refererUri === substr($allowedRefererUri, 0, strlen($refererUri))) {
                        $callFromOutside = false;
                        break;
                    }
                }
            }
        } else {
            // Hack for Uploadify-Script!
            // The Uploadify-Script calls the flasplayer which does not send a referer
            // Dieses ruft durch den Flashplayer auf, der keinen Referer mitschickt.
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Adobe') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false) {
                $callFromOutside = false;
            } else {
                if (Director::isDev()) {
                    $callFromOutside = false;
                }
            }
        }

        return $callFromOutside;
    }
}
