<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use Carbon\Carbon;
use DateTime;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules;
use DonnySim\Validation\Tests\Stubs\RuleStub;
use DonnySim\Validation\Tests\Stubs\TestMessageResolver;
use DonnySim\Validation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function DonnySim\Validation\rule;

class ValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function active_url_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'aslsdlks'], [Rules::make('foo')->activeUrl()]);
        $this->assertValidationFail($v, 'foo', 'foo must be an active url');

        $v = $this->makeValidator(['foo' => ['fdsfs', 'fdsfds']], [Rules::make('foo')->activeUrl()]);
        $this->assertValidationFail($v, 'foo', 'foo must be an active url');

        $v = $this->makeValidator(['foo' => 'http://google.com'], [Rules::make('foo')->activeUrl()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'http://www.google.com'], [Rules::make('foo')->activeUrl()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'http://www.google.com/about'], [Rules::make('foo')->activeUrl()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function alpha_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'aslsdlks'], [Rules::make('foo')->alpha()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator([
            'foo' => 'aslsdlks
1
1',
        ], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 'http://google.com'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 'ユニコードを基盤技術と'], [Rules::make('foo')->alpha()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ユニコード を基盤技術と'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 'नमस्कार'], [Rules::make('foo')->alpha()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'आपका स्वागत है'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 'Continuación'], [Rules::make('foo')->alpha()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ofreció su dimisión'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => '❤'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => '123'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 123], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');

        $v = $this->makeValidator(['foo' => 'abc123'], [Rules::make('foo')->alpha()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha');
    }

    /**
     * @test
     */
    public function alpha_dash_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'asls1-_3dlks'], [Rules::make('foo')->alphaDash()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'http://-g232oogle.com'], [Rules::make('foo')->alphaDash()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha dash');

        $v = $this->makeValidator(['foo' => 'नमस्कार-_'], [Rules::make('foo')->alphaDash()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '٧٨٩'], [Rules::make('foo')->alphaDash()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function alpha_num_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'asls13dlks'], [Rules::make('foo')->alphaNum()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'http://g232oogle.com'], [Rules::make('foo')->alphaNum()]);
        $this->assertValidationFail($v, 'foo', 'foo must be alpha num');

        $v = $this->makeValidator(['foo' => '१२३'], [Rules::make('foo')->alphaNum()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '٧٨٩'], [Rules::make('foo')->alphaNum()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'नमस्कार'], [Rules::make('foo')->alphaNum()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function accepted_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator([], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => false], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 'false'], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'on'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function array_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->arrayType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be array');

        $v = $this->makeValidator([], [Rules::make('foo')->arrayType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [Rules::make('foo')->arrayType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => 'baz']], [Rules::make('foo')->arrayType()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function date_before_and_after_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateBefore('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be before 2000-01-01');

        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => [new Carbon('2000-01-01')]], [Rules::make('x')->dateBefore('2012-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01');

        $v = $this->makeValidator(['x' => '2012-01-01'], [Rules::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => ['2012-01-01']], [Rules::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be after 2000-01-01');

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be after 2000-01-01');

        $v = $this->makeValidator(['x' => new Carbon('2012-01-01')], [Rules::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 2012-01-01');

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 2000-01-01', $v->getMessages()->first('start'));
        self::assertSame('ends must be after 2012-01-01', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(['x' => new DateTime('2000-01-01')], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2000-01-01'),
                'ends' => new Carbon('2013-01-01'),
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => new DateTime('2013-01-01'),
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2012-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 2012-01-01 00:00:00');

        $v = $this->makeValidator(
            [
                'start' => 'today',
                'ends' => 'tomorrow',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:43:59'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44')->dateAfter('2012-01-01 17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:01'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:02')->dateAfter('2012-01-01 17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [Rules::make('x')->dateAfter('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:43:59'],
            [Rules::make('x')->dateBefore('17:44')->dateAfter('17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:01'],
            [Rules::make('x')->dateBefore('17:44:02')->dateAfter('17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateAfter('17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44:00');
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, 'x', 'x must be before 31/02/2012');

        $v = $this->makeValidator(['x' => ['31/12/2000']], [Rules::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, 'x', 'x must be before 31/02/2012');

        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateBefore('31/02/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, 'x', 'x must be after 31/12/2000');

        $v = $this->makeValidator(['x' => ['31/12/2012']], [Rules::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, 'x', 'x must be after 31/12/2000');

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateAfter('31/12/2000', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateAfter('01/01/2000'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be after 01/01/2000', $v->getMessages()->first('start'));
        self::assertSame('ends must be after start', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be after 31/12/2000', $v->getMessages()->first('start'));
        self::assertSame('ends must be after start', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 31/12/2012');

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before ends', $v->getMessages()->first('start'));
        self::assertSame('ends must be after start', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends'), 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before ends', $v->getMessages()->first('start'));
        self::assertSame('ends must be after start', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends'), 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 31/12/2000', $v->getMessages()->first('start'));
        self::assertSame('ends must be after 31/12/2012', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            ['x' => \date('d/m/Y')],
            [Rules::make('x')->dateAfter('yesterday', 'd/m/Y')->dateBefore('tomorrow', 'd/m/Y')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => \date('d/m/Y')],
            [Rules::make('x')->dateAfter('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after today');

        $v = $this->makeValidator(
            ['x' => \date('d/m/Y')],
            [Rules::make('x')->dateBefore('today', 'd/m/Y')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');

        $v = $this->makeValidator(
            ['x' => \date('Y-m-d')],
            [Rules::make('x')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => \date('Y-m-d')],
            [Rules::make('x')->dateAfter('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after today');

        $v = $this->makeValidator(
            ['x' => \date('Y-m-d')],
            [Rules::make('x')->dateBefore('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:01', 'Y-m-d H:i:s')->dateAfter('2012-01-01 17:43:59', 'Y-m-d H:i:s')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:00', 'Y-m-d H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateBefore('17:44:01', 'H:i:s')->dateAfter('17:43:59')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateBefore('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateAfter('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:45', 'H:i')->dateAfter('17:43')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateAfter('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44');
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_from_date_format_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateFormat('d/m/Y')->dateBefore('31/02/2012')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateFormat('d/m/Y')->dateAfter('31/12/2000')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 31/12/2012');

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 31/12/2000', $v->getMessages()->first('start'));
        self::assertSame('ends must be after 31/12/2012', $v->getMessages()->first('ends'));

        $v = $this->makeValidator(
            ['x' => \date('d/m/Y')],
            [Rules::make('x')->dateFormat('d/m/Y')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => \date('d/m/Y')],
            [Rules::make('x')->dateFormat('d/m/Y')->dateBefore('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');
    }

    /**
     * @test
     */
    public function boolean_like_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'false'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator([], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => false], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '0'], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'asd'], [Rules::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean like');

        $v = $this->makeValidator(['foo' => [true]], [Rules::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean like');

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean like');
    }

    /**
     * @test
     */
    public function boolean_type_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => 'false'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator([], [Rules::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => false], [Rules::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => '0'], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');
    }

    /**
     * @test
     */
    public function cast_to_boolean(): void
    {
        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'on'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);

        $v = $this->makeValidator(['foo' => 'anything non booleanish'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);
    }

    /**
     * @test
     */
    public function confirmed(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'baz'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'bar'], [Rules::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1e2', 'foo_confirmation' => '100'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');
    }

    /**
     * @test
     */
    public function date_format_rule(): void
    {
        $v = $this->makeValidator(['foo' => '2000-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '01/01/2001'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '22000-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '00-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => ['Not', 'a', 'date']], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '2013-02'], [Rules::make('foo')->dateFormat('Y-m')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Atlantic/Azores'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:se')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Z'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sT')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+0000'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sO')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+00:30'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sP')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [Rules::make('foo')->dateFormat('Y-m-d H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [Rules::make('foo')->dateFormat('H:i:s')]);
        $this->assertValidationFail($v, 'foo', 'foo must match H:i:s');

        $v = $this->makeValidator(['foo' => '17:43:59'], [Rules::make('foo')->dateFormat('H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '17:43:59'], [Rules::make('foo')->dateFormat('H:i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match H:i');

        $v = $this->makeValidator(['foo' => '17:43'], [Rules::make('foo')->dateFormat('H:i')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function email_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'aslsdlks'], [Rules::make('email')->email()]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => ['aslsdlks']], [Rules::make('email')->email()]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'foo@gmail.com'], [Rules::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'foo@gmäil.com'], [Rules::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'foo@bar '], [Rules::make('email')->email(['strict'])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'foo@bar'], [Rules::make('email')->email(['filter'])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'example@example.com'], [Rules::make('email')->email(['filter'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@example.com'], [Rules::make('email')->email(['filter'])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@exämple.com'], [Rules::make('email')->email(['filter'])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'foo@bar'], [Rules::make('email')->email(['filter_unicode'])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['email' => 'example@example.com'], [Rules::make('email')->email(['filter_unicode'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@example.com'], [Rules::make('email')->email(['filter_unicode'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@exämple.com'], [Rules::make('email')->email(['filter_unicode'])]);
        self::assertFalse($v->passes());
    }

    /**
     * @test
     */
    public function ends_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('hello')]);
        $this->assertValidationFail($v, 'foo', 'foo must end with hello');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('world')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('http')]);
        $this->assertValidationFail($v, 'foo', 'foo must end with http');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith(['https', 'http'])]);
        $this->assertValidationFail($v, 'foo', 'foo must end with https, http');
    }

    /**
     * @test
     */
    public function filled_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->filled()]);
        $this->assertValidationFail($v, 'foo', 'foo must be filled');

        $v = $this->makeValidator(['foo' => [['id' => 1], []]], [Rules::make('foo.*.id')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => '']]], [Rules::make('foo.*.id')->filled()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id must be filled');

        $v = $this->makeValidator(['foo' => [['id' => null]]], [Rules::make('foo.*.id')->filled()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id must be filled');
    }

    /**
     * @test
     */
    public function integer_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->integerType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be integer');

        $v = $this->makeValidator([], [Rules::make('foo')->integerType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->integerType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.12], [Rules::make('foo')->integerType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be integer');

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->integerType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be integer');
    }

    /**
     * @test
     */
    public function in_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'foo'], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');
    }

    /**
     * @test
     */
    public function ip_address_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'aslsdlks'], [Rules::make('foo')->ipAddress()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ip address');

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress(Rules\IpAddress::TYPE_IPV4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '::1'], [Rules::make('foo')->ipAddress(Rules\IpAddress::TYPE_IPV6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress(Rules\IpAddress::TYPE_IPV6)]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ip address');

        $v = $this->makeValidator(['foo' => '::1'], [Rules::make('foo')->ipAddress(Rules\IpAddress::TYPE_IPV4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ip address');
    }

    /**
     * @test
     */
    public function not_regex_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'foo bar'], [Rules::make('foo')->notRegex('/[xyz]/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'foo xxx bar'], [Rules::make('foo')->notRegex('/[xyz]/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must not match regex');

        $v = $this->makeValidator(['foo' => 'foo bar'], [Rules::make('foo')->notRegex('/x{3,}/i')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function not_in_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'foo'], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must not be in array');

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function present_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->present()]);
        $this->assertValidationFail($v, 'foo', 'foo must be present');

        $v = $this->makeValidator([], [Rules::make('foo')->present()->nullable()]);
        $this->assertValidationFail($v, 'foo', 'foo must be present');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['name' => 'a']]], [Rules::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id must be present');

        $v = $this->makeValidator(['foo' => [['id' => 1], []]], [Rules::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id must be present');

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => '']]], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => null]]], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function regex_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'asdasdf'], [Rules::make('foo')->regex('/^[a-z]+$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'aasd234fsd1'], [Rules::make('foo')->regex('/^[a-z]+$/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match regex');

        $v = $this->makeValidator(['foo' => 'a,b'], [Rules::make('foo')->regex('/^a,b$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12'], [Rules::make('foo')->regex('/^12$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12], [Rules::make('foo')->regex('/^12$/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match regex');
    }

    /**
     * @test
     */
    public function required_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => []], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => 'name'], [Rules::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['name']], [Rules::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [Rules::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [null]], [Rules::make('foo.*')->required()]);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 is required');

        $v = $this->makeValidator(['foo' => ['bar']], [Rules::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', null]], [Rules::make('foo.*')->required()]);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 is required');

        // TODO
//        $file = new File(__FILE__, false);
//        $file2 = new File(__FILE__, false);
//        $v = $this->makeValidator(['files' => [$file, $file2]], [
//            'files.0' => Rule::make()->required(),
//            'files.1' => Rule::make()->required(),
//        ]);
//        self::assertTrue($v->passes());
//
//        $v = $this->makeValidator(['files' => [$file, $file2]], ['files' => Rule::make()->required()]);
//        self::assertTrue($v->passes());
//
//        $file = new File('', false);
//        $v = new Validator($trans, ['name' => $file], ['name' => 'Required']);
//        $this->assertFalse($v->passes());
//
//        $file = new File(__FILE__, false);
//        $v = new Validator($trans, ['name' => $file], ['name' => 'Required']);
//        $this->assertTrue($v->passes());
//
//        $file = new File(__FILE__, false);
//        $file2 = new File(__FILE__, false);
//        $v = new Validator($trans, ['files' => [$file, $file2]], ['files.0' => 'Required', 'files.1' => 'Required']);
//        $this->assertTrue($v->passes());
//
//        $v = new Validator($trans, ['files' => [$file, $file2]], ['files' => 'Required']);
//        $this->assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function it_throws_if_rule_is_not_single_or_batch_rule(): void
    {
        $this->makeValidator([], [Rules::make('foo')->rule(new Rules\Required())]);
        // TODO batch

        $this->expectException(InvalidArgumentException::class);
        $this->makeValidator([], [Rules::make('foo')->rule(new RuleStub())]);
    }

    /**
     * @test
     */
    public function rule_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->rule(new Rules\Required())]);
        self::assertFalse($v->passes());
    }

    /**
     * @test
     */
    public function max_rule(): void
    {
        // TODO numeric

        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->max(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be max 3 length');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->max(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '211'], [Rules::make('foo')->max(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '22'], [Rules::make('foo')->max(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->max(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->max(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain max 2 items');
    }

    /**
     * @test
     */
    public function min_rule(): void
    {
        // TODO numeric
        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->min(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be min 3 length');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->min(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->min(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be min 3');

        $v = $this->makeValidator(['foo' => 'abc'], [Rules::make('foo')->min(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ab'], [Rules::make('foo')->min(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be min 3 length');

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->min(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [Rules::make('foo')->min(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain 3 items');
    }

    /**
     * @test
     */
    public function nullable_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->nullable()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->nullable()->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->nullable()->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => [null, 'true']], [Rules::make('foo.*')->nullable()->booleanType()]);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 must be boolean');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->nullable()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function numeric_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->nullable()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->numeric()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric');

        $v = $this->makeValidator(['foo' => '1.23'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function omit_from_data(): void
    {
        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->omitFromData()]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator([
            'foo' => [
                ['id' => 1, 'name' => 'test'],
            ],
        ], [
            Rules::make('foo', false)->arrayType(),
            Rules::make('foo.*.name')->required(),
        ]);
        self::assertSame([
            'foo' => [['name' => 'test']],
        ], $v->getValidatedData());
    }

    /**
     * @test
     */
    public function pipe_rule(): void
    {
        $v = $this->makeValidator(
            ['foo' => [1, 2, 3]],
            [
                Rules::make('foo.*')
                    ->pipe(static function (EntryPipeline $pipeline, Entry $entry) {
                        switch ($entry->getValue()) {
                            case 2:
                                $pipeline->insertNext(fn(Rules $rules) => $rules->booleanType());
                                break;
                            case 3:
                                $pipeline->insertNext(fn(Rules $rules) => $rules->min(4));
                                break;
                        }
                    })
                    ->min(2),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame(3, $v->getMessages()->count());
        self::assertSame('foo.0 should be min 2', $v->getMessages()->first('foo.0'));
        self::assertSame('foo.1 must be boolean', $v->getMessages()->first('foo.1'));
        self::assertSame('foo.2 should be min 4', $v->getMessages()->first('foo.2'));
    }

    /**
     * @test
     */
    public function same_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'boom'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'bar'], [Rules::make('foo')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1e2', 'baz' => '100'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => null, 'baz' => null], [Rules::make('foo')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['boom'], 'baz' => ['boom']], [Rules::make('foo.*')->same('baz.*')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['boom'], 'baz' => 'boom'], [Rules::make('foo.*')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['foo' => true, 'baz' => 'on'], [
            Rules::make('foo')->same('baz'),
            Rules::make('baz')->castToBoolean(),
        ]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');
    }

    /**
     * @test
     */
    public function starts_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('hello')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('world')]);
        $this->assertValidationFail($v, 'foo', 'foo must start with world');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('http')]);
        $this->assertValidationFail($v, 'foo', 'foo must start with http');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith(['https', 'http'])]);
        $this->assertValidationFail($v, 'foo', 'foo must start with https, http');
    }

    /**
     * @test
     */
    public function when_rule(): void
    {
        $v = $this->makeValidator(
            [],
            [
                rule('foo')->when(true, static function (Rules $rules) {
                    $rules->required();
                }),
            ]
        );
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(
            [],
            [
                rule('foo')->when(false, static function (Rules $rules) {
                    $rules->required();
                }),
            ]
        );
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function set_value_if_missing_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->setValueIfMissing('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('missing', $data['foo']);

        $v = $this->makeValidator(['foo' => 'shoo'], [Rules::make('foo')->setValueIfMissing('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('shoo', $data['foo']);
    }

    /**
     * @test
     */
    public function sometimes_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->sometimes()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function string_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->stringType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->stringType()]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => 'asd'], [Rules::make('foo')->stringType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->stringType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be string');
    }

    /**
     * @test
     * @dataProvider validUuidList
     *
     * @param string $uuid
     */
    public function uuid_valid_rule(string $uuid): void
    {
        $v = $this->makeValidator(['foo' => $uuid], [Rules::make('foo')->uuid()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     * @dataProvider invalidUuidList
     *
     * @param string $uuid
     */
    public function uuid_invalid_rule(string $uuid): void
    {
        $v = $this->makeValidator(['foo' => $uuid], [Rules::make('foo')->uuid()]);
        $this->assertValidationFail($v, 'foo', 'foo must be uuid');
    }

    public function validUuidList(): array
    {
        return [
            ['a0a2a2d2-0b87-4a18-83f2-2529882be2de'],
            ['145a1e72-d11d-11e8-a8d5-f2801f1b9fd1'],
            ['00000000-0000-0000-0000-000000000000'],
            ['e60d3f48-95d7-4d8d-aad0-856f29a27da2'],
            ['ff6f8cb0-c57d-11e1-9b21-0800200c9a66'],
            ['ff6f8cb0-c57d-21e1-9b21-0800200c9a66'],
            ['ff6f8cb0-c57d-31e1-9b21-0800200c9a66'],
            ['ff6f8cb0-c57d-41e1-9b21-0800200c9a66'],
            ['ff6f8cb0-c57d-51e1-9b21-0800200c9a66'],
            ['FF6F8CB0-C57D-11E1-9B21-0800200C9A66'],
        ];
    }

    public function invalidUuidList(): array
    {
        return [
            ['not a valid uuid so we can test this'],
            ['zf6f8cb0-c57d-11e1-9b21-0800200c9a66'],
            ['145a1e72-d11d-11e8-a8d5-f2801f1b9fd1' . \PHP_EOL],
            ['145a1e72-d11d-11e8-a8d5-f2801f1b9fd1 '],
            [' 145a1e72-d11d-11e8-a8d5-f2801f1b9fd1'],
            ['145a1e72-d11d-11e8-a8d5-f2z01f1b9fd1'],
            ['3f6f8cb0-c57d-11e1-9b21-0800200c9a6'],
            ['af6f8cb-c57d-11e1-9b21-0800200c9a66'],
            ['af6f8cb0c57d11e19b210800200c9a66'],
            ['ff6f8cb0-c57da-51e1-9b21-0800200c9a66'],
        ];
    }

    protected function assertValidationFail(Validator $validator, string $key, string $message): void
    {
        self::assertFalse($validator->passes());
        self::assertSame(1, $validator->getMessages()->count());
        self::assertSame($message, $validator->getMessages()->first($key));
    }

    protected function makeValidator(array $data, array $rules): Validator
    {
        return new Validator(new TestMessageResolver([
            'active_url' => ':attribute must be an active url',
            'accepted' => ':attribute must be accepted',
            'array_type' => ':attribute must be array',
            'alpha' => ':attribute must be alpha',
            'alpha_dash' => ':attribute must be alpha dash',
            'alpha_num' => ':attribute must be alpha num',
            'date_after' => ':attribute must be after :other',
            'date_before' => ':attribute must be before :other',
            'boolean_like' => ':attribute must be boolean like',
            'boolean_type' => ':attribute must be boolean',
            'confirmed' => ':attribute must be confirmed',
            'date_format' => ':attribute must match :format',
            'ends_with' => ':attribute must end with :values',
            'filled' => ':attribute must be filled',
            'in' => ':attribute must be in array',
            'integer_type' => ':attribute must be integer',
            'ip_address' => ':attribute must be a valid ip address',
            'max.array' => ':attribute should contain max :max items',
            'max.numeric' => ':attribute should be max :max',
            'max.string' => ':attribute should be max :max length',
            'min.array' => ':attribute should contain :min items',
            'min.numeric' => ':attribute should be min :min',
            'min.string' => ':attribute should be min :min length',
            'numeric' => ':attribute must be numeric',
            'not_regex' => ':attribute must not match regex',
            'not_in' => ':attribute must not be in array',
            'present' => ':attribute must be present',
            'required' => ':attribute is required',
            'regex' => ':attribute must match regex',
            'same' => ':attribute and :other must match',
            'starts_with' => ':attribute must start with :values',
            'string_type' => ':attribute must be string',
            'uuid' => ':attribute must be uuid',
        ]), $data, $rules);
    }
}
