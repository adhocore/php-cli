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
        $rows = [['header' => 'values']];
        $expectedOutput =
            "+--------+" . PHP_EOL .
            "| Header |" . PHP_EOL .
            "+--------+" . PHP_EOL .
            "| values |" . PHP_EOL .
            "+--------+";

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_multiple_rows_and_columns(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25', 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'age' => '40', 'city' => 'Chicago']
        ];

        $expectedOutput =
            "+-------------+-----+-------------+" . PHP_EOL .
            "| Name        | Age | City        |" . PHP_EOL .
            "+-------------+-----+-------------+" . PHP_EOL .
            "| John Doe    | 30  | New York    |" . PHP_EOL .
            "| Jane Smith  | 25  | Los Angeles |" . PHP_EOL .
            "| Bob Johnson | 40  | Chicago     |" . PHP_EOL .
            "+-------------+-----+-------------+" ;

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_different_styles_for_odd_and_even_rows(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'age' => '40']
        ];

        $styles = [
            'odd' => 'bold',
            'even' => 'comment'
        ];

        $expectedOutput =
            "+-------------+-----+" . PHP_EOL .
            "| Name        | Age |" . PHP_EOL .
            "+-------------+-----+" . PHP_EOL .
            "|<bold> John Doe    </end>|<bold> 30  </end>|" . PHP_EOL .
            "|<comment> Jane Smith  </end>|<comment> 25  </end>|" . PHP_EOL .
            "|<bold> Bob Johnson </end>|<bold> 40  </end>|" . PHP_EOL .
            "+-------------+-----+";

        $result = $this->table->render($rows, $styles);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_padded_column_content(): void
    {
        $rows = [
            ['name' => 'John', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob', 'age' => '40']
        ];

        $expectedOutput =
            "+------------+-----+" . PHP_EOL .
            "| Name       | Age |" . PHP_EOL .
            "+------------+-----+" . PHP_EOL .
            "| John       | 30  |" . PHP_EOL .
            "| Jane Smith | 25  |" . PHP_EOL .
            "| Bob        | 40  |" . PHP_EOL .
            "+------------+-----+";

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_generates_correct_separators_between_header_and_body(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => '25']
        ];

        $expectedOutput =
            "+------------+-----+" . PHP_EOL .
            "| Name       | Age |" . PHP_EOL .
            "+------------+-----+" . PHP_EOL .
            "| John Doe   | 30  |" . PHP_EOL .
            "| Jane Smith | 25  |" . PHP_EOL .
            "+------------+-----+";

        $result = $this->table->render($rows);

        $this->assertStringContainsString("+------------+-----+" . PHP_EOL, $result);
        $this->assertStringContainsString("| Name       | Age |" . PHP_EOL, $result);
        $this->assertStringContainsString("+------------+-----+" . PHP_EOL, $result);
        $this->assertEquals(3, substr_count($result, "+------------+-----+" . PHP_EOL));
        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_handles_missing_values_in_rows_gracefully(): void
    {
        $rows = [
            ['name' => 'John Doe', 'age' => '30', 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => '25'],
            ['name' => 'Bob Johnson', 'city' => 'Chicago']
        ];

        $expectedOutput =
            "+-------------+-----+----------+" . PHP_EOL .
            "| Name        | Age | City     |" . PHP_EOL .
            "+-------------+-----+----------+" . PHP_EOL .
            "| John Doe    | 30  | New York |" . PHP_EOL .
            "| Jane Smith  | 25  |          |" . PHP_EOL .
            "| Bob Johnson |     | Chicago  |" . PHP_EOL .
            "+-------------+-----+----------+";

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_converts_column_names_to_words(): void
    {
        $rows = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'age_in_years' => '30'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'age_in_years' => '25']
        ];

        $expectedOutput =
            "+------------+-----------+--------------+" . PHP_EOL .
            "| First Name | Last Name | Age In Years |" . PHP_EOL .
            "+------------+-----------+--------------+" . PHP_EOL .
            "| John       | Doe       | 30           |" . PHP_EOL .
            "| Jane       | Smith     | 25           |" . PHP_EOL .
            "+------------+-----------+--------------+";

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
            "+------------+-----+" . PHP_EOL .
            "|<boldGreen> Name       </end>|<boldGreen> Age </end>|" . PHP_EOL .
            "+------------+-----+" . PHP_EOL .
            "|<bold> John Doe   </end>|<bold> 30  </end>|" . PHP_EOL .
            "|<comment> Jane Smith </end>|<comment> 25  </end>|" . PHP_EOL .
            "+------------+-----+";

        $result = $this->table->render($rows, $styles);

        $this->assertStringContainsString("<boldGreen>", $result);
        $this->assertStringContainsString("<bold>", $result);
        $this->assertStringContainsString("<comment>", $result);
        $this->assertSame($expectedOutput, trim($result));
    }

    public function test_render_with_ansi_color_codes_in_cell_content(): void
    {
        $rows = [
            ['name' => "\033[31mJohn Doe\033[0m", 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => "\033[32m25\033[0m"],
            ['name' => "\033[34mBob Johnson\033[0m", 'age' => '40']
        ];

        $expectedOutput =
            "+-------------+-----+" . PHP_EOL .
            "| Name        | Age |" . PHP_EOL .
            "+-------------+-----+" . PHP_EOL .
            "| \033[31mJohn Doe\033[0m    | 30  |" . PHP_EOL .
            "| Jane Smith  | \033[32m25\033[0m  |" . PHP_EOL .
            "| \033[34mBob Johnson\033[0m | 40  |" . PHP_EOL .
            "+-------------+-----+";

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }


    public function test_render_with_ansi_color_codes_in_cell_content_using_colors_class(): void
    {
        $color = new Color();

        $rows = [
            ['name' => $color->error('John Doe'), 'age' => '30'],
            ['name' => 'Jane Smith', 'age' => $color->ok('25')],
            ['name' => $color->info('Bob Johnson'), 'age' => '40']
        ];

        var_dump($color->ok('25'));
        // exit;
        $expectedOutput =
            "+-------------+-----+" . PHP_EOL .
            "| Name        | Age |" . PHP_EOL .
            "+-------------+-----+" . PHP_EOL .
            "| \033[0;31mJohn Doe\033[0m    | 30  |" . PHP_EOL .
            "| Jane Smith  | \033[0;32m25\033[0m  |" . PHP_EOL .
            "| \033[0;34mBob Johnson\033[0m | 40  |" . PHP_EOL .
            "+-------------+-----+";

        $result = $this->table->render($rows);

        $this->assertSame($expectedOutput, trim($result));
    }
}
