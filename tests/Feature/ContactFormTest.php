<?php

use App\Notifications\ContactFormSubmitted;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\postJson;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => fake()->firstName().' '.fake()->lastName(),
        'email' => fake()->safeEmail(),
        'subject' => fakeTitle(),
        'message' => fakeText(100),
        'key' => fake()->lexify('????????'),
        'captcha' => (string) fake()->numberBetween(0, 99),
    ], $overrides);
}

beforeEach(function (): void {
    // The real rule needs a rendered captcha image; these tests are about everything around it.
    Validator::extend('captcha_api', fn (): bool => true);
});

describe('the contact form', function (): void {
    it('emails the site administrators', function (): void {
        // Given
        Notification::fake();

        // When
        $response = postJson(route('contact.store'), contactPayload());

        // Then
        $response->assertOk();
        Notification::assertSentOnDemand(ContactFormSubmitted::class);
    });

    it('rejects a message that is too long', function (): void {
        // Given
        Notification::fake();

        // When
        $response = postJson(route('contact.store'), contactPayload(['message' => fake()->lexify(str_repeat('?', 2001))]));

        // Then
        $response->assertUnprocessable()->assertJsonValidationErrors('message');
        Notification::assertNothingSent();
    });

    it('cannot smuggle extra rules through the captcha key', function (): void {
        // Given
        $parameters = null;
        $keyStart = fake()->lexify('????');
        $keyEnd = fake()->lexify('????');
        Validator::extend('captcha_api', function (string $attribute, mixed $value, array $ruleParameters) use (&$parameters): bool {
            $parameters = $ruleParameters;

            return true;
        });

        // When
        postJson(route('contact.store'), contactPayload(['key' => "{$keyStart},{$keyEnd}|required"]));

        // Then
        expect($parameters)->toBe(["{$keyStart}{$keyEnd}required", 'math']);
    });

    it('is throttled', function (): void {
        // When
        foreach (range(1, 5) as $attempt) {
            postJson(route('contact.store'), []);
        }
        $response = postJson(route('contact.store'), []);

        // Then
        $response->assertTooManyRequests();
    });
});
