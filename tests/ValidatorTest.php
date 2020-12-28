<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules;
use DonnySim\Validation\Tests\Stubs\RuleStub;
use DonnySim\Validation\Tests\Stubs\TestMessageResolver;
use DonnySim\Validation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_replaces_paths_with_custom_attribute_names(): void
    {
        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()], ['foo' => 'FOO']);
        $this->assertValidationFail($v, 'foo', 'FOO is required');

        $v = $this->makeValidator(['foo' => [null]], [Rules::make('foo.*')->required()], ['foo' => 'FOO']);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 is required');

        $v = $this->makeValidator(['foo' => [null]], [Rules::make('foo.*')->required()], ['foo.*' => 'FOO']);
        $this->assertValidationFail($v, 'foo.0', 'FOO is required');

        $v = $this->makeValidator(['foo' => [null, null]], [Rules::make('foo.*')->required()], ['foo.1' => 'FOO']);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 is required', 2);
        $this->assertValidationFail($v, 'foo.1', 'FOO is required', 2);

        $v = $this->makeValidator(['foo' => [null, null]], [Rules::make('foo.*')->required()], ['foo.*' => 'BAR', 'foo.1' => 'FOO']);
        $this->assertValidationFail($v, 'foo.0', 'BAR is required', 2);
        $this->assertValidationFail($v, 'foo.1', 'FOO is required', 2);
    }

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
    public function date_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '01/01/2000'], [Rules::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => new DateTime()], [Rules::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => new DateTimeImmutable()], [Rules::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '1325376000'], [Rules::make('x')->date()]);
        $this->assertValidationFail($v, 'x', 'x must be a date');

        $v = $this->makeValidator(['x' => ['Not', 'a', 'date']], [Rules::make('x')->date()]);
        $this->assertValidationFail($v, 'x', 'x must be a date');
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
    public function date_before_and_after_or_equal_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-16')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-14')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 2012-01-14');

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateBeforeOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateBeforeOrEqual('14/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 14/01/2012');

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('tomorrow', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal yesterday');

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-14')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-16')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 2012-01-16');

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateAfterOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateAfterOrEqual('16/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 16/01/2012');

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('yesterday', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal tomorrow');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s')
                    ->dateAfterOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                Rules::make('x')
                    ->dateFormat('Y-m-d H:i:s')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00')
                    ->dateAfterOrEqual('2012-01-01 17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateBeforeOrEqual('2012-01-01 17:43:59', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 2012-01-01 17:43:59');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateAfterOrEqual('2012-01-01 17:44:01', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 2012-01-01 17:44:01');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('17:44:00', 'H:i:s')
                    ->dateAfterOrEqual('17:44:00', 'H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                Rules::make('x')
                    ->dateFormat('H:i:s')
                    ->dateBeforeOrEqual('17:44:00')
                    ->dateAfterOrEqual('17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateBeforeOrEqual('17:43:59', 'H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 17:43:59');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateAfterOrEqual('17:44:01', 'H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 17:44:01');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('17:44', 'H:i')
                    ->dateAfterOrEqual('17:44', 'H:i'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateBeforeOrEqual('17:43', 'H:i')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 17:43');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateAfterOrEqual('17:45', 'H:i')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 17:45');
    }

    /**
     * @test
     */
    public function date_equal_rule(): void
    {
        \date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [Rules::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateEqual('2001-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2001-01-01');

        $v = $this->makeValidator(
            [
                'starts' => new DateTime('2000-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                Rules::make('ends')->dateEqual(Rules::reference('starts')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('Y-m-d')], [Rules::make('x')->dateEqual('today')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('Y-m-d')], [Rules::make('x')->dateEqual('yesterday')]);
        $this->assertValidationFail($v, 'x', 'x must be equal yesterday');

        $v = $this->makeValidator(['x' => \date('Y-m-d')], [Rules::make('x')->dateEqual('tomorrow')]);
        $this->assertValidationFail($v, 'x', 'x must be equal tomorrow');

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be equal yesterday');

        $v = $this->makeValidator(['x' => \date('d/m/Y')], [Rules::make('x')->dateEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be equal tomorrow');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:43:59')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2012-01-01 17:43:59');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2012-01-01 17:44:01');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:43:59')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:43:59');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:44:01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:44:01');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:44')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:43')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:43');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:45')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:45');
    }

    /**
     * @test
     */
    public function distinct_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['foo', 'foo']], [Rules::make('foo.*')->distinct()]);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 2);

        $v = $this->makeValidator(['foo' => ['foo', 'bar']], [Rules::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 1]]], [Rules::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, 'foo.bar.id', 'foo.bar.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.baz.id', 'foo.baz.id contains duplicate value', 2);

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 'qux'], 'baz' => ['id' => 'QUX']]], [Rules::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 2]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1, 'nested' => ['id' => 1]]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 2]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]], [Rules::make('cat.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]]], [Rules::make('cat.sub.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['foo' => ['foo', 'bar'], 'bar' => ['foo', 'bar']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 1]]], [Rules::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id contains duplicate value', 2);

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 1]]]]], [Rules::make('cat.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, 'cat.0.prod.0.id', 'cat.0.prod.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'cat.1.prod.0.id', 'cat.1.prod.0.id contains duplicate value', 2);

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 2]]], ['prod' => [['id' => 2]]]]]], [Rules::make('cat.sub.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, 'cat.sub.0.prod.0.id', 'cat.sub.0.prod.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'cat.sub.1.prod.0.id', 'cat.sub.1.prod.0.id contains duplicate value', 2);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'baz']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 2);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'bar']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 4);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 4);
        $this->assertValidationFail($v, 'bar.0', 'bar.0 contains duplicate value', 4);
        $this->assertValidationFail($v, 'bar.1', 'bar.1 contains duplicate value', 4);
    }

    /**
     * @test
     */
    public function between_rule(): void
    {
        \ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ancfs'], [Rules::make('foo')->between(3, 5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => '4'], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5 chars');

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->between(3, 5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4, 5, 6]], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5 items');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 6], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->between(3.1, '3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->between('3.1', 3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->between('3.0', '3.5')]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 3.5');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between(4, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4 and 5');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between(4.1, 5.1)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4.1 and 5.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between('4.1', '5.1')]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4.1 and 5.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->between('4.1', '4.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->between('3.1', '4.1')]);
        self::assertTrue($v->passes());

        // TODO
//        $v = $this->makeValidator(['foo' => 3.5], [Rules::make('foo')->between(3.4, 3.6)]);
//        self::assertTrue($v->passes());
//
//        $v = $this->makeValidator(['foo' => 3.5], [Rules::make('foo')->between(3.0, 3.4)]);
//        $this->assertValidationFail($v, 'foo', 'foo must be between 3.0 and 3.4');

        // TODO
//        $file = $this->getMockBuilder(File::class)->onlyMethods(['getSize'])->setConstructorArgs([__FILE__, false])->getMock();
//        $file->expects($this->any())->method('getSize')->willReturn(3072);
//        $v = new Validator($trans, ['photo' => $file], ['photo' => 'Between:1,5']);
//        $this->assertTrue($v->passes());
//
//        $file = $this->getMockBuilder(File::class)->onlyMethods(['getSize'])->setConstructorArgs([__FILE__, false])->getMock();
//        $file->expects($this->any())->method('getSize')->willReturn(4072);
//        $v = new Validator($trans, ['photo' => $file], ['photo' => 'Between:1,2']);
//        $this->assertFalse($v->passes());
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
    public function different_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'boom'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => null], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'bar'], [Rules::make('foo')->different('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo must be different from baz');

        $v = $this->makeValidator(['foo' => '1e2', 'baz' => '100'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function digits_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->digits(1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [Rules::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [Rules::make('foo')->digits(5)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 5 digits');

        $v = $this->makeValidator(['foo' => '123'], [Rules::make('foo')->digits(200)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 200 digits');

        $v = $this->makeValidator(['foo' => '+2.37'], [Rules::make('foo')->digits(200)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 200 digits');

        $v = $this->makeValidator(['foo' => '2e7'], [Rules::make('foo')->digits(3)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 3 digits');
    }

    /**
     * @test
     */
    public function digits_between_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->digitsBetween(1, 2)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [Rules::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [Rules::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 6');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->digitsBetween(1, 10)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 10');

        $v = $this->makeValidator(['foo' => '123'], [Rules::make('foo')->digitsBetween(4, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 4 and 5');

        $v = $this->makeValidator(['foo' => '+12.3'], [Rules::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 6');
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
    public function json_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->json()]);
        $this->assertValidationFail($v, 'foo', 'foo must be json');

        $v = $this->makeValidator(['foo' => '[]'], [Rules::make('foo')->json()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '{"name":"John","age":"34"}'], [Rules::make('foo')->json()]);
        self::assertTrue($v->passes());
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
        $this->makeValidator([], [Rules::make('foo')->rule(new Rules\Distinct())]);

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
    public function rules_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->rules([new Rules\Nullable(), new Rules\Required()])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->rules([new Rules\Nullable(), new Rules\Required()])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function less_than_rule(): void
    {
        \ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => '211'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => '22'], [Rules::make('foo')->lessThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain less than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThan(3.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3.1');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThan('3.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3.1');

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->lessThan('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan(2.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan('2.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->lessThan('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 4.1');
    }

    /**
     * @test
     */
    public function less_than_or_equal_rule(): void
    {
        \ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '211'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '22'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain less than 2 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->lessThanOrEqual('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual(2.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual('2.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->lessThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_rule(): void
    {
        \ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 'abc'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 'ab'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->greaterThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain more than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThan(3.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3.1');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThan('3.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3.1');

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->greaterThan('3.0')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan(4)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan(4.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '4.2'], [Rules::make('foo')->numeric()->greaterThan('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_or_equal_rule(): void
    {
        \ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 'abc'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ab'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain more than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->greaterThanOrEqual('3.0')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual(4)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual(4.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
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
        $v = $this->makeValidator([], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->numeric()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->numeric()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric');

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->numeric()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric');

        $v = $this->makeValidator(['foo' => '1.23'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1.23'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1.1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.1], [Rules::make('foo')->numeric()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function numeric_float_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => '1.23'], [Rules::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1.23'], [Rules::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1'], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => -1], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric float');

        $v = $this->makeValidator(['foo' => -1.1], [Rules::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.1], [Rules::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function numeric_integer_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => new \stdClass()], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => '1.23'], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => '-1.23'], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => '-1'], [Rules::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1], [Rules::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1.1], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');

        $v = $this->makeValidator(['foo' => 1.1], [Rules::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, 'foo', 'foo must be numeric integer');
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
                                $pipeline->insertNext(fn(Rules $rules) => $rules->greaterThanOrEqual(4));
                                break;
                        }
                    })
                    ->greaterThanOrEqual(2),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame(3, $v->getMessages()->count());
        self::assertSame('foo.0 should be greater than 2', $v->getMessages()->first('foo.0'));
        self::assertSame('foo.1 must be boolean', $v->getMessages()->first('foo.1'));
        self::assertSame('foo.2 should be greater than 4', $v->getMessages()->first('foo.2'));
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
                Rules::make('foo')->when(true, static function (Rules $rules) {
                    $rules->required();
                }),
            ]
        );
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(
            [],
            [
                Rules::make('foo')->when(false, static function (Rules $rules) {
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
     */
    public function timezone(): void
    {
        $v = $this->makeValidator(['foo' => 'India'], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');

        $v = $this->makeValidator(['foo' => 'Cairo'], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');

        $v = $this->makeValidator(['foo' => 'UTC'], [Rules::make('foo')->timezone()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'Africa/Windhoek'], [Rules::make('foo')->timezone()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'africa/windhoek'], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');

        $v = $this->makeValidator(['foo' => 'GMT'], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');

        $v = $this->makeValidator(['foo' => 'GB'], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');

        $v = $this->makeValidator(['foo' => ['this_is_not_a_timezone']], [Rules::make('foo')->timezone()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a timezone');
    }

    /**
     * @test
     * @dataProvider validUrls
     *
     * @param string $url
     */
    public function url_valid_rule(string $url): void
    {
        $v = $this->makeValidator(['foo' => $url], [Rules::make('foo')->url()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     * @dataProvider invalidUrls
     *
     * @param string $url
     */
    public function url_invalid_rule(string $url): void
    {
        $v = $this->makeValidator(['foo' => $url], [Rules::make('foo')->url()]);
        self::assertFalse($v->passes());
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

    public function validUrls(): array
    {
        return [
            ['aaa://fully.qualified.domain/path'],
            ['aaas://fully.qualified.domain/path'],
            ['about://fully.qualified.domain/path'],
            ['acap://fully.qualified.domain/path'],
            ['acct://fully.qualified.domain/path'],
            ['acr://fully.qualified.domain/path'],
            ['adiumxtra://fully.qualified.domain/path'],
            ['afp://fully.qualified.domain/path'],
            ['afs://fully.qualified.domain/path'],
            ['aim://fully.qualified.domain/path'],
            ['apt://fully.qualified.domain/path'],
            ['attachment://fully.qualified.domain/path'],
            ['aw://fully.qualified.domain/path'],
            ['barion://fully.qualified.domain/path'],
            ['beshare://fully.qualified.domain/path'],
            ['bitcoin://fully.qualified.domain/path'],
            ['blob://fully.qualified.domain/path'],
            ['bolo://fully.qualified.domain/path'],
            ['callto://fully.qualified.domain/path'],
            ['cap://fully.qualified.domain/path'],
            ['chrome://fully.qualified.domain/path'],
            ['chrome-extension://fully.qualified.domain/path'],
            ['cid://fully.qualified.domain/path'],
            ['coap://fully.qualified.domain/path'],
            ['coaps://fully.qualified.domain/path'],
            ['com-eventbrite-attendee://fully.qualified.domain/path'],
            ['content://fully.qualified.domain/path'],
            ['crid://fully.qualified.domain/path'],
            ['cvs://fully.qualified.domain/path'],
            ['data://fully.qualified.domain/path'],
            ['dav://fully.qualified.domain/path'],
            ['dict://fully.qualified.domain/path'],
            ['dlna-playcontainer://fully.qualified.domain/path'],
            ['dlna-playsingle://fully.qualified.domain/path'],
            ['dns://fully.qualified.domain/path'],
            ['dntp://fully.qualified.domain/path'],
            ['dtn://fully.qualified.domain/path'],
            ['dvb://fully.qualified.domain/path'],
            ['ed2k://fully.qualified.domain/path'],
            ['example://fully.qualified.domain/path'],
            ['facetime://fully.qualified.domain/path'],
            ['fax://fully.qualified.domain/path'],
            ['feed://fully.qualified.domain/path'],
            ['feedready://fully.qualified.domain/path'],
            ['file://fully.qualified.domain/path'],
            ['filesystem://fully.qualified.domain/path'],
            ['finger://fully.qualified.domain/path'],
            ['fish://fully.qualified.domain/path'],
            ['ftp://fully.qualified.domain/path'],
            ['geo://fully.qualified.domain/path'],
            ['gg://fully.qualified.domain/path'],
            ['git://fully.qualified.domain/path'],
            ['gizmoproject://fully.qualified.domain/path'],
            ['go://fully.qualified.domain/path'],
            ['gopher://fully.qualified.domain/path'],
            ['gtalk://fully.qualified.domain/path'],
            ['h323://fully.qualified.domain/path'],
            ['ham://fully.qualified.domain/path'],
            ['hcp://fully.qualified.domain/path'],
            ['http://fully.qualified.domain/path'],
            ['https://fully.qualified.domain/path'],
            ['iax://fully.qualified.domain/path'],
            ['icap://fully.qualified.domain/path'],
            ['icon://fully.qualified.domain/path'],
            ['im://fully.qualified.domain/path'],
            ['imap://fully.qualified.domain/path'],
            ['info://fully.qualified.domain/path'],
            ['iotdisco://fully.qualified.domain/path'],
            ['ipn://fully.qualified.domain/path'],
            ['ipp://fully.qualified.domain/path'],
            ['ipps://fully.qualified.domain/path'],
            ['irc://fully.qualified.domain/path'],
            ['irc6://fully.qualified.domain/path'],
            ['ircs://fully.qualified.domain/path'],
            ['iris://fully.qualified.domain/path'],
            ['iris.beep://fully.qualified.domain/path'],
            ['iris.lwz://fully.qualified.domain/path'],
            ['iris.xpc://fully.qualified.domain/path'],
            ['iris.xpcs://fully.qualified.domain/path'],
            ['itms://fully.qualified.domain/path'],
            ['jabber://fully.qualified.domain/path'],
            ['jar://fully.qualified.domain/path'],
            ['jms://fully.qualified.domain/path'],
            ['keyparc://fully.qualified.domain/path'],
            ['lastfm://fully.qualified.domain/path'],
            ['ldap://fully.qualified.domain/path'],
            ['ldaps://fully.qualified.domain/path'],
            ['magnet://fully.qualified.domain/path'],
            ['mailserver://fully.qualified.domain/path'],
            ['mailto://fully.qualified.domain/path'],
            ['maps://fully.qualified.domain/path'],
            ['market://fully.qualified.domain/path'],
            ['message://fully.qualified.domain/path'],
            ['mid://fully.qualified.domain/path'],
            ['mms://fully.qualified.domain/path'],
            ['modem://fully.qualified.domain/path'],
            ['ms-help://fully.qualified.domain/path'],
            ['ms-settings://fully.qualified.domain/path'],
            ['ms-settings-airplanemode://fully.qualified.domain/path'],
            ['ms-settings-bluetooth://fully.qualified.domain/path'],
            ['ms-settings-camera://fully.qualified.domain/path'],
            ['ms-settings-cellular://fully.qualified.domain/path'],
            ['ms-settings-cloudstorage://fully.qualified.domain/path'],
            ['ms-settings-emailandaccounts://fully.qualified.domain/path'],
            ['ms-settings-language://fully.qualified.domain/path'],
            ['ms-settings-location://fully.qualified.domain/path'],
            ['ms-settings-lock://fully.qualified.domain/path'],
            ['ms-settings-nfctransactions://fully.qualified.domain/path'],
            ['ms-settings-notifications://fully.qualified.domain/path'],
            ['ms-settings-power://fully.qualified.domain/path'],
            ['ms-settings-privacy://fully.qualified.domain/path'],
            ['ms-settings-proximity://fully.qualified.domain/path'],
            ['ms-settings-screenrotation://fully.qualified.domain/path'],
            ['ms-settings-wifi://fully.qualified.domain/path'],
            ['ms-settings-workplace://fully.qualified.domain/path'],
            ['msnim://fully.qualified.domain/path'],
            ['msrp://fully.qualified.domain/path'],
            ['msrps://fully.qualified.domain/path'],
            ['mtqp://fully.qualified.domain/path'],
            ['mumble://fully.qualified.domain/path'],
            ['mupdate://fully.qualified.domain/path'],
            ['mvn://fully.qualified.domain/path'],
            ['news://fully.qualified.domain/path'],
            ['nfs://fully.qualified.domain/path'],
            ['ni://fully.qualified.domain/path'],
            ['nih://fully.qualified.domain/path'],
            ['nntp://fully.qualified.domain/path'],
            ['notes://fully.qualified.domain/path'],
            ['oid://fully.qualified.domain/path'],
            ['opaquelocktoken://fully.qualified.domain/path'],
            ['pack://fully.qualified.domain/path'],
            ['palm://fully.qualified.domain/path'],
            ['paparazzi://fully.qualified.domain/path'],
            ['pkcs11://fully.qualified.domain/path'],
            ['platform://fully.qualified.domain/path'],
            ['pop://fully.qualified.domain/path'],
            ['pres://fully.qualified.domain/path'],
            ['prospero://fully.qualified.domain/path'],
            ['proxy://fully.qualified.domain/path'],
            ['psyc://fully.qualified.domain/path'],
            ['query://fully.qualified.domain/path'],
            ['redis://fully.qualified.domain/path'],
            ['rediss://fully.qualified.domain/path'],
            ['reload://fully.qualified.domain/path'],
            ['res://fully.qualified.domain/path'],
            ['resource://fully.qualified.domain/path'],
            ['rmi://fully.qualified.domain/path'],
            ['rsync://fully.qualified.domain/path'],
            ['rtmfp://fully.qualified.domain/path'],
            ['rtmp://fully.qualified.domain/path'],
            ['rtsp://fully.qualified.domain/path'],
            ['rtsps://fully.qualified.domain/path'],
            ['rtspu://fully.qualified.domain/path'],
            ['s3://fully.qualified.domain/path'],
            ['secondlife://fully.qualified.domain/path'],
            ['service://fully.qualified.domain/path'],
            ['session://fully.qualified.domain/path'],
            ['sftp://fully.qualified.domain/path'],
            ['sgn://fully.qualified.domain/path'],
            ['shttp://fully.qualified.domain/path'],
            ['sieve://fully.qualified.domain/path'],
            ['sip://fully.qualified.domain/path'],
            ['sips://fully.qualified.domain/path'],
            ['skype://fully.qualified.domain/path'],
            ['smb://fully.qualified.domain/path'],
            ['sms://fully.qualified.domain/path'],
            ['smtp://fully.qualified.domain/path'],
            ['snews://fully.qualified.domain/path'],
            ['snmp://fully.qualified.domain/path'],
            ['soap.beep://fully.qualified.domain/path'],
            ['soap.beeps://fully.qualified.domain/path'],
            ['soldat://fully.qualified.domain/path'],
            ['spotify://fully.qualified.domain/path'],
            ['ssh://fully.qualified.domain/path'],
            ['steam://fully.qualified.domain/path'],
            ['stun://fully.qualified.domain/path'],
            ['stuns://fully.qualified.domain/path'],
            ['submit://fully.qualified.domain/path'],
            ['svn://fully.qualified.domain/path'],
            ['tag://fully.qualified.domain/path'],
            ['teamspeak://fully.qualified.domain/path'],
            ['tel://fully.qualified.domain/path'],
            ['teliaeid://fully.qualified.domain/path'],
            ['telnet://fully.qualified.domain/path'],
            ['tftp://fully.qualified.domain/path'],
            ['things://fully.qualified.domain/path'],
            ['thismessage://fully.qualified.domain/path'],
            ['tip://fully.qualified.domain/path'],
            ['tn3270://fully.qualified.domain/path'],
            ['turn://fully.qualified.domain/path'],
            ['turns://fully.qualified.domain/path'],
            ['tv://fully.qualified.domain/path'],
            ['udp://fully.qualified.domain/path'],
            ['unreal://fully.qualified.domain/path'],
            ['urn://fully.qualified.domain/path'],
            ['ut2004://fully.qualified.domain/path'],
            ['vemmi://fully.qualified.domain/path'],
            ['ventrilo://fully.qualified.domain/path'],
            ['videotex://fully.qualified.domain/path'],
            ['view-source://fully.qualified.domain/path'],
            ['wais://fully.qualified.domain/path'],
            ['webcal://fully.qualified.domain/path'],
            ['ws://fully.qualified.domain/path'],
            ['wss://fully.qualified.domain/path'],
            ['wtai://fully.qualified.domain/path'],
            ['wyciwyg://fully.qualified.domain/path'],
            ['xcon://fully.qualified.domain/path'],
            ['xcon-userid://fully.qualified.domain/path'],
            ['xfire://fully.qualified.domain/path'],
            ['xmlrpc.beep://fully.qualified.domain/path'],
            ['xmlrpc.beeps://fully.qualified.domain/path'],
            ['xmpp://fully.qualified.domain/path'],
            ['xri://fully.qualified.domain/path'],
            ['ymsgr://fully.qualified.domain/path'],
            ['z39.50://fully.qualified.domain/path'],
            ['z39.50r://fully.qualified.domain/path'],
            ['z39.50s://fully.qualified.domain/path'],
            ['http://a.pl'],
            ['http://localhost/url.php'],
            ['http://local.dev'],
            ['http://google.com'],
            ['http://www.google.com'],
            ['http://goog_le.com'],
            ['https://google.com'],
            ['http://illuminate.dev'],
            ['http://localhost'],
            ['https://laravel.com/?'],
            ['http://президент.рф/'],
            ['http://스타벅스코리아.com'],
            ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
            ['https://laravel.com?'],
            ['https://laravel.com?q=1'],
            ['https://laravel.com/?q=1'],
            ['https://laravel.com#'],
            ['https://laravel.com#fragment'],
            ['https://laravel.com/#fragment'],
            ['https://domain1'],
            ['https://domain12/'],
            ['https://domain12#fragment'],
            ['https://domain1/path'],
            ['https://domain.com/path/%2528failed%2526?param=1#fragment'],
        ];
    }

    public function invalidUrls(): array
    {
        return [
            ['aslsdlks'],
            ['google.com'],
            ['://google.com'],
            ['http ://google.com'],
            ['http:/google.com'],
            ['http://google.com::aa'],
            ['http://google.com:aa'],
            ['http://127.0.0.1:aa'],
            ['http://[::1'],
            ['foo://bar'],
            ['javascript://test%0Aalert(321)'],
        ];
    }

    protected function assertValidationFail(Validator $validator, string $key, string $message, int $errors = 1): void
    {
        self::assertFalse($validator->passes(), 'Validation should fail but passed.');
        self::assertSame($errors, $validator->getMessages()->count());
        self::assertSame($message, $validator->getMessages()->first($key));
    }

    protected function makeValidator(array $data, array $rules, array $overrides = []): Validator
    {
        $validator = new Validator(new TestMessageResolver([
            'accepted' => ':attribute must be accepted',
            'active_url' => ':attribute must be an active url',
            'alpha' => ':attribute must be alpha',
            'alpha_dash' => ':attribute must be alpha dash',
            'alpha_num' => ':attribute must be alpha num',
            'array_type' => ':attribute must be array',
            'between.array' => ':attribute must be between :min and :max items',
            'between.numeric' => ':attribute must be between :min and :max',
            'between.string' => ':attribute must be between :min and :max chars',
            'boolean_like' => ':attribute must be boolean like',
            'boolean_type' => ':attribute must be boolean',
            'confirmed' => ':attribute must be confirmed',
            'date' => ':attribute must be a date',
            'date_after' => ':attribute must be after :date',
            'date_after_or_equal' => ':attribute must be after or equal :date',
            'date_before' => ':attribute must be before :date',
            'date_before_or_equal' => ':attribute must be before or equal :date',
            'date_equal' => ':attribute must be equal :date',
            'date_format' => ':attribute must match :format',
            'different' => ':attribute must be different from :other',
            'digits' => ':attribute must have :digits digits',
            'digits_between' => ':attribute must have digits between :min and :max',
            'distinct' => ':attribute contains duplicate value',
            'ends_with' => ':attribute must end with :values',
            'filled' => ':attribute must be filled',
            'greater_than.array' => ':attribute should contain more than :other items',
            'greater_than.numeric' => ':attribute should be greater than :other',
            'greater_than.string' => ':attribute should be greater than :other length',
            'greater_than_or_equal.array' => ':attribute should contain :other items',
            'greater_than_or_equal.numeric' => ':attribute should be min :other',
            'greater_than_or_equal.string' => ':attribute should be min :other length',
            'in' => ':attribute must be in array',
            'integer_type' => ':attribute must be integer',
            'ip_address' => ':attribute must be a valid ip address',
            'json' => ':attribute must be json',
            'less_than.array' => ':attribute should contain less than :other items',
            'less_than.numeric' => ':attribute should be less than :other',
            'less_than.string' => ':attribute should be less than :other length',
            'less_than_or_equal.array' => ':attribute should contain max :other items',
            'less_than_or_equal.numeric' => ':attribute should be max :other',
            'less_than_or_equal.string' => ':attribute should be max :other length',
            'not_in' => ':attribute must not be in array',
            'not_regex' => ':attribute must not match regex',
            'numeric.float' => ':attribute must be numeric float',
            'numeric.integer' => ':attribute must be numeric integer',
            'numeric.mixed' => ':attribute must be numeric',
            'present' => ':attribute must be present',
            'regex' => ':attribute must match regex',
            'required' => ':attribute is required',
            'same' => ':attribute and :other must match',
            'starts_with' => ':attribute must start with :values',
            'string_type' => ':attribute must be string',
            'timezone' => ':attribute must be a timezone',
            'uuid' => ':attribute must be uuid',
        ]), $data, $rules);

        return $validator->override($overrides);
    }
}
