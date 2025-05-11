<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Output;

use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Table;
use Ahc\Cli\Test\CliTestCase;

class TableTest extends CliTestCase
{
    protected Table $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = new Table();
    }

    public function test_render_returns_empty_string_for_empty_rows(): void
    {
        $result = $this->table->render([]);

        $this->assertSame('', $result);
    }

    public function test_render_with_single_row_and_column(): void
    {
        $rows           = [['header' => 'values']];
        $expectedOutput =
            '+--------+' . PHP_EOL .
            '| Header |' . PHP_EOL .
            '+--------+' . PHP_EOL .
            '| values |' . PHP_EOL .
            '+--------+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_single_column(): void
    {
        $rows = [
            ['name' => 'John Doe'],
            ['name' => 'Jane Smith'],
            ['name' => 'Bob Johnson'],
        ];

        $expectedOutput =
            '+-------------+' . PHP_EOL .
            '| Name        |' . PHP_EOL .
            '+-------------+' . PHP_EOL .
            '| John Doe    |' . PHP_EOL .
            '| Jane Smith  |' . PHP_EOL .
            '| Bob Johnson |' . PHP_EOL .
            '+-------------+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_multiple_rows_and_columns(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25', 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'age' => '40', 'city' => 'Chicago'],
        ];

        $expectedOutput =
            '+-------------+-----+-------------+' . PHP_EOL .
            '| Name        | Age | City        |' . PHP_EOL .
            '+-------------+-----+-------------+' . PHP_EOL .
            '| John Doe    | 30  | New York    |' . PHP_EOL .
            '| Jane Smith  | 25  | Los Angeles |' . PHP_EOL .
            '| Bob Johnson | 40  | Chicago     |' . PHP_EOL .
            '+-------------+-----+-------------+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_different_styles_for_odd_and_even_rows(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'age' => '40'],
        ];

        $styles = [
            'odd'  => 'bold',
            'even' => 'comment',
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '| Name        | Age |' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            '|<bold> John Doe    </end>|<bold> 30  </end>|' . PHP_EOL .
            '|<comment> Jane Smith  </end>|<comment> 25  </end>|' . PHP_EOL .
            '|<bold> Bob Johnson </end>|<bold> 40  </end>|' . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_padded_column_content(): void
    {
        $rows = [
            ['name' => 'John', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob', 'age' => '40'],
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '| Name       | Age |' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '| John       | 30  |' . PHP_EOL .
            '| Jane Smith | 25  |' . PHP_EOL .
            '| Bob        | 40  |' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_generates_correct_separators_between_header_and_body(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '| Name       | Age |' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '| John Doe   | 30  |' . PHP_EOL .
            '| Jane Smith | 25  |' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows);

        $this->assertStringContainsString('+------------+-----+' . PHP_EOL, $result);
        $this->assertStringContainsString('| Name       | Age |' . PHP_EOL, $result);
        $this->assertStringContainsString('+------------+-----+' . PHP_EOL, $result);
        $this->assertEquals(3, substr_count($result, '+------------+-----+' . PHP_EOL));
        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_handles_missing_values_in_rows_gracefully(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'city' => 'Chicago'],
        ];

        $expectedOutput =
            '+-------------+-----+----------+' . PHP_EOL .
            '| Name        | Age | City     |' . PHP_EOL .
            '+-------------+-----+----------+' . PHP_EOL .
            '| John Doe    | 30  | New York |' . PHP_EOL .
            '| Jane Smith  | 25  |          |' . PHP_EOL .
            '| Bob Johnson |     | Chicago  |' . PHP_EOL .
            '+-------------+-----+----------+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_converts_column_names_to_words(): void
    {
        $rows = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'age_in_years' => '30'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'age_in_years' => '25'],
        ];

        $expectedOutput =
            '+------------+-----------+--------------+' . PHP_EOL .
            '| First Name | Last Name | Age In Years |' . PHP_EOL .
            '+------------+-----------+--------------+' . PHP_EOL .
            '| John       | Doe       | 30           |' . PHP_EOL .
            '| Jane       | Smith     | 25           |' . PHP_EOL .
            '+------------+-----------+--------------+';

        $result = $this->table->render($rows);

        $this->assertStringContainsString('| First Name |', $result);
        $this->assertStringContainsString('| Last Name |', $result);
        $this->assertStringContainsString('| Age In Years |', $result);
        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_custom_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $styles = [
            'head' => 'boldGreen', // For the table heading
            'odd'  => 'bold',      // For the odd rows (1st row is odd, then 3, 5 etc)
            'even' => 'comment',   // For the even rows (2nd row is even, then 4, 6 etc)
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '|<boldGreen> Name       </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '|<bold> John Doe   </end>|<bold> 30  </end>|' . PHP_EOL .
            '|<comment> Jane Smith </end>|<comment> 25  </end>|' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertStringContainsString('<boldGreen>', $result);
        $this->assertStringContainsString('<bold>', $result);
        $this->assertStringContainsString('<comment>', $result);
        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_ansi_color_codes_in_cell_content(): void
    {
        $rows = [
            ['name' => "\033[31mJohn Doe\033[0m", 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => "\033[32m25\033[0m"],
            ['name' => "\033[34mBob Johnson\033[0m", 'age' => '40'],
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '| Name        | Age |' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            "| \033[31mJohn Doe\033[0m    | 30  |" . PHP_EOL .
            "| Jane Smith  | \033[32m25\033[0m  |" . PHP_EOL .
            "| \033[34mBob Johnson\033[0m | 40  |" . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_ansi_color_codes_in_cell_content_using_colors_class(): void
    {
        $color = new Color();

        $rows = [
            ['name' => $color->error('John Doe'), 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => $color->ok('25')],
            ['name' => $color->info('Bob Johnson'), 'age' => '40'],
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '| Name        | Age |' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            "| \033[0;31mJohn Doe\033[0m    | 30  |" . PHP_EOL .
            "| Jane Smith  | \033[0;32m25\033[0m  |" . PHP_EOL .
            "| \033[0;34mBob Johnson\033[0m | 40  |" . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_cell_specific_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '1:1'  => 'boldRed',    // Cell-specific style for first row, first column
            '2:2'  => 'boldBlue',   // Cell-specific style for second row, second column
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '|<boldGreen> Name       </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '|<boldRed> John Doe   </end>| 30  |' . PHP_EOL .
            '| Jane Smith |<boldBlue> 25  </end>|' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_column_specific_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '*:2'  => 'boldBlue',   // Column-specific style for the second column
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '|<boldGreen> Name       </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '| John Doe   |<boldBlue> 30  </end>|' . PHP_EOL .
            '| Jane Smith |<boldBlue> 25  </end>|' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_row_specific_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'age' => '40'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '2:*'  => 'boldRed',   // Row-specific style for the second row
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '|<boldGreen> Name        </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            '| John Doe    | 30  |' . PHP_EOL .
            '|<boldRed> Jane Smith  </end>|<boldRed> 25  </end>|' . PHP_EOL .
            '| Bob Johnson | 40  |' . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_callable_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '1:1'  => function ($val, $row, $table) {
                return $val === 'John Doe' ? 'boldRed' : '';
            },
            '2:2' => function ($val, $row, $table) {
                return $val === '25' ? 'boldBlue' : '';
            },
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '|<boldGreen> Name       </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '|<boldRed> John Doe   </end>| 30  |' . PHP_EOL .
            '| Jane Smith |<boldBlue> 25  </end>|' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_callable_styles_using_row(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'age' => '40'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '*:2'  => function ($val, $row) {
                if ($val == 25) {
                    return 'boldYellow';
                }

                return $row['age'] >= 30 ? 'boldRed' : '';
            },
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '|<boldGreen> Name        </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            '| John Doe    |<boldRed> 30  </end>|' . PHP_EOL .
            '| Jane Smith  |<boldYellow> 25  </end>|' . PHP_EOL .
            '| Bob Johnson |<boldRed> 40  </end>|' . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_callable_styles_on_any_cell(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Alice Bob', 'age' => '10'],
            ['name' => 'Bob Johnson', 'age' => '40'],
            ['name' => 'Jane X', 'age' => '50'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '*:*'  => function ($val, $row) {
                if ($val === 'Jane X') {
                    return 'yellow';
                }
                if ($val == 10) {
                    return 'purple';
                }

                return $row['age'] >= 30 ? 'boldRed' : '';
            },
        ];

        $expectedOutput =
            '+-------------+-----+' . PHP_EOL .
            '|<boldGreen> Name        </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+-------------+-----+' . PHP_EOL .
            '|<boldRed> John Doe    </end>|<boldRed> 30  </end>|' . PHP_EOL .
            '| Jane Smith  | 25  |' . PHP_EOL .
            '| Alice Bob   |<purple> 10  </end>|' . PHP_EOL .
            '|<boldRed> Bob Johnson </end>|<boldRed> 40  </end>|' . PHP_EOL .
            '|<yellow> Jane X      </end>|<boldRed> 50  </end>|' . PHP_EOL .
            '+-------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_mixed_specific_styles(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25', 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'age' => '40', 'city' => 'Chicago'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '1:2'  => 'boldRed',    // Cell-specific style for first row, second column
            '*:3'  => 'boldBlue',   // Column-specific style for the third column
            '3:*'  => 'italic',     // Row-specific style for the third row
        ];

        $expectedOutput =
            '+-------------+-----+-------------+' . PHP_EOL .
            '|<boldGreen> Name        </end>|<boldGreen> Age </end>|<boldGreen> City        </end>|' . PHP_EOL .
            '+-------------+-----+-------------+' . PHP_EOL .
            '| John Doe    |<boldRed> 30  </end>|<boldBlue> New York    </end>|' . PHP_EOL .
            '| Jane Smith  | 25  |<boldBlue> Los Angeles </end>|' . PHP_EOL .
            '|<italic> Bob Johnson </end>|<italic> 40  </end>|<boldBlue> Chicago     </end>|' . PHP_EOL .
            '+-------------+-----+-------------+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_styles_using_column_name(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25', 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'age' => '40', 'city' => 'Chicago'],
        ];

        $styles = [
            'head' => 'boldGreen',
            '1:2'  => 'boldRed',    // Cell-specific style for first row, second column
            'city' => 'boldBlue',
            'name' => 'italic',
        ];

        $expectedOutput =
            '+-------------+-----+-------------+' . PHP_EOL .
            '|<boldGreen> Name        </end>|<boldGreen> Age </end>|<boldGreen> City        </end>|' . PHP_EOL .
            '+-------------+-----+-------------+' . PHP_EOL .
            '|<italic> John Doe    </end>|<boldRed> 30  </end>|<boldBlue> New York    </end>|' . PHP_EOL .
            '|<italic> Jane Smith  </end>| 25  |<boldBlue> Los Angeles </end>|' . PHP_EOL .
            '|<italic> Bob Johnson </end>| 40  |<boldBlue> Chicago     </end>|' . PHP_EOL .
            '+-------------+-----+-------------+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_empty_styles_array(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '| Name       | Age |' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '| John Doe   | 30  |' . PHP_EOL .
            '| Jane Smith | 25  |' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, []);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_handles_invalid_style_keys_gracefully(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
        ];

        $invalidStyles = [
            'invalidKey' => 'boldRed', // Invalid style key
            'head'       => 'boldGreen',
        ];

        $expectedOutput =
            '+------------+-----+' . PHP_EOL .
            '|<boldGreen> Name       </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+------------+-----+' . PHP_EOL .
            '| John Doe   | 30  |' . PHP_EOL .
            '| Jane Smith | 25  |' . PHP_EOL .
            '+------------+-----+';

        $result = $this->table->render($rows, $invalidStyles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_large_number_of_columns(): void
    {
        $columns = 100;
        $rows    = [
            array_combine(
                array_map(fn ($i) => "col$i", range(1, $columns)),
                array_map(fn ($i) => "value$i", range(1, $columns))
            ),
        ];

        $result = $this->table->render($rows);

        $this->assertStringContainsString('| Col1   | Col2   | Col3   |', $result);
        $this->assertStringContainsString('| Col98   | Col99   | Col100   |', $result);
        $this->assertStringContainsString('| value1 | value2 | value3 |', $result);
        $this->assertStringContainsString('| value98 | value99 | value100 |', $result);

        $expectedLineCount = 5; // Header, separator lines, and data row
        $this->assertEquals($expectedLineCount, substr_count($result, PHP_EOL));

        $expectedColumnCount = $columns + $columns + 2; // start + columns + separators + end
        $this->assertEquals($expectedColumnCount, substr_count($result, '|'));
    }

    public function test_render_handles_large_number_of_rows(): void
    {
        $rows = [];
        for ($i = 0; $i < 1000; $i++) {
            $rows[] = [
                'id'    => $i,
                'name'  => "Name $i",
                'email' => "email$i@example.com",
            ];
        }

        $result = $this->table->render($rows);

        $this->assertStringContainsString('| Id  | Name     | Email                |', $result);
        $this->assertStringContainsString('| 0   | Name 0   | email0@example.com   |', $result);
        $this->assertStringContainsString('| 999 | Name 999 | email999@example.com |', $result);
        $this->assertEquals(1004, substr_count($result, PHP_EOL)); // 1000 data rows + 4 border rows
    }

    public function test_render_with_html_like_tags_in_cell_content(): void
    {
        $rows = [
            ['name' => '<b>John Doe</b>', 'age' => '30'],
            ['name' => 'Jane <i>Smith</i>', 'age' => '25'],
            ['name' => '<span>Bob Johnson</span>', 'age' => '40'],
        ];

        $styles = [
            'head' => 'boldGreen',
            'odd'  => 'bold',
            'even' => 'comment',
        ];

        $expectedOutput =
            '+--------------------------+-----+' . PHP_EOL .
            '|<boldGreen> Name                     </end>|<boldGreen> Age </end>|' . PHP_EOL .
            '+--------------------------+-----+' . PHP_EOL .
            '|<bold> <b>John Doe</b>          </end>|<bold> 30  </end>|' . PHP_EOL .
            '|<comment> Jane <i>Smith</i>        </end>|<comment> 25  </end>|' . PHP_EOL .
            '|<bold> <span>Bob Johnson</span> </end>|<bold> 40  </end>|' . PHP_EOL .
            '+--------------------------+-----+';

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_unicode_characters_in_cell_content(): void
    {
        if (!extension_loaded('mbstring')) {
            $this->markTestSkipped('The mbstring extension is not installed. This test will faill without it');
        }

        $rows = [
            ['name' => 'François', 'greeting' => 'Bonjour'],
            ['name' => 'Jürgen', 'greeting' => 'Guten Tag'],
            ['name' => '北京', 'greeting' => '你好'],
        ];

        $expectedOutput =
            '+----------+-----------+' . PHP_EOL .
            '| Name     | Greeting  |' . PHP_EOL .
            '+----------+-----------+' . PHP_EOL .
            '| François | Bonjour   |' . PHP_EOL .
            '| Jürgen   | Guten Tag |' . PHP_EOL .
            '| 北京     | 你好      |' . PHP_EOL .
            '+----------+-----------+';

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    /**
     * Tests the rendering of table data where cell content includes formatting tags.
     *
     * Validates that the table rendering properly handles formatting tags by not including their length in the final
     * column width computation.
     *
     * @return void
     */
    public function test_render_with_formatting_tags_in_cell_content(): void
    {
        $rows = [
            ['name' => "<bold>Robert Smith</end>", 'age' => '<yellow>30</end>'],
            ['name' => "<bold>Elizabeth Fraser</end>", 'age' => '<yellow>25</end>'],
        ];

        $expectedOutput = implode(PHP_EOL, [
            '+------------------+-----+',
            '| Name             | Age |',
            '+------------------+-----+',
            '| <bold>Robert Smith</end>     | <yellow>30</end>  |',
            '| <bold>Elizabeth Fraser</end> | <yellow>25</end>  |',
            '+------------------+-----+'
        ]);

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }
}
