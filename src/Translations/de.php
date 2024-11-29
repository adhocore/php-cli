<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

return [
    // main
    'arguments'          => 'Argumente',
    'choice'             => 'Wahl',
    'choices'            => 'Wahlmöglichkeiten (durch Komma getrennt)',
    'command'            => 'Befehl',
    'commandNotFound'    => 'Befehl %s nicht gefunden',
    'commandSuggestion'  => 'Meinten Sie %s?',
    'commands'           => 'Befehle',
    'descWithDefault'    => '%s [Standard: %s]',
    'helpExample'        => '[OPTIONEN...] [ARGUMENTE...]',
    'optionHelp'         => 'Legende: <erforderlich> [optional] variadisch...',
    'options'            => 'Optionen',
    'promptInvalidValue' => 'Ungültiger Wert. Versuchen Sie es erneut!',
    'showHelp'           => 'Hilfe anzeigen',
    'showHelpFooter'     => 'Führen Sie `<Befehl> --help` für spezifische Hilfe aus',
    'showVersion'        => 'Version anzeigen',
    'stackTrace'         => 'Stack Trace',
    'thrownIn'           => 'geworfen in',
    'thrownAt'           => 'bei',
    'usage'              => 'Verwendung',
    'usageExamples'      => 'Verwendungsbeispiele',
    'version'            => 'Version',
    'verbosityLevel'     => 'Verbosity-Level',

    // exceptions
    'argumentVariadic'           => 'Nur das letzte Argument kann variadisch sein',
    'badProgram'                 => 'Das fehlerhafte Programm konnte nicht gestartet werden.',
    'commandAlreadyAdded'        => 'Befehl "%s" wurde bereits hinzugefügt',
    'commandDoesNotExist'        => 'Befehl "%s" existiert nicht',
    'configOptionMissing'        => 'Der Wert der Konfigurationsoption wird benötigt',
    'invalidTableRowsType'       => 'Zeilen müssen ein Array von assoziativen Arrays sein, %s gegeben',
    'optionNotRegistered'        => 'Option "%s" nicht registriert',
    'parameterAlreadyRegistered' => 'Der Parameter "%s" ist bereits registriert',
    'processAlreadyRun'          => 'Der Prozess läuft bereits.',
    'procOpenMissing'            => 'Die Funktion proc_open fehlt in Ihrer PHP-Konfiguration.',
    'progressbarCurrentMax'      => 'Der aktuelle (%d) ist größer als der Gesamtwert (%d).',
    'progressbarTotalMin'        => 'Der Gesamtwert der Fortschrittsanzeige muss größer als null sein.',
    'styleInvisible'             => 'Eingebaute Stile können nicht unsichtbar sein',
    'textRequired'               => 'Text erforderlich',
    'timeoutOccured'             => 'Zeitüberschreitung, Prozess beendet.',
    'undefinedStyle'             => 'Stil "%s" nicht definiert',
    'usingInvalidStyle'          => 'Versuch, einen leeren oder ungültigen Stil festzulegen'
];
