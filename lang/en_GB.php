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
 * @subpackage i18n
 * @ignore
 */

i18n::include_locale_file('customhtmlform', 'en_US');

global $lang;

if (array_key_exists('en_GB', $lang) && is_array($lang['en_GB'])) {
    $lang['en_GB'] = array_merge($lang['en_US'], $lang['en_GB']);
} else {
    $lang['en_GB'] = $lang['en_US'];
}

$lang['en_GB']['Form']['FIELD_MUST_BE_EMPTY'] = 'This field must be empty.';
$lang['en_GB']['Form']['FIELD_MAY_NOT_BE_EMPTY'] = 'This field may not be empty.';
$lang['en_GB']['Form']['FIELD_MUST_BE_FILLED_IN'] = 'Please fill in this field.';
$lang['en_GB']['Form']['MIN_CHARS'] = 'Enter at least %s characters.';
$lang['en_GB']['Form']['FIED_REQUIRES_NR_OF_CHARS'] = 'This field requires exactly %s characters.';
$lang['en_GB']['Form']['REQUIRES_SAME_VALUE_AS_IN_FIELD'] = 'Please enter the same value as in field "%s".';
$lang['en_GB']['Form']['REQUIRES_OTHER_VALUE_AS_IN_FIELD'] = 'This field may not have the same value as field "%s".';
$lang['en_GB']['Form']['NUMBERS_ONLY'] = 'This field may consist of numbers only.';
$lang['en_GB']['Form']['CURRENCY_ONLY'] = 'Please enter a valid currency amount (e.g. 1499,00).';
$lang['en_GB']['Form']['DATE_ONLY'] = 'Please enter a valid german date (e.g. "dd.mm.yyyy").';
$lang['en_GB']['Form']['CAPTCHAFIELDNOMATCH'] = 'Your entry was not correct. Please try again!';
$lang['en_GB']['Form']['HASNOSPECIALSIGNS'] = 'This field must contain special signs (other signs than letters, numbers and the signs "@" and ".").';
$lang['en_GB']['Form']['HASSPECIALSIGNS'] = 'This field must not contain special signs (letters, numbers and the signs "@" and ".").';
$lang['en_GB']['Form']['MANDATORYFIELD'] = 'This field must be filled in.';
$lang['en_GB']['Form']['MUSTNOTBEEMAILADDRESS'] = "Please don't enter an email address.";
$lang['en_GB']['Form']['MUSTBEEMAILADDRESS'] = 'Please enter a valid email address.';
$lang['en_GB']['CustomHtmlFormStepPage']['BASE_NAME'] = 'base name for form object and template files: ';
$lang['en_GB']['CustomHtmlFormStepPage']['SHOW_CANCEL'] = 'show cancel link';
$lang['en_GB']['CustomHtmlFormStepPage']['CANCEL_TARGET'] = 'To which page should the cancel link direct: ';
$lang['en_GB']['CustomHtmlFormErrorMessages']['CHECK_FIELDS'] = 'Please check your input on the following fields:';