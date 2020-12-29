<?php

declare(strict_types=1);

namespace DonnySim\Validation\Laravel;

use DonnySim\Validation\Validator;
use DonnySim\Validation\ValidatorFactory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Validation\ValidationException;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'donnysim');
    }

    public function register(): void
    {
        $this->app->singleton(ValidatorFactory::class, static function () {
            return ValidatorFactory::instance();
        });

        $this->addPublishGroup('ds-translations', [
            __DIR__ . '/lang' => $this->app->resourcePath('lang/vendor/donnysim'),
        ]);

        Validator::setFailureHandler(static function (Validator $validator) {
            throw ValidationException::withMessages($validator->getMessages()->all());
        });

        ValidatorFactory::setInstanceResolver(function () {
            return new ValidatorFactory(new TranslationMessageResolver($this->app->get('translator')));
        });
    }
}
