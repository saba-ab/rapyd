<?php

declare(strict_types=1);

use Illuminate\Http\Client\Response;
use Sabaab\Rapyd\Client\RapydResponse;
use Sabaab\Rapyd\Exceptions\ApiException;
use Sabaab\Rapyd\Exceptions\AuthenticationException;
use Sabaab\Rapyd\Exceptions\ValidationException;

function makeResponse(array $body): RapydResponse
{
    $httpResponse = new GuzzleHttp\Psr7\Response(200, [], json_encode($body));
    $illuminateResponse = new Response($httpResponse);

    return new RapydResponse($illuminateResponse);
}

it('unwraps a successful response', function () {
    $response = makeResponse([
        'status' => ['status' => 'SUCCESS', 'error_code' => '', 'message' => '', 'operation_id' => 'op_123'],
        'data' => ['id' => 'payment_abc', 'amount' => 100],
    ]);

    expect($response->successful())->toBeTrue();
    expect($response->failed())->toBeFalse();
    expect($response->data())->toBe(['id' => 'payment_abc', 'amount' => 100]);
    expect($response->operationId())->toBe('op_123');
});

it('detects an error response', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'SOME_ERROR', 'message' => 'Something went wrong', 'operation_id' => 'op_456'],
    ]);

    expect($response->successful())->toBeFalse();
    expect($response->failed())->toBeTrue();
    expect($response->errorCode())->toBe('SOME_ERROR');
    expect($response->message())->toBe('Something went wrong');
});

it('returns null data on error responses without data key', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'ERR', 'message' => 'fail'],
    ]);

    expect($response->data())->toBeNull();
});

it('throws AuthenticationException for UNAUTHENTICATED error code', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'UNAUTHENTICATED', 'message' => 'Bad credentials'],
    ]);

    $response->throw();
})->throws(AuthenticationException::class, 'Bad credentials');

it('throws AuthenticationException for UNAUTHORIZED error code', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'UNAUTHORIZED', 'message' => 'Not allowed'],
    ]);

    $response->throw();
})->throws(AuthenticationException::class, 'Not allowed');

it('throws ValidationException for INVALID_FIELDS error code', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'INVALID_FIELDS', 'message' => 'Invalid fields'],
        'data' => ['amount' => 'required'],
    ]);

    try {
        $response->throw();
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->getMessage())->toBe('Invalid fields');
        expect($e->fields)->toBe(['amount' => 'required']);
    }
});

it('throws ValidationException for INVALID_FIELDS prefixed error codes', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'INVALID_FIELDS_AMOUNT', 'message' => 'Invalid amount'],
        'data' => [],
    ]);

    $response->throw();
})->throws(ValidationException::class, 'Invalid amount');

it('throws ApiException for generic error codes', function () {
    $response = makeResponse([
        'status' => [
            'status' => 'ERROR',
            'error_code' => 'INTERNAL_ERROR',
            'message' => 'Server error',
            'operation_id' => 'op_789',
            'response_code' => 'RSP_500',
        ],
    ]);

    try {
        $response->throw();
        $this->fail('Expected ApiException');
    } catch (ApiException $e) {
        expect($e->getMessage())->toBe('Server error');
        expect($e->errorCode)->toBe('INTERNAL_ERROR');
        expect($e->operationId)->toBe('op_789');
        expect($e->responseCode)->toBe('RSP_500');
    }
});

it('does not throw on successful responses', function () {
    $response = makeResponse([
        'status' => ['status' => 'SUCCESS', 'error_code' => ''],
        'data' => ['id' => 'test'],
    ]);

    expect($response->throw())->toBeInstanceOf(RapydResponse::class);
});

it('hydrates a DTO via toDto', function () {
    $response = makeResponse([
        'status' => ['status' => 'SUCCESS', 'error_code' => ''],
        'data' => ['id' => 'obj_123', 'name' => 'Test'],
    ]);

    $dto = $response->toDto(FakeDto::class);

    expect($dto)->toBeInstanceOf(FakeDto::class);
    expect($dto->id)->toBe('obj_123');
    expect($dto->name)->toBe('Test');
});

it('throws when calling toDto on an error response', function () {
    $response = makeResponse([
        'status' => ['status' => 'ERROR', 'error_code' => 'SOME_ERROR', 'message' => 'fail'],
    ]);

    $response->toDto(FakeDto::class);
})->throws(ApiException::class);

it('returns full body via toArray', function () {
    $body = [
        'status' => ['status' => 'SUCCESS', 'error_code' => ''],
        'data' => ['id' => 'test'],
    ];

    $response = makeResponse($body);

    expect($response->toArray())->toBe($body);
});

final class FakeDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
        );
    }
}
