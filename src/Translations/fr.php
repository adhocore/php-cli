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
    'arguments'          => 'Arguments',
    'choice'             => 'Choix',
    'choices'            => 'Choix (séparés par des virgules)',
    'command'            => 'Commande',
    'commandNotFound'    => 'Commande %s non trouvée',
    'commandSuggestion'  => 'Vouliez-vous dire %s ?',
    'commands'           => 'Commandes',
    'descWithDefault'    => '%s [par défaut : %s]',
    'helpExample'        => '[OPTIONS...] [ARGUMENTS...]',
    'optionHelp'         => 'Légende : <obligatoire> [optionnel] variadique...',
    'options'            => 'Options',
    'promptInvalidValue' => 'Valeur invalide. Essayez à nouveau!',
    'showHelp'           => 'Afficher l\'aide',
    'showHelpFooter'     => 'Exécutez `<commande> --help` pour de l\'aide spécifique',
    'showVersion'        => 'Afficher la version',
    'stackTrace'         => 'Trace de la pile',
    'thrownIn'           => 'levée dans',
    'thrownAt'           => 'à',
    'usage'              => 'Utilisation',
    'usageExamples'      => 'Exemples d\'utilisation',
    'version'            => 'version',
    'verbosityLevel'     => 'Niveau de verbosité',

    // exceptions
    'argumentVariadic'           => 'Seul le dernier argument peut être variadique',
    'badProgram'                 => 'Le programme défectueux n\'a pas pu être démarré.',
    'commandAlreadyAdded'        => 'La commande "%s" a déjà été ajoutée',
    'commandDoesNotExist'        => 'La commande "%s" n\'existe pas',
    'configOptionMissing'        => 'La valeur de l\'option de configuration est requise',
    'invalidTableRowsType'       => 'Les lignes doivent être un tableau de tableaux associatifs, %s donné',
    'optionNotRegistered'        => 'L\'option "%s" n\'est pas enregistrée',
    'parameterAlreadyRegistered' => 'Le paramètre "%s" est déjà enregistré',
    'processAlreadyRun'          => 'Le processus est déjà en cours.',
    'procOpenMissing'            => 'La fonction proc_open est manquante dans votre configuration PHP.',
    'progressbarCurrentMax'      => 'Le %d actuel est supérieur au total %d.',
    'progressbarTotalMin'        => 'Le total de la barre de progression doit être supérieur à zéro.',
    'styleInvisible'             => 'Les styles intégrés ne peuvent pas être invisibles',
    'textRequired'               => 'Texte requis',
    'timeoutOccured'             => 'Délai dépassé, processus interrompu.',
    'undefinedStyle'             => 'Style "%s" non défini',
    'usingInvalidStyle'          => 'Tentative de définir un style vide ou invalide'
];
