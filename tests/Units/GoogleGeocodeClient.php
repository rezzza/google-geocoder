<?php

namespace Rezzza\GoogleGeocoder\Tests\Units;

use mageekguy\atoum;
use Rezzza\GoogleGeocoder\Exception\GoogleGeocodeNoResultException;
use Rezzza\GoogleGeocoder\GoogleGeocodeClient as SUT;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleGeocodeClient extends atoum
{
    private $mockAdapter;

    private $messageFactory;

    private $apiKey;

    public function beforeTestMethod($method)
    {
        $this->apiKey = null;
        $this->mockAdapter = new \mock\Ivory\HttpAdapter\CurlHttpAdapter();
        $this->messageFactory = new \Ivory\HttpAdapter\Message\MessageFactory();
    }

    public function test_it_returns_payload_on_valid_response()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [{"hello" : "world"}], "status" : "OK"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->when($result = $SUT->executeQuery([]))
            ->then
                ->array($result)
                    ->isEqualTo(['results' => [['hello' => 'world']], 'status' => 'OK'])
        ;
    }

    public function test_it_passes_query_params_to_google_url()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [{"hello" : "world"}], "status" : "OK"}'
                ),
                $this->apiKey = 'someApiKey',
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->when($result = $SUT->executeQuery([
                'language' => 'fr',
                'components' => 'country:FR|locality:paris'
            ]))
            ->then
                ->mock($this->mockAdapter)
                    ->call('get')
                        ->withIdenticalArguments(
                            'https://maps.googleapis.com/maps/api/geocode/json?key=someApiKey&language=fr&components=country%3AFR%7Clocality%3Aparis'
                        )
                        ->once()
        ;
    }

    public function test_that_throwed_exception_error_message_overrides_default()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "UNKNOWN_ERROR", "error_message": "foobar"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function () use ($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeException')
                ->hasMessage('foobar')
            );
    }

    public function test_it_throws_response_decode_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{ "key": SomeInvalidJSONMuhahahahahaha!!!! }'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeResponseDecodeException')
                ->hasCode(4)
                ->message
                    ->matches('#^(Syntax error|unexpected character)$#')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_no_result_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "ZERO_RESULTS"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery(['address' => 'someInexistantAddress']);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeNoResultException')
                ->hasMessage('No result found.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->withIdenticalArguments(
                            'https://maps.googleapis.com/maps/api/geocode/json?address=someInexistantAddress'
                        )
                            ->once()
            );
    }

    public function test_it_throws_protocol_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    500,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    [],
                    'Oops'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeProtocolException')
                ->hasMessage('Internal Server Error')
                ->hasCode(500)
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_protocol_exception_from_http_adapter()
    {
        $this
            ->given(
                $SUT = new SUT($this->mockAdapter, $this->apiKey),
                $httpAdapterException = \Ivory\HttpAdapter\HttpAdapterException::cannotFetchUri(
                    'http://www.google.com',
                    get_class($this->mockAdapter),
                    'this is an error message'
                ),
                $this->calling($this->mockAdapter)->get->throw = $httpAdapterException
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeProtocolException')
                ->hasMessage('An error occurred when fetching the URI "http://www.google.com" with the adapter "mock\Ivory\HttpAdapter\CurlHttpAdapter" ("this is an error message").')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_quota_exceeded_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "OVER_QUERY_LIMIT"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery(['latlng' => '42.1,2.1', 'language' => 'en']);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeQuotaExceededException')
                ->hasMessage('Query limit exceeded.')
            )
            ->and(
            $this->mock($this->mockAdapter)
                ->call('get')
                    ->withIdenticalArguments(
                        'https://maps.googleapis.com/maps/api/geocode/json?latlng=42.1%2C2.1&language=en'
                    )
                        ->once()
            );
    }

    public function test_it_throws_request_denied_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "REQUEST_DENIED"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeRequestDeniedException')
                ->hasMessage('Unauthorized request.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_invalid_request_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "INVALID_REQUEST"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeInvalidRequestException')
                ->hasMessage('Invalid request.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_unknown_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "UNKNOWN_ERROR", "error_message": "foobar"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeUnknownException')
                ->hasMessage('foobar')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }
}
