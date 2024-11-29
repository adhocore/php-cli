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
    'arguments'          => 'Аргументы',
    'choice'             => 'Выбор',
    'choices'            => 'Варианты (через запятую)',
    'command'            => 'Команда',
    'commandNotFound'    => 'Команда %s не найдена',
    'commandSuggestion'  => 'Вы имели в виду %s?',
    'commands'           => 'Команды',
    'descWithDefault'    => '%s [по умолчанию: %s]',
    'helpExample'        => '[ОПЦИИ...] [АРГУМЕНТЫ...]',
    'optionHelp'         => 'Легенда: <обязательно> [необязательно] вариативные...',
    'options'            => 'Опции',
    'promptInvalidValue' => 'Неверное значение. Попробуйте снова!',
    'showHelp'           => 'Показать помощь',
    'showHelpFooter'     => 'Запустите `<команда> --help` для получения конкретной помощи',
    'showVersion'        => 'Показать версию',
    'stackTrace'         => 'Трассировка стека',
    'thrownIn'           => 'выброшено в',
    'thrownAt'           => 'Ha',
    'usage'              => 'Использование',
    'usageExamples'      => 'Примеры использования',
    'version'            => 'версия',
    'verbosityLevel'     => 'Уровень подробности',

    // exception
    'argumentVariadic'           => 'Только последний аргумент может быть вариативным',
    'badProgram'                 => 'Не удалось запустить поврежденную программу.',
    'commandAlreadyAdded'        => 'Команда "%s" уже добавлена',
    'commandDoesNotExist'        => 'Команда "%s" не существует',
    'configOptionMissing'        => 'Необходимо указать значение опции конфигурации',
    'invalidTableRowsType'       => 'Строки должны быть массивом ассоциативных массивов, %s передано',
    'optionNotRegistered'        => 'Опция "%s" не зарегистрирована',
    'parameterAlreadyRegistered' => 'Параметр "%s" уже зарегистрирован',
    'processAlreadyRun'          => 'Процесс уже выполняется.',
    'procOpenMissing'            => 'Функция proc_open отсутствует в вашей конфигурации PHP.',
    'progressbarCurrentMax'      => 'Текущий %d больше общего %d.',
    'progressbarTotalMin'        => 'Общий прогресс должен быть больше нуля.',
    'styleInvisible'             => 'Встроенные стили не могут быть невидимыми',
    'textRequired'               => 'Необходим текст',
    'timeoutOccured'             => 'Превышено время ожидания, процесс завершен.',
    'undefinedStyle'             => 'Стиль "%s" не определен',
    'usingInvalidStyle'          => 'Попытка установить пустой или недопустимый стиль'
];
