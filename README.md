## adhocore/cli

Framework agnostic Command Line Interface utilities and helpers for PHP. Build Console App with ease, fun and love.

[![Latest Version](https://img.shields.io/github/release/adhocore/cli.svg?style=flat-square)](https://github.com/adhocore/cli/releases)
[![Travis Build](https://travis-ci.com/adhocore/cli.svg?branch=master)](https://travis-ci.com/adhocore/cli?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/cli.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/cli/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/cli/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/cli)
[![StyleCI](https://styleci.io/repos/139012552/shield)](https://styleci.io/repos/139012552)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

- Command line application made easy
- Inspired by nodejs [commander](https://github.com/tj/commander.js) (thanks tj)
- For PHP7 and for good

[![Screen Preview](https://i.imgur.com/qIYg9Zn.gif "Preview from adhocore/phalcon-ext which uses this cli package")](https://github.com/adhocore/phalcon-ext/tree/master/example/cli)

#### What's included

**Core**

- [Argv parser](#as-argv-parser)
- [Cli application](#as-console-app)

**IO**

- [Colorizer](#color)
- [Cursor manipulator](#cursor)
- [Stream writer](#writer)
- [Stream reader](#reader)

## Installation
```bash
composer require adhocore/cli
```

## Usage

### As argv parser

```php
$command = new Ahc\Cli\Input\Command('rmdir', 'Remove dirs');

$command
    ->version('0.0.1-dev')
    // Arguments are separated by space
    // Format: `<name>` for required, `[name]` for optional
    //  `[name:default]` for default value, `[name...]` for variadic (last argument)
    ->arguments('<dir> [dirs...]')
    // `-h --help`, `-V --version`, `-v --verbosity` options are already added by default.
    // Format: `<name>` for required, `[name]` for optional
    ->option('-s --with-subdir', 'Also delete subdirs (`with` means false by default)')
    ->option('-e,--no-empty', 'Delete empty (`no` means true by default)')
    // Specify santitizer/callback as 3rd param, default value as 4th param
    ->option('-d|--depth [nestlevel]', 'How deep to process subdirs', 'intval', 5)
    ->parse(['thisfile.php', '-sev', 'dir', 'dir1', 'dir2', '-vv']) // `$_SERVER['argv']`
;

// Print all values:
print_r($command->values());

/*Array
(
    [help] =>
    [version] => 0.0.1
    [verbosity] => 3
    [dir] => dir
    [dirs] => Array
        (
            [0] => dir1
            [1] => dir2
        )

    [subdir] => true
    [empty] => false
    [depth] => 5
)*/

// Pick a value by name
$command->dir;   // dir
$command->dirs;  // [dir1, dir2]
$command->depth; // 5
```

#### Command help

It can be triggered manually with `$command->showHelp()` or automatic when `-h` or `--help` option is passed to `$command->parse()`.

For above example, the output would be:
![Command Help](./sc/command-help.png "Command Help")

#### Command version

It can be triggered manually with `$command->showVersion()` or automatic when `-V` or `--version` option is passed to `$command->parse()`.

For above example, the output would be:
```
0.0.1-dev
```

### As console app

We simulate a `git` app with limited functionality of `add`, and `checkout`.
You will see how intuitive, fluent and cheese building a console app is!

#### Git app

```php
$app = new Ahc\Cli\Application('git', '0.0.1');

$app
    // Register `add` command
    ->command('add', 'Stage changed files', 'a') // alias a
        // Set options and arguments for this command
        ->arguments('<path> [paths...]')
        ->option('-f --force', 'Force add ignored file', 'boolval', false)
        ->option('-N --intent-to-add', 'Add content later but index now', 'boolval', false)
        // Handler for this command: param names should match but order can be anything :)
        ->action(function ($path, $paths, $force, $intentToAdd) {
            array_unshift($paths, $path);

            echo ($intentToAdd ? 'Intent to add ' : 'Add ')
                . implode(', ', $paths)
                . ($force ? ' with force' : '');
        })
        // Done setting up this command for now, tap() to retreat back so we can add another command
        ->tap()
    ->command('checkout', 'Switch branches', 'co') // alias co
        ->arguments('<branch>')
        ->option('-b --new-branch', 'Create a new branch and switch to it', false)
        ->option('-f --force', 'Checkout even if index differs', 'boolval', false)
        ->action(function ($branch, $newBranch, $force) {
            echo 'Checkout to '
                . ($newBranch ? 'new ' . $branch : $branch)
                . ($force ? ' with force' : '');
        })
;

// Maybe it could be named `handle()` or `run()`, but again we keep legacy of `commander.js`
$app->parse(['git', 'add', 'path1', 'path2', 'path3', '-f']);
// Will produce: Add path1, path2, path3 with force

$app->parse(['git', 'co', '-b', 'master-2', '-f']);
// Will produce: Checkout to new master-2 with force
```

#### App help

It can be triggered manually with `$app->showHelp()` or automatic when `-h` or `--help` option is passed to `$app->parse()`.
**Note** If you pass something like `['app', cmd', '-h']` to `$app->parse()` it will automatically and instantly show you help of that `cmd` and not the `$app`.

For above example, the output would be:
![App Help](./sc/app-help.png "App Help")

#### App version

Same version number is passed to all attached Commands. So you can trigger version on any of the commands.

### Cli Interaction

You can perform user interaction like printing colored output, reading user input programatically and  moving the cursors around with provided `Ahc\Cli\IO\Interactor`.

```php
$interactor = new Ahc\Cli\IO\Interactor;
// For mocking io: `$interactor = new Ahc\Cli\IO\Interactor($inputPath, $outputPath)`

$confirm = $interactor->confirm('Are you happy?', 'n'); // Default: n (no)
$confirm // is a boolean
    ? $interactor->greenBold('You are happy :)', true)  // Output green bold text
    : $interactor->redBold('You are sad :(', true);     // Output red bold text

// Single choice
$fruits = ['a' => 'apple', 'b' => 'banana'];
$choice = $interactor->choice('Select a fruit', $fruits, 'b');
$interactor->greenBold("You selected: {$fruits[$choice]}", true);

// Multiple choices
$fruits  = ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'];
$choices = $interactor->choices('Select fruit(s)', $fruits, ['b', 'c']);
$choices = \array_map(function ($c) use ($fruits) { return $fruits[$c]; }, $choices);
$interactor->greenBold('You selected: ' . implode(', ', $choices), true);

// Promt free input
$any = $interactor->prompt('Anything', rand(1, 100)); // Random default
$interactor->greenBold("Anything is: $any", true);

// Prompting with validation
$nameValidator = function ($value) {
    if (\strlen($value) < 5) {
        throw new \Exception('Name should be atleast 5 chars');
    }

    return $value;
};

// No default, Retry 5 more times
$name = $interactor->prompt('Name', null, $nameValidator, 5);
$interactor->greenBold("The name is: $name", true);
```

![Interactive Preview](https://i.imgur.com/qYBNd29.gif "Interactive Preview")

### IO Components

The interactor is composed of `Ahc\Cli\Input\Reader` and `Ahc\Cli\Output\Writer` while the `Writer` itself is composed of `Ahc\Cli\Output\Color`. All these components can be used standalone.

#### Color

Color looks cool!

```php
$color = new Ahc\Cli\Output\Color;

echo $color->warn('This is warning');
echo $color->info('This is info');
echo $color->error('This is error');

// Custom style:
Ahc\Cli\Output\Color::style('mystyle', [
    'bg' => Ahc\Cli\Output\Color::CYAN,
    'fg' => Ahc\Cli\Output\Color::WHITE,
    'bold' => 1, // You can experiment with 0, 1, 2, 3 ... as well
]);

echo $color->mystyle('My text');
```

#### Cursor

Move cursor around, erase line up or down, clear screen.

```php
$cursor = new Ahc\Cli\Output\Cursor;

echo  $cursor->up(1) . $cursor->down(2)
    . $cursor->right(3) . $cursor->left(4)
    . $cursor->next(0) . $cursor->prev(2);
    . $cursor->eraseLine() . $cursor->clear()
    . $cursor->clearUp() . $cursor->clearDown()
    . $cursor->moveTo(5, 8); // x, y
```

#### Writer

Write anything in style.

```php
$writer = new Ahc\Cli\Output\Writer;

// Output formatting: You can call methods composed of:
//  ('<colorName>', 'bold', 'bg', 'fg', 'warn', 'info', 'error', 'ok', 'comment')
// ... in any order (eg: bgRedFgBlaock, boldRed, greenBold, commentBgPurple and so on ...)
$writer->bold->green->write('It is bold green');
$writer->boldGreen('It is bold green'); // Same as above
$writer->comment('This is grayish comment', true); // True indicates append EOL character.
$writer->bgPurpleBold('This is white on purple background');

// All writes are forwarded to STDOUT
// But if you specify error, then to STDERR
$writer->errorBold('This is error');

// Write a normal raw text.
$writer->raw('Enter name: ');
```

#### Reader

Read and pre process user input.

```php
$reader = new Ahc\Cli\Input\Reader;

// No default, callback fn `ucwords()`
$reader->read(null, 'ucwords');

// Default 'abc', callback `trim()`
$reader->read('abc', 'trim');
```

### Related

- [adhocore/phalcon-ext](https://github.com/adhocore/phalcon-ext)
