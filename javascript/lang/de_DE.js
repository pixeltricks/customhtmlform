if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
    //console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('de_DE', {
        'All.LOGINWRONGDATA':                       'Die eingegebenen Zugangsdaten sind nicht korrekt',
        'Form.FIELD_MUST_BE_EMPTY':                 'Dieses Feld muss leer sein.',
        'Form.FIELD_MAY_NOT_BE_EMPTY':              'Dieses Feld darf nicht leer sein.',
        'Form.FIELD_MUST_BE_FILLED_IN':             'Dieses Feld muss ausgefuellt sein.',
        'Form.MIN_CHARS':                           'Bitte gib mindestens %s Zeichen ein.',
        'Form.FIED_REQUIRES_NR_OF_CHARS':           'Dieses Feld erfordert %s Zeichen.',
        'Form.REQUIRES_SAME_VALUE_AS_IN_FIELD':     'Bitte gib den gleichen Wert ein wie im Feld "%s".',
        'Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD':    'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.',
        'Form.NUMBERS_ONLY':                        'Dieses Feld darf nur Zahlen enthalten.',
        'Form.CURRENCY_ONLY':                       'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.',
        'Form.DATE_ONLY':                           'In dieses Feld muss ein Datum im Format "tt.mm.jjjj" eingetragen werden.',
        'Form.HASNOSPECIALSIGNS':                   'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").',
        'Form.HASSPECIALSIGNS':                     'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.',
        'Form.MUSTNOTBEEMAILADDRESS':               'Bitte gib hier keine Email Adresse an.',
        'Form.MUSTBEEMAILADDRESS':                  'Bitte gib hier eine gültige Email Adresse an.'
    });
}
