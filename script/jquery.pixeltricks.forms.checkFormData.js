// Namensraum initialisieren und ggfs. vorhandenen verwenden
var pixeltricks         = pixeltricks       ? pixeltricks       : [];
    pixeltricks.forms   = pixeltricks.forms ? pixeltricks.forms : [];

/**
 * Methoden zur Feldpruefung.
 */
pixeltricks.forms.checkFormData = function()
{
    /**
     * Workaround fuer Selbstreferenzierung in Closures.
     */
    var that = this;

    /**
     * Enthaelt den Feldwert.
     */
    this.fieldValue = '';

    /**
     * Enthaelt den Feldtyp.
     */
    this.fieldType = '';

    /**
     * Prueft, ob die Eingabe Sonderzeichen enthaelt und dieses Resultat dem
     * erwarteten Resultat entspricht.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.hasSpecialSigns = function(expectedResult) {
        var errorMessage    = '';
        var success         = false;
        var valueMatch      = false;

        var matches = this.fieldValue.match(/^[A-Za-z0-9@\.]+$/);

        if (matches && (matches[0] == this.fieldValue)) {
            valueMatch = true;
        }

        if (valueMatch == expectedResult) {
            success = true;
        } else {
            success = false;

            if (valueMatch) {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").';
                } else {
                    errorMessage = ss.i18n._t('Form.HASNOSPECIALSIGNS', 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").');
                }
            } else {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.';
                } else {
                    errorMessage = ss.i18n._t('Form.HASSPECIALSIGNS', 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.');
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    };

    /**
     * Checks, whether the given string matches basicly an email address.
     * The rule is: one or more chars, then '@', then two ore more chars, then
     * '.', then two or more chars. This matching was simplified because the 
     * stricter version did not match some special cases.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.isEmailAddress = function(expectedResult) {
        var errorMessage    = '';
        var success         = false;
        var valueMatch      = false;

        var matches = this.fieldValue.match(/.{1,}@.{2,}\..{2,}/);

        if (matches && (matches[0] == this.fieldValue)) {
            valueMatch = true;
        }

        if (valueMatch == expectedResult) {
            success = true;
        } else {
            success = false;

            if (valueMatch) {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = "Please don't enter an email address.";
                } else {
                    errorMessage = ss.i18n._t('Form.MUSTNOTBEEMAILADDRESS', "Please don't enter an email address.");
                }
            } else {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Please enter a valid email address.';
                } else {
                    errorMessage = ss.i18n._t('Form.MUSTBEEMAILADDRESS', 'Please enter a valid email address.');
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    };

    /**
     * Prueft, ob die Eingabe in ein Captchafield korrekt war.
     *
     * @return array
     */
    this.PtCaptchaInput = function(parameters) {

        var errorMessage    = '';
        var success         = true;

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob ein Feld leer ist und dieses Resultat dem erwarteten Resultat
     * entspricht.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.isFilledIn = function(expectedResult)
    {
        var errorMessage    = '';
        var isFilledIn      = true;
        var success         = false;

        if (this.fieldType == 'CheckboxField')
        {
            isFilledIn = this.fieldValue;
        }
        else if (this.fieldType == 'OptionsetField' ||
                 this.fieldType == 'SilvercartCheckoutOptionsetField' ||
                 this.fieldType == 'SilvercartAddressOptionsetField')
        {
            isFilledIn = this.fieldValue.length > 0 ? true : false;
        }
        else
        {
            var checkValue = this.getValueWithoutWhitespace(this.fieldValue);

            if (checkValue === '')
            {
                isFilledIn = false;
            }
        }

        if (isFilledIn === expectedResult)
        {
            success = true;
        }

        if (!success)
        {
            if (isFilledIn)
            {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld muss leer sein.';
                } else {
                    errorMessage = ss.i18n._t('Form.FIELD_MUST_BE_EMPTY', 'Dieses Feld muss leer sein.');
                }
            }
            else
            {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld darf nicht leer sein.';
                } else {
                    errorMessage = ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes leer ist; ob ein Fehler zurueckgegeben
     * wird, haengt davon ab, ob das als Abhaengigkeit gegebene Feld
     * ausgefuellt ist.
     *
     * @param array parameters
     */
    this.isFilledInDependantOn = function(parameters)
    {
        var errorMessage        = '';
        var isFilledInCorrectly = true;
        var checkValue          = this.getValueWithoutWhitespace(this.fieldValue);

        if (typeof parameters == 'object')
        {
            if (!parameters[0].field ||
                !parameters[0].hasValue)
            {
                // Fehlerbehandlung noch offen: serverseitig pruefen lassen
            }

            // Abfrage fuer Checkboxen
            if ($('input[@name=' + [parameters[0].field] + ']:checked').val() == parameters[0].hasValue)
            {
                if (checkValue.length == 0)
                {
                    isFilledInCorrectly = false;
                }
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nicht leer sein.';
        } else {
            errorMessage = ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
        }

        return {
            success:        isFilledInCorrectly,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob die Laenge des Wertes der angegebenen Mindestlaenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int minLength
     * @return array
     */
    this.hasMinLength = function(minLength)
    {
        var errorMessage    = '';
        var hasMinLength    = true;
        var checkValue      = this.getValueWithoutWhitespace(this.fieldValue);

        if (checkValue.length > 0 &&
            checkValue.length < minLength)
        {
            hasMinLength = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Bitte geben Sie mindestens ' + minLength + ' Zeichen ein.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.MIN_CHARS', 'Bitte geben Sie mindestens %s Zeichen ein.'), minLength);
        }

        return {
            success:        hasMinLength,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob die Laenge des Wertes der angegebenen Laenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int length
     * @return array
     */
    this.hasLength = function(length)
    {
        var errorMessage    = '';
        var hasLength       = true;
        var checkValue      = jQuery.trim(this.fieldValue);

        if (checkValue.length > 0 &&
            checkValue.length != length)
        {
            hasLength = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld erfordert ' + length + ' Zeichen.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.FIELD_REQUIRES_NR_OF_CHARS', 'Dieses Feld erfordert %s Zeichen.'), length);
        }

        return {
            success:        hasLength,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft ob der Wert eines Feldes dem Wert eines anderen Feldes
     * entspricht.
     *
     * @param array (
     *     'value'      => string: Wert den das Feld haben muss
     *     'fieldName'  => string: Name des anderen Feldes
     * )
     * @return array
     */
    this.mustEqual = function(parameters)
    {
        var errorMessage    = '';
        var isEqual         = true;

        if (this.fieldValue != parameters.value)
        {
            isEqual = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Bitte geben Sie den gleichen Wert ein wie im Feld "' + parameters.fieldName + '".';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.REQUIRES_SAME_VALUE_AS_IN_FIELD', 'Bitte geben Sie den gleichen Wert ein wie im Feld "%s".'), parameters.fieldName);
        }

        return {
            success     :   isEqual,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft ob der Wert eines Feldes dem Wert eines anderen Feldes
     * nicht entspricht.
     *
     * @param array (
     *     'value'      => string: Wert den das Feld nicht haben darf
     *     'fieldName'  => string: Name des anderen Feldes
     * )
     * @return array
     */
    this.mustNotEqual = function(parameters)
    {
        var errorMessage    = '';
        var isNotEqual      = true;

        if (this.fieldValue == parameters.value)
        {
            isNotEqual = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nicht den gleichen Wert wie das Feld "' + parameters.fieldName + '" haben.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD', 'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.'), parameters.fieldName);
        }

        return {
            success     :   isNotEqual,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob ein Feld ausschliesslich aus Zahlen besteht.
     *
     * @param boolean
     * @return array
     */
    this.isNumbersOnly = function(expectedResult)
    {
        var errorMessage            = '';
        var consistsOfNumbersOnly   = true;
        var success                 = false;
        var checkValue              = that.fieldValue.replace(/[0-9]*/g, '');

        if (checkValue.length > 0)
        {
            consistsOfNumbersOnly = false;
        }

        if (consistsOfNumbersOnly == expectedResult)
        {
            success = true;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nur Zahlen enthalten.';
        } else {
            errorMessage = ss.i18n._t('Form.NUMBERS_ONLY', 'Dieses Feld darf nur Zahlen enthalten.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Waehrungsangabe entspricht.
     *
     * @param mixed expectedResult
     * @return array
     */
    this.isCurrency = function(expectedResult)
    {
        var errorMessage    = '';
        var success         = expectedResult;

        if (that.fieldValue.length > 0)
        {
            var nrOfMatches = that.fieldValue.search(
                /^[\d]{1,}[,]?[\d]{0,2}$/
            );

            if (nrOfMatches === -1)
            {
                success = false;
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.';
        } else {
            errorMessage = ss.i18n._t('Form.CURRENCY_ONLY', 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Datumsangabe entspricht.
     *
     * @param mixed expectedResult
     * @return array
     */
    this.isDate = function(expectedResult)
    {
        var errorMessage    = '';
        var success         = expectedResult;

        if (that.fieldValue.length > 0)
        {
            var nrOfMatches = that.fieldValue.search(
                /^[\d]{2}[\.]{1}[\d]{2}[\.]{1}[\d]{4}$/
            );

            if (nrOfMatches === -1)
            {
                success = false;
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'In dieses Feld muss ein Datum (z.B. "31.01.2010") eingetragen werden.';
        } else {
            errorMessage = ss.i18n._t('Form.DATE_ONLY', 'In dieses Feld muss ein Datum (z.B. "31.01.2010") eingetragen werden.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Entfernt alle Whitespaces aus dem uebergebenen Wert und gibt das
     * Ergebnis zurueck.
     *
     * @param string value
     * @return string
     */
    this.getValueWithoutWhitespace = function(value)
    {
        if (value)
        {
            return value.replace(/[\s]*/g, '');
        }
        else
        {
            return '';
        }
    }

    /**
     * Setzt den Wert des Feldes.
     *
     * @param Mixed fieldValue
     */
    this.setFieldValue = function(fieldValue)
    {
        this.fieldValue = fieldValue;
    }

    /**
     * Setzt den Typ des Felds.
     *
     * @param string fieldType
     */
    this.setFieldType = function(fieldType)
    {
        this.fieldType = fieldType;
    }
}
