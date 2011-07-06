// Namensraum initialisieren und ggfs. vorhandenen verwenden
var pixeltricks         = pixeltricks       ? pixeltricks       : [];
    pixeltricks.forms   = pixeltricks.forms ? pixeltricks.forms : [];

/**
 * Methoden zum Binden von Events an Felder.
 *
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright pixeltricks GmbH
 * @since 11.11.2010
 * @license none
 */
pixeltricks.forms.events = function()
{
    /**
     * Workaround fuer Selbstreferenzierung in Closures.
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    var that = this;

    /**
     * Enthaelt den Name des Formulars.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.formName = '';

    /**
     * Enthaelt den Name des aktuellen Feldes.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.fieldName = '';

    /**
     * Enthaelt das Trennzeichen fuer HTML-IDs.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.nameSeparator = '';

    /**
     * Bindet ein onChange-Event an ein Referenzfeld, das die Werte eines
     * anderen Feldes aendert, wenn sich der Wert des Referenzfelds aendert.
     *
     * @param array definition Die Angaben zum Event:
     *      {
     *          // Die HTML-ID des Feldes, von dessen Wert der eigene Feldwert
     *          // gesetzt werden soll
     *          referenceFieldName: {
     *              referenceFieldHasValue1: {
     *                  // die folgenden key-value-Paare werden als <option>-
     *                  // Tags gesetzt:
     *                  key: value,
     *                  key: value,
     *                  ...
     *              },
     *              referenceFieldHasValue2: {
     *                  // die folgenden key-value-Paare werden als <option>-
     *                  // Tags gesetzt:
     *                  key: value,
     *                  key: value,
     *                  ...
     *              }
     *          }
     *      }
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.setValueDependantOn = function(definition)
    {
        var fieldId = that.formName + that.nameSeparator + that.fieldName;

        $.each(
            definition,
            function(referenceFieldName, mapping)
            {
                var referenceFieldId = that.formName + that.nameSeparator + referenceFieldName;
                
                $('#' + referenceFieldId).bind('change', {mapping: mapping, fieldId: fieldId}, function() {

                    var newValue = '';

                    if (mapping[this.value]) {
                        // Zugeordnetes Mapping anwenden
                        $.each(
                            mapping[this.value],
                            function(optionValue, optionTitle) {

                                if (optionValue === 'CustomHtmlFormEmptyValue') {
                                    optionValue = '';
                                }

                                newValue += '<option value="' + optionValue + '">' + optionTitle + '</option>';
                            }
                        );
                    } else {
                        // Es gibt kein zugeordnetes Mapping, also das Default-
                        // mapping verwenden, wenn vorhanden
                        if (mapping['default']) {
                            $.each(
                                mapping['default'],
                                function(optionValue, optionTitle) {

                                    if (optionValue === 'CustomHtmlFormEmptyValue') {
                                        optionValue = '';
                                    }

                                    newValue += '<option value="' + optionValue + '">' + optionTitle + '</option>';
                                }
                            );
                        }
                    }

                    $('#' + fieldId + ' option').remove();
                    $('#' + fieldId).append(newValue);
                    $('#' + fieldId).triggerHandler('change');
                });
            }
        );
    }

    /**
     * Bind a generic event and function call to this element.
     *
     * @param array definition The event instructions:
     *          'type' => The event type, e.g. 'onclick'
     *          'callFunction' => The function to call on event
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 13.03.2011
     */
    this.setEventHandler = function(definition) {
        var fieldId = that.formName + that.nameSeparator + that.fieldName;

        if (definition.type &&
            definition.callFunction) {
            $('#' + fieldId).bind(
                definition.type,
                eval(definition.callFunction)
            );
        }

    }

    /**
     * Bindet ein onChange-Event an ein Referenzfeld, das die Werte eines
     * anderen Feldes aendert, wenn sich der Wert des Referenzfelds aendert.
     *
     * @param string callbackFunctionName Die Funktion, die aufgerufen werden
     *        soll
     * @param array parameters Optionale Parameter
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.setCallback = function(callbackFunctionName) {
        var funcName        = callbackFunctionName[0];
        var funcParameters  = callbackFunctionName[1];
        var func            = eval(funcName);
        var fieldId         = that.formName + that.nameSeparator + that.fieldName;

        $('#' + fieldId).bind('change', {parameters: funcParameters, fieldId: fieldId, formName: that.formName, nameSeparator: that.nameSeparator}, func);
        
    }

    /**
     * Setzt den Namen des Formulars.
     *
     * @param string formName Der Name des Formulars
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.setFormName = function(formName)
    {
        that.formName = formName;
    }

    /**
     * Setzt den Namen des Trennzeichens fuer HTML IDs.
     *
     * @param string separator Das Trennzeichen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.setNameSeparator = function(separator)
    {
        that.nameSeparator = separator;
    }

    /**
     * Setzt den Namen des aktuellen Feldes.
     *
     * @param string fieldName Der Name des aktuellen Feldes.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    this.setFieldName = function(fieldName)
    {
        that.fieldName = fieldName;
    }
}