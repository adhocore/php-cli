## [v1.2.0](https://github.com/adhocore/php-cli/releases/tag/v1.2.0) (2022-10-08)

### Features
- **App**: Set common group to commands set via callable (Jitendra Adhikari) [_6c6e53c_](https://github.com/adhocore/php-cli/commit/6c6e53c)
- **Helper**: Support grouped sorting for show help (Jitendra Adhikari) [_314a887_](https://github.com/adhocore/php-cli/commit/314a887)
- **Input**: Add Groupable interface, make Command groupable (Jitendra Adhikari) [_29b09ce_](https://github.com/adhocore/php-cli/commit/29b09ce)

### Bug Fixes
- Adapt for strict type php8 (Jitendra Adhikari) [_8198969_](https://github.com/adhocore/php-cli/commit/8198969)

### Internal Refactors
- *****: Use imports instead of FQN (Jitendra Adhikari) [_bd0a70c_](https://github.com/adhocore/php-cli/commit/bd0a70c)

### Miscellaneous
- **Travis**: Retire it :( (Jitendra Adhikari) [_70e510b_](https://github.com/adhocore/php-cli/commit/70e510b)

### Documentations
- Add Grouping commands section (Jitendra Adhikari) [_3e05837_](https://github.com/adhocore/php-cli/commit/3e05837)

### Builds
- **Workflow**: Add github action build (Jitendra Adhikari) [_9b4da7d_](https://github.com/adhocore/php-cli/commit/9b4da7d)


## [v1.0.1](https://github.com/adhocore/php-cli/releases/tag/v1.0.1) (2022-07-05)

### Bug Fixes
- Correct io() fallback mechanism (Daniel Jakob) [_4072eaf_](https://github.com/adhocore/php-cli/commit/4072eaf)


## [v1.0.0](https://github.com/adhocore/php-cli/releases/tag/v1.0.0) (2022-06-30)

### Bug Fixes
- **Shell**: Exitcode is null first (Jitendra Adhikari)

### Internal Refactors
- Add typehints, remove redundant docblocks (Jitendra Adhikari)
- Use php8 syntax (Jitendra Adhikari)

### Miscellaneous
- Typehint (Jitendra)
- Run on >= php8 (Jitendra Adhikari)
- Use php8 deps (Jitendra Adhikari)


## [0.9.1](https://github.com/adhocore/php-cli/releases/tag/0.9.1) (2022-02-18)

### Bug Fixes
- Php8.1 substr, d197dc6 (Jitendra A)


## [0.8.4](https://github.com/adhocore/php-cli/releases/tag/0.8.4) (2020-10-09)

### Builds
- **Travis**: Add php 7.3 and 7.4 (Jitendra Adhikari) [_a5c4a16_](https://github.com/adhocore/php-cli/commit/a5c4a16)


## [0.8.3](https://github.com/adhocore/php-cli/releases/tag/0.8.3) (2020-01-05)

### Internal Refactors
- **App**: Extract cmd not found to outputhelper (Jitendra Adhikari) [_c317634_](https://github.com/adhocore/php-cli/commit/c317634)


## [0.8.2](https://github.com/adhocore/php-cli/releases/tag/0.8.2) (2020-01-03)

### Bug Fixes
- **Normalizer**: Complex option containing value delimited by = (Jitendra Adhikari) [_5d5394a_](https://github.com/adhocore/php-cli/commit/5d5394a)


## [0.8.1](https://github.com/adhocore/php-cli/releases/tag/0.8.1) (2020-01-03)

### Bug Fixes
- **Cmd.action**: Can be array too (besides closure/null) (Jitendra Adhikari) [_238c8b1_](https://github.com/adhocore/php-cli/commit/238c8b1)


## [0.8.0](https://github.com/adhocore/php-cli/releases/tag/0.8.0) (2020-01-03)

### Features
- **Cmd.action**: Bind to $this (Jitendra Adhikari) [_c479995_](https://github.com/adhocore/php-cli/commit/c479995)

### Documentations
- Add credits, update license year (Jitendra Adhikari) [_2bf08c5_](https://github.com/adhocore/php-cli/commit/2bf08c5)


## [0.7.3](https://github.com/adhocore/php-cli/releases/tag/0.7.3) (2020-01-03)

### Internal Refactors
- **App**: Add onExit prop, execute or action can return exit code (Jitendra Adhikari) [_1e754a8_](https://github.com/adhocore/php-cli/commit/1e754a8)

### Documentations
- About cmd exit code (Jitendra Adhikari) [_32d45c2_](https://github.com/adhocore/php-cli/commit/32d45c2)
- About cmd usage ($0 and ##) (Jitendra Adhikari) [_2319370_](https://github.com/adhocore/php-cli/commit/2319370)


## [0.7.2](https://github.com/adhocore/php-cli/releases/tag/0.7.2) (2020-01-02)

### Bug Fixes
- **Cmd.help**: Usage label was printed even if no text (Jitendra Adhikari) [_4639624_](https://github.com/adhocore/php-cli/commit/4639624)


## [0.7.1](https://github.com/adhocore/php-cli/releases/tag/0.7.1) (2020-01-02)

### Bug Fixes
- **Phpunit**: Rm xml.syntaxCheck (Jitendra Adhikari) [_6ae66b0_](https://github.com/adhocore/php-cli/commit/6ae66b0)

### Internal Refactors
- **Interactor**: Reduce complexity in prompt (Jitendra Adhikari) [_dd008af_](https://github.com/adhocore/php-cli/commit/dd008af)

### Miscellaneous
- **Composer**: Tweak script.test (Jitendra Adhikari) [_9b8ee5d_](https://github.com/adhocore/php-cli/commit/9b8ee5d)
- **Travis**: Script (Jitendra Adhikari) [_c41e256_](https://github.com/adhocore/php-cli/commit/c41e256)


## [0.7.0](https://github.com/adhocore/php-cli/releases/tag/0.7.0) (2019-12-30)

### Bug Fixes
- **Output.helper**: Pad ## (Jitendra Adhikari) [_73a4a4e_](https://github.com/adhocore/php-cli/commit/73a4a4e)
- **Normalizer**: Invert bool iff type is bool not value (Jitendra Adhikari) [_6ff4acd_](https://github.com/adhocore/php-cli/commit/6ff4acd)

### Internal Refactors
- **Option**: Improve bool() check (Jitendra Adhikari) [_e7e95e3_](https://github.com/adhocore/php-cli/commit/e7e95e3)


## [0.6.2](https://github.com/adhocore/php-cli/releases/tag/0.6.2) (2019-12-30)

### Bug Fixes
- **Color**: Comment line shows white trailing bar (daemonu) [_b578d9a_](https://github.com/adhocore/php-cli/commit/b578d9a)


## [0.6.1](https://github.com/adhocore/php-cli/releases/tag/0.6.1) (2019-12-29)

### Miscellaneous
- Extend exception from throwable (Jitendra Adhikari) [_ab6b351_](https://github.com/adhocore/php-cli/commit/ab6b351)


## [0.6.0](https://github.com/adhocore/php-cli/releases/tag/0.6.0) (2019-12-26)

### Features
- **Table**: Add table renderer class (Jitendra Adhikari) [_808e80e_](https://github.com/adhocore/php-cli/commit/808e80e)
- **Reader**: Add readAll() (Jitendra Adhikari) [_9264082_](https://github.com/adhocore/php-cli/commit/9264082)
- **Output**: Add show usage (Jitendra Adhikari) [_1356515_](https://github.com/adhocore/php-cli/commit/1356515)
- **Reader**: Add read piped (Jitendra Adhikari) [_790f2a1_](https://github.com/adhocore/php-cli/commit/790f2a1)

### Internal Refactors
- **Reader**: Visibility (Jitendra Adhikari) [_bcea11b_](https://github.com/adhocore/php-cli/commit/bcea11b)
- **Writer**: Use Table::render instead (Jitendra Adhikari) [_f0f33ee_](https://github.com/adhocore/php-cli/commit/f0f33ee)
- **Command**: Use helper showUsage() instead (Jitendra Adhikari) [_ef5ea2b_](https://github.com/adhocore/php-cli/commit/ef5ea2b)

### Miscellaneous
- **Composer**: Add test scripts (Jitendra Adhikari) [_4d292ca_](https://github.com/adhocore/php-cli/commit/4d292ca)
- **Color**: Add dark and light gray colors (Jitendra Adhikari) [_2d4051d_](https://github.com/adhocore/php-cli/commit/2d4051d)

### Documentations
- Add readAll() usage (Jitendra Adhikari) [_62cbfd0_](https://github.com/adhocore/php-cli/commit/62cbfd0)
- Update intro and credits (Jitendra Adhikari) [_7cbaae6_](https://github.com/adhocore/php-cli/commit/7cbaae6)
- Add readHidden, readPiped usage (Jitendra Adhikari) [_57dae5e_](https://github.com/adhocore/php-cli/commit/57dae5e)


## [0.5.0](https://github.com/adhocore/php-cli/releases/tag/0.5.0) (2019-09-16)

### Features
- **Reader**: Add read hidden for win os (Jitendra Adhikari) [_742c622_](https://github.com/adhocore/php-cli/commit/742c622)

### Internal Refactors
- **Interactor**: Prompt hidden now supported in win os (Jitendra Adhikari) [_491d162_](https://github.com/adhocore/php-cli/commit/491d162)

### Documentations
- Add a note about hidden prompt on win os (Jitendra Adhikari) [_43fe762_](https://github.com/adhocore/php-cli/commit/43fe762)


## [0.4.0](https://github.com/adhocore/php-cli/releases/tag/0.4.0) (2019-09-07)

### Features
- **Interactor**: Add promptHidden (unix only) (Jitendra Adhikari) [_1eb06c6_](https://github.com/adhocore/php-cli/commit/1eb06c6)
- **Reader**: Add readHidden (Jitendra Adhikari) [_3628331_](https://github.com/adhocore/php-cli/commit/3628331)

### Documentations
- About hidden prompt (Jitendra Adhikari) [_af086f9_](https://github.com/adhocore/php-cli/commit/af086f9)


## [0.3.3] 2019-03-09 06:03:34 UTC

- [573d3c1](https://github.com/adhocore/php-cli/commit/573d3c1) refactor: remove sc folder and update readme with imgur link (Sushil Gupta)
- [121ab6c](https://github.com/adhocore/php-cli/commit/121ab6c) refactor(plugin): use gawk, cleanup (Jitendra Adhikari)
- [aeaf5f4](https://github.com/adhocore/php-cli/commit/aeaf5f4) refactor(plugin): phpcli => ahccli (Jitendra Adhikari)
- [e625fd0](https://github.com/adhocore/php-cli/commit/e625fd0) chore: phpcli => ahccli (Jitendra Adhikari)
- [9e31caf](https://github.com/adhocore/php-cli/commit/9e31caf) docs: improve auto completion docs, rename phpcli to ahccli (Jitendra Adhikari)

## [0.3.2] 2018-09-06 15:09:22 UTC

- [6e8755f](https://github.com/adhocore/php-cli/commit/6e8755f) docs: autocompletion (Jitendra Adhikari)
- [1152671](https://github.com/adhocore/php-cli/commit/1152671) chore(zsh.plugin): auto complete provider for zsh with oh-my-zsh (Jitendra Adhikari)

## [0.3.1] 2018-09-06 00:09:23 UTC

- [f390b6b](https://github.com/adhocore/php-cli/commit/f390b6b) refactor: remove redundant codeCoverageIgnore (Sushil Gupta)
- [cd8d109](https://github.com/adhocore/php-cli/commit/cd8d109) refactor: minor refactor on messages + add isWindows() method using DIRECTORY_SEPARATOR check to set pipes (Sushil Gupta)

## [0.3.0] 2018-09-04 15:09:50 UTC

- [a1c2c30](https://github.com/adhocore/php-cli/commit/a1c2c30) docs: add shell section and contributors (Jitendra Adhikari)
- [5146260](https://github.com/adhocore/php-cli/commit/5146260) test: shell tests (Jitendra Adhikari)
- [d1e8e73](https://github.com/adhocore/php-cli/commit/d1e8e73) refactor(shell): ignore cov, cleanup etc (Jitendra Adhikari)
- [8d5ebe9](https://github.com/adhocore/php-cli/commit/8d5ebe9) feat(shell): a shell wrapper (Jitendra Adhikari)
- [37c0e4c](https://github.com/adhocore/php-cli/commit/37c0e4c) Async true gives the process ID (Sushil Gupta)
- [1052ca0](https://github.com/adhocore/php-cli/commit/1052ca0) More style fixes (Sushil Gupta)
- [989213f](https://github.com/adhocore/php-cli/commit/989213f) Style fixes (Sushil Gupta)
- [29e8d13](https://github.com/adhocore/php-cli/commit/29e8d13) If timeout is set, and is set to wait (not async by default), then either wait until it runs or kill it after timeout occurs - if async (not wait) - then don't care about the process at all (Sushil Gupta)
- [7cf9536](https://github.com/adhocore/php-cli/commit/7cf9536) If not async, then check for timeout if it is still running and attempt to stop it (Sushil Gupta)
- [88bc092](https://github.com/adhocore/php-cli/commit/88bc092) Stop - not kill (Sushil Gupta)
- [40ba003](https://github.com/adhocore/php-cli/commit/40ba003) Minor formatting fixed (Sushil Gupta)
- [f81237e](https://github.com/adhocore/php-cli/commit/f81237e) Added set options method (Sushil Gupta)
- [2d44553](https://github.com/adhocore/php-cli/commit/2d44553) On destruct, if running, waiting until timeout and then attempting to stop, instead of directly attempting to stop (Sushil Gupta)
- [acabcca](https://github.com/adhocore/php-cli/commit/acabcca) Root namespace appended for microtime (Sushil Gupta)
- [e86580a](https://github.com/adhocore/php-cli/commit/e86580a) Removed redundant unblocking of getOutput (Sushil Gupta)
- [76fce1b](https://github.com/adhocore/php-cli/commit/76fce1b) Minor DocBlock update (Sushil Gupta)
- [926c4a6](https://github.com/adhocore/php-cli/commit/926c4a6) WIP - Implemented timeout checking and wait system - not working yet (Sushil Gupta)
- [641d229](https://github.com/adhocore/php-cli/commit/641d229) Minor refactor - removing updateProcessStatus when asking for getState - not related (Sushil Gupta)
- [205daed](https://github.com/adhocore/php-cli/commit/205daed) Refactored to add another state variable to store actual state of the shell execution vs the process status (Sushil Gupta)
- [62acd2d](https://github.com/adhocore/php-cli/commit/62acd2d) File default info added (Sushil Gupta)
- [acdbd64](https://github.com/adhocore/php-cli/commit/acdbd64) Refactor - assigning default null + only assigning exit value if not already set and process has stopped (Sushil Gupta)
- [d742ecb](https://github.com/adhocore/php-cli/commit/d742ecb) Updating status before sending back exitcodes (Sushil Gupta)
- [96e3a9e](https://github.com/adhocore/php-cli/commit/96e3a9e) Made private methods protected (Sushil Gupta)
- [3939825](https://github.com/adhocore/php-cli/commit/3939825) Setting exit code on proc_close from the proc_get_status itself (Sushil Gupta)
- [56ba25d](https://github.com/adhocore/php-cli/commit/56ba25d) Implemented suggestions from code-review (Sushil Gupta)
- [dbb3c21](https://github.com/adhocore/php-cli/commit/dbb3c21) Refactored small things (Sushil Gupta)
- [13380da](https://github.com/adhocore/php-cli/commit/13380da) Removed timeout - not used anywhere for now (Sushil Gupta)
- [2d0f815](https://github.com/adhocore/php-cli/commit/2d0f815) One more style fix (Sushil Gupta)
- [e93b398](https://github.com/adhocore/php-cli/commit/e93b398) More style fixes :/ (Sushil Gupta)
- [1be59a6](https://github.com/adhocore/php-cli/commit/1be59a6) More style fixes (Sushil Gupta)
- [e43a34f](https://github.com/adhocore/php-cli/commit/e43a34f) More style fixes (Sushil Gupta)
- [0874497](https://github.com/adhocore/php-cli/commit/0874497) Style fixes - unindenting inside <?php tag (Sushil Gupta)
- [5923304](https://github.com/adhocore/php-cli/commit/5923304) Removed vdd (Sushil Gupta)
- [304f148](https://github.com/adhocore/php-cli/commit/304f148) Removed wait method - wasn't working - to be added (Sushil Gupta)
- [735294c](https://github.com/adhocore/php-cli/commit/735294c) Removed env + cwd from the options, passing null, for the sprit of minimalism ;) (Sushil Gupta)
- [bfe1965](https://github.com/adhocore/php-cli/commit/bfe1965) Added basic test case for getOutput (Sushil Gupta)
- [4972de3](https://github.com/adhocore/php-cli/commit/4972de3) Moved to helper (Sushil Gupta)
- [425af5e](https://github.com/adhocore/php-cli/commit/425af5e) Added pipes for different platform, checking directory separator + added public method to return PID (Sushil Gupta)
- [19ce603](https://github.com/adhocore/php-cli/commit/19ce603) Minor refactoring (Sushil Gupta)
- [22a759a](https://github.com/adhocore/php-cli/commit/22a759a) Moved public functions to the bottom (Sushil Gupta)
- [87ed2e4](https://github.com/adhocore/php-cli/commit/87ed2e4) Made some methods private + added exitCode method (Sushil Gupta)
- [c4c4f4e](https://github.com/adhocore/php-cli/commit/c4c4f4e) Added wait and other methods (Sushil Gupta)
- [bd54feb](https://github.com/adhocore/php-cli/commit/bd54feb) Using constants for descriptors key (Sushil Gupta)
- [4d8578e](https://github.com/adhocore/php-cli/commit/4d8578e) Minor refactoring (Sushil Gupta)
- [1e42021](https://github.com/adhocore/php-cli/commit/1e42021) Shell wrapper - basic proc_open implemented (Sushil Gupta)

## [0.2.1] 2018-08-28 14:08:59 UTC

- [25c3f1a](https://github.com/adhocore/php-cli/commit/25c3f1a) docs: improve readability and organize (Jitendra Adhikari)

## [0.2.0] 2018-08-21 14:08:52 UTC

- [a75c76e](https://github.com/adhocore/php-cli/commit/a75c76e) feat(cmd.option): support multiline desc and indent them properly on help (Jitendra Adhikari)
- [7b04d18](https://github.com/adhocore/php-cli/commit/7b04d18) refactor: readme > README (Jitendra Adhikari)
- [6e79204](https://github.com/adhocore/php-cli/commit/6e79204) docs: exceptions preview (Jitendra Adhikari)
- [c5ffb12](https://github.com/adhocore/php-cli/commit/c5ffb12) test: 100% cov ftw (Jitendra Adhikari)
- [92f41ba](https://github.com/adhocore/php-cli/commit/92f41ba) feat(output.helper): add print trace (Jitendra Adhikari)
- [7b5080e](https://github.com/adhocore/php-cli/commit/7b5080e) refactor(app): output helper instantiation and print trace (Jitendra Adhikari)
