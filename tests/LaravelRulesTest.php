<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Rules;
use DonnySim\Validation\Rules\Laravel\Exists;
use DonnySim\Validation\Tests\Stubs\EntityModelStub;
use DonnySim\Validation\Tests\Stubs\TestMessageResolver;
use DonnySim\Validation\Validator;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class LaravelRulesTest extends TestCase
{
    /**
     * @test
     */
    public function exists_rule(): void
    {
        $v = $this->makeValidator(
            ['email' => 'foo'],
            [Rules::make('email')->rule(Exists::make('entities', 'email'))]
        );
        $this->assertValidationFail($v, 'email', 'email is invalid');

        DB::table('entities')->insert([
            ['email' => 'exists_builder', 'active' => 1],
        ]);
        $v = $this->makeValidator(
            ['email' => 'exists_builder'],
            [Rules::make('email')->rule(Exists::make(DB::table('entities')->where('active', 1), 'email'))]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['email' => 'exists_builder'],
            [Rules::make('email')->rule(Exists::make(DB::table('entities')->where('active', 0), 'email'))]
        );
        $this->assertValidationFail($v, 'email', 'email is invalid');

        DB::table('entities')->insert([
            ['email' => 'exists_arr_case1_1'],
            ['email' => 'exists_arr_case1_2'],
        ]);
        $v = $this->makeValidator(
            ['email' => ['exists_arr_case1_1', 'exists_arr_case1_2']],
            [Rules::make('email')->rule(Exists::make(DB::table('entities'), 'email'))]
        );
        self::assertTrue($v->passes());

        DB::table('entities')->insert([
            ['email' => 'exists_arr_case2_1'],
        ]);
        $v = $this->makeValidator(
            ['email' => ['exists_arr_case2_1', 'exists_arr_case2_2']],
            [Rules::make('email')->rule(Exists::make(DB::table('entities'), 'email'))]
        );
        $this->assertValidationFail($v, 'email', 'email is invalid');

        DB::table('entities')->insert([
            ['email' => 'exists_wild_1'],
            ['email' => 'exists_wild_2'],
        ]);
        $v = $this->makeValidator(
            ['emails' => ['exists_wild_1', 'exists_wild_2', 'exists_wild_3']],
            [Rules::make('emails.*')->rule(Exists::make(DB::table('entities'), 'email'))]
        );
        $this->assertValidationFail($v, 'emails.2', 'emails.2 is invalid');

        $v = $this->makeValidator(
            ['emails' => ['exists_wild_1', 'exists_wild_2']],
            [Rules::make('emails.*')->rule(Exists::make(DB::table('entities'), 'email'))]
        );
        self::assertTrue($v->passes());
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
            'laravel.exists' => ':attribute is invalid',
        ]), $data, $rules);

        return $validator->override($overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $manager = new DB();
        $manager->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $manager->setAsGlobal();
        $manager->bootEloquent();

        $manager->getDatabaseManager()
            ->getSchemaBuilder()
            ->create('entities', static function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->nullable();
                $table->boolean('active')->default(0);
            });
    }
}
