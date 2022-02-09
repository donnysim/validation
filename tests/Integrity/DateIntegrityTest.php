<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Integrity;

use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;
use function date;
use function date_default_timezone_set;

final class DateIntegrityTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function date_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '01/01/2000'], [RuleSet::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => new DateTime()], [RuleSet::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => new DateTimeImmutable()], [RuleSet::make('x')->date()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '1325376000'], [RuleSet::make('x')->date()]);
        $this->assertValidationFail($v, ['x' => ['x must be a date']]);

        $v = $this->makeValidator(['x' => ['Not', 'a', 'date']], [RuleSet::make('x')->date()]);
        $this->assertValidationFail($v, ['x' => ['x must be a date']]);
    }

    /**
     * @test
     */
    public function date_before_and_after_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->dateBefore('2000-01-01')]);
        $this->assertValidationFail($v, ['x' => ['x must be before 2000-01-01']]);

        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [RuleSet::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => [new Carbon('2000-01-01')]], [RuleSet::make('x')->dateBefore('2012-01-01')]);
        $this->assertValidationFail($v, ['x' => ['x must be before 2012-01-01']]);

        $v = $this->makeValidator(['x' => '2012-01-01'], [RuleSet::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => ['2012-01-01']], [RuleSet::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, ['x' => ['x must be after 2000-01-01']]);

        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, ['x' => ['x must be after 2000-01-01']]);

        $v = $this->makeValidator(['x' => new Carbon('2012-01-01')], [RuleSet::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                RuleSet::make('start')->dateAfter('2000-01-01'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                RuleSet::make('start')->dateAfter('2000-01-01'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        $this->assertValidationFail($v, ['ends' => ['ends must be after 2012-01-01']]);

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 2000-01-01', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after 2012-01-01', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(['x' => new DateTime('2000-01-01')], [RuleSet::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2000-01-01'),
                'ends' => new Carbon('2013-01-01'),
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => new DateTime('2013-01-01'),
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2012-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                RuleSet::make('start')->dateAfter('2000-01-01'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        $this->assertValidationFail($v, ['ends' => ['ends must be after 2012-01-01 00:00:00']]);

        $v = $this->makeValidator(
            [
                'start' => 'today',
                'ends' => 'tomorrow',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:43:59'],
            [RuleSet::make('x')->dateBefore('2012-01-01 17:44')->dateAfter('2012-01-01 17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:01'],
            [RuleSet::make('x')->dateBefore('2012-01-01 17:44:02')->dateAfter('2012-01-01 17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [RuleSet::make('x')->dateBefore('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before 2012-01-01 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [RuleSet::make('x')->dateAfter('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after 2012-01-01 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '17:43:59'],
            [RuleSet::make('x')->dateBefore('17:44')->dateAfter('17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:01'],
            [RuleSet::make('x')->dateBefore('17:44:02')->dateAfter('17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [RuleSet::make('x')->dateBefore('17:44:00')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [RuleSet::make('x')->dateAfter('17:44:00')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after 17:44:00']]);
    }

    /**
     * @test
     */
    public function date_format_rule(): void
    {
        $v = $this->makeValidator(['foo' => '2000-01-01'], [RuleSet::make('foo')->dateFormat('Y-m-d')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '01/01/2001'], [RuleSet::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match Y-m-d']]);

        $v = $this->makeValidator(['foo' => '22000-01-01'], [RuleSet::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match Y-m-d']]);

        $v = $this->makeValidator(['foo' => '00-01-01'], [RuleSet::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match Y-m-d']]);

        $v = $this->makeValidator(['foo' => ['Not', 'a', 'date']], [RuleSet::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match Y-m-d']]);

        $v = $this->makeValidator(['foo' => '2013-02'], [RuleSet::make('foo')->dateFormat('Y-m')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Atlantic/Azores'], [RuleSet::make('foo')->dateFormat('Y-m-d\TH:i:se')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Z'], [RuleSet::make('foo')->dateFormat('Y-m-d\TH:i:sT')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+0000'], [RuleSet::make('foo')->dateFormat('Y-m-d\TH:i:sO')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+00:30'], [RuleSet::make('foo')->dateFormat('Y-m-d\TH:i:sP')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [RuleSet::make('foo')->dateFormat('Y-m-d H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [RuleSet::make('foo')->dateFormat('H:i:s')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match H:i:s']]);

        $v = $this->makeValidator(['foo' => '17:43:59'], [RuleSet::make('foo')->dateFormat('H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '17:43:59'], [RuleSet::make('foo')->dateFormat('H:i')]);
        $this->assertValidationFail($v, ['foo' => ['foo must match H:i']]);

        $v = $this->makeValidator(['foo' => '17:43'], [RuleSet::make('foo')->dateFormat('H:i')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [RuleSet::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, ['x' => ['x must be before 31/02/2012']]);

        $v = $this->makeValidator(['x' => ['31/12/2000']], [RuleSet::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, ['x' => ['x must be before 31/02/2012']]);

        $v = $this->makeValidator(['x' => '31/12/2000'], [RuleSet::make('x')->dateBefore('31/02/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [RuleSet::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, ['x' => ['x must be after 31/12/2000']]);

        $v = $this->makeValidator(['x' => ['31/12/2012']], [RuleSet::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, ['x' => ['x must be after 31/12/2000']]);

        $v = $this->makeValidator(['x' => '31/12/2012'], [RuleSet::make('x')->dateAfter('31/12/2000', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateAfter('01/01/2000'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be after 01/01/2000', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after start', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateAfter('31/12/2000'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be after 31/12/2000', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after start', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start'), 'd/m/Y'),
            ]
        );
        $this->assertValidationFail($v, ['ends' => ['ends must be after 31/12/2012']]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before ends', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after start', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends'), 'd/m/Y'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before ends', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after start', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateBefore(RuleSet::ref('ends'), 'd/m/Y'),
                RuleSet::make('ends')->dateAfter(RuleSet::ref('start'), 'd/m/Y'),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 31/12/2000', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after 31/12/2012', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [RuleSet::make('x')->dateAfter('yesterday', 'd/m/Y')->dateBefore('tomorrow', 'd/m/Y')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [RuleSet::make('x')->dateAfter('today', 'd/m/Y')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after today']]);

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [RuleSet::make('x')->dateBefore('today', 'd/m/Y')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before today']]);

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [RuleSet::make('x')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [RuleSet::make('x')->dateAfter('today')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after today']]);

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [RuleSet::make('x')->dateBefore('today')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before today']]);

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [RuleSet::make('x')->dateBefore('2012-01-01 17:44:01', 'Y-m-d H:i:s')->dateAfter('2012-01-01 17:43:59', 'Y-m-d H:i:s')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [RuleSet::make('x')->dateBefore('2012-01-01 17:44:00', 'Y-m-d H:i:s')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before 2012-01-01 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [RuleSet::make('x')->dateBefore('17:44:01', 'H:i:s')->dateAfter('17:43:59')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [RuleSet::make('x')->dateBefore('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [RuleSet::make('x')->dateAfter('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after 17:44:00']]);

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [RuleSet::make('x')->dateBefore('17:45', 'H:i')->dateAfter('17:43')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [RuleSet::make('x')->dateBefore('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before 17:44']]);

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [RuleSet::make('x')->dateAfter('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be after 17:44']]);
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_from_date_format_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [RuleSet::make('x')->dateFormat('d/m/Y')->dateBefore('31/02/2012')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [RuleSet::make('x')->dateFormat('d/m/Y')->dateAfter('31/12/2000')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                RuleSet::make('ends')->dateFormat('d/m/Y')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                RuleSet::make('ends')->dateFormat('d/m/Y')->dateAfter(RuleSet::ref('start')),
            ]
        );
        $this->assertValidationFail($v, ['ends' => ['ends must be after 31/12/2012']]);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                RuleSet::make('start')->dateFormat('d/m/Y')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateFormat('d/m/Y')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                RuleSet::make('start')->dateFormat('d/m/Y')->dateBefore(RuleSet::ref('ends')),
                RuleSet::make('ends')->dateFormat('d/m/Y')->dateAfter(RuleSet::ref('start')),
            ]
        );
        self::assertFalse($v->passes());
        self::assertSame('start must be before 31/12/2000', $v->resolveMessages($this->makeValidationMessageResolver())['start'][0]);
        self::assertSame('ends must be after 31/12/2012', $v->resolveMessages($this->makeValidationMessageResolver())['ends'][0]);

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [RuleSet::make('x')->dateFormat('d/m/Y')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [RuleSet::make('x')->dateFormat('d/m/Y')->dateBefore('today')]
        );
        $this->assertValidationFail($v, ['x' => ['x must be before today']]);
    }

    /**
     * @test
     */
    public function date_before_and_after_or_equal_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateBeforeOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateBeforeOrEqual('2012-01-16')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateBeforeOrEqual('2012-01-14')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal 2012-01-14']]);

        $v = $this->makeValidator(['x' => '15/01/2012'], [RuleSet::make('x')->dateBeforeOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [RuleSet::make('x')->dateBeforeOrEqual('14/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal 14/01/2012']]);

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateBeforeOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateBeforeOrEqual('tomorrow', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateBeforeOrEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal yesterday']]);

        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateAfterOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateAfterOrEqual('2012-01-14')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [RuleSet::make('x')->dateAfterOrEqual('2012-01-16')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal 2012-01-16']]);

        $v = $this->makeValidator(['x' => '15/01/2012'], [RuleSet::make('x')->dateAfterOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [RuleSet::make('x')->dateAfterOrEqual('16/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal 16/01/2012']]);

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateAfterOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateAfterOrEqual('yesterday', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateAfterOrEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal tomorrow']]);

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                RuleSet::make('x')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s')
                    ->dateAfterOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                RuleSet::make('x')
                    ->dateFormat('Y-m-d H:i:s')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00')
                    ->dateAfterOrEqual('2012-01-01 17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [RuleSet::make('x')->dateBeforeOrEqual('2012-01-01 17:43:59', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal 2012-01-01 17:43:59']]);

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [RuleSet::make('x')->dateAfterOrEqual('2012-01-01 17:44:01', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal 2012-01-01 17:44:01']]);

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                RuleSet::make('x')
                    ->dateBeforeOrEqual('17:44:00', 'H:i:s')
                    ->dateAfterOrEqual('17:44:00', 'H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                RuleSet::make('x')
                    ->dateFormat('H:i:s')
                    ->dateBeforeOrEqual('17:44:00')
                    ->dateAfterOrEqual('17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [RuleSet::make('x')->dateBeforeOrEqual('17:43:59', 'H:i:s')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal 17:43:59']]);

        $v = $this->makeValidator(['x' => '17:44:00'], [RuleSet::make('x')->dateAfterOrEqual('17:44:01', 'H:i:s')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal 17:44:01']]);

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [
                RuleSet::make('x')
                    ->dateBeforeOrEqual('17:44', 'H:i')
                    ->dateAfterOrEqual('17:44', 'H:i'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [RuleSet::make('x')->dateBeforeOrEqual('17:43', 'H:i')]);
        $this->assertValidationFail($v, ['x' => ['x must be before or equal 17:43']]);

        $v = $this->makeValidator(['x' => '17:44'], [RuleSet::make('x')->dateAfterOrEqual('17:45', 'H:i')]);
        $this->assertValidationFail($v, ['x' => ['x must be after or equal 17:45']]);
    }

    /**
     * @test
     */
    public function date_equal_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [RuleSet::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [RuleSet::make('x')->dateEqual('2001-01-01')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 2001-01-01']]);

        $v = $this->makeValidator(
            [
                'starts' => new DateTime('2000-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                RuleSet::make('ends')->dateEqual(RuleSet::ref('starts')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('Y-m-d')], [RuleSet::make('x')->dateEqual('today')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('Y-m-d')], [RuleSet::make('x')->dateEqual('yesterday')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal yesterday']]);

        $v = $this->makeValidator(['x' => date('Y-m-d')], [RuleSet::make('x')->dateEqual('tomorrow')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal tomorrow']]);

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal yesterday']]);

        $v = $this->makeValidator(['x' => date('d/m/Y')], [RuleSet::make('x')->dateEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal tomorrow']]);

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [RuleSet::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [RuleSet::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:43:59')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 2012-01-01 17:43:59']]);

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [RuleSet::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:01')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 2012-01-01 17:44:01']]);

        $v = $this->makeValidator(['x' => '17:44:00'], [RuleSet::make('x')->dateFormat('H:i:s')->dateEqual('17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [RuleSet::make('x')->dateFormat('H:i:s')->dateEqual('17:43:59')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 17:43:59']]);

        $v = $this->makeValidator(['x' => '17:44:00'], [RuleSet::make('x')->dateFormat('H:i:s')->dateEqual('17:44:01')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 17:44:01']]);

        $v = $this->makeValidator(['x' => '17:44'], [RuleSet::make('x')->dateFormat('H:i')->dateEqual('17:44')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [RuleSet::make('x')->dateFormat('H:i')->dateEqual('17:43')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 17:43']]);

        $v = $this->makeValidator(['x' => '17:44'], [RuleSet::make('x')->dateFormat('H:i')->dateEqual('17:45')]);
        $this->assertValidationFail($v, ['x' => ['x must be equal 17:45']]);
    }
}
