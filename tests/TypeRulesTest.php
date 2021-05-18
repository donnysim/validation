<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DateTime;
use DateTimeImmutable;
use DonnySim\Validation\Rules;
use DonnySim\Validation\Rules\Types\IpAddress;
use DonnySim\Validation\Tests\Concerns\ValidatorHelpers;
use PHPUnit\Framework\TestCase;
use function date_default_timezone_set;
use const PHP_EOL;

class TypeRulesTest extends TestCase
{
    use ValidatorHelpers;

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
    public function date_rule(): void
    {
        date_default_timezone_set('UTC');
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
    public function ip_address_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'aslsdlks'], [Rules::make('foo')->ipAddress()]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ip address');

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress(IpAddress::TYPE_IPV4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '::1'], [Rules::make('foo')->ipAddress(IpAddress::TYPE_IPV6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [Rules::make('foo')->ipAddress(IpAddress::TYPE_IPV6)]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ipv6 address');

        $v = $this->makeValidator(['foo' => '::1'], [Rules::make('foo')->ipAddress(IpAddress::TYPE_IPV4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be a valid ipv4 address');
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
            ['145a1e72-d11d-11e8-a8d5-f2801f1b9fd1' . PHP_EOL],
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
}
