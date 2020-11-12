<?php

namespace Sysvale\ApiFiles\Tests\Unit;

use Mockery;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Sysvale\ApiFiles\ApiFilesClient;
use Sysvale\ApiFiles\Tests\TestCase;
use Sysvale\ApiFiles\Facades\ApiFiles;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ApiFilesTest extends TestCase
{
	public function testTrySendInvalidPath()
	{
		$path = 'invalid/path/to/file';

		$this->expectException(FileNotFoundException::class);
		ApiFiles::send($path);
	}

	public function testSendFile()
	{
		$mock = Mockery::mock(ApiFilesClient::class);

		$mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse);

		$this->app->instance(ApiFilesClient::class, $mock);

		$filename = __DIR__ . '/../Support/Stubs/dummy_file';
		$response = ApiFiles::send($filename);

		$this->assertNotNull($response->id);
	}

	public function testGetInvalidFile()
	{
		$client = Mockery::mock(ApiFilesClient::class);

		$client->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse(404));

		$this->app->instance(ApiFilesClient::class, $client);

		$file = 'arquivo-que-nao-existe.zip';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"ApiFiles got status code 404 on try get the file with id $file"
		);

		ApiFiles::get($file);
	}

	public function testGetFile()
	{
		$client_mock = Mockery::mock(ApiFilesClient::class);

		$client_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse);

		$this->app->instance(ApiFilesClient::class, $client_mock);

		$response = ApiFiles::get('test.png');

		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testSendEmptyStringToGet()
	{
		$this->expectException(\InvalidArgumentException::class);
		ApiFiles::get('');
	}

	public function testSendContents()
	{
		$client_mock = Mockery::mock(ApiFilesClient::class);

		$content = 'biscoito';
		$client_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse(200, $content));

		$this->app->instance(ApiFilesClient::class, $client_mock);

		$response = ApiFiles::sendContent($content, 'nome.txt', 'deu erro');
		$this->assertNotNull($response->id);

		$response = ApiFiles::get($response->id);
		$this->assertEquals($content, $response->getBody()->getContents());
	}

	public function testReceiveExceptionWhenPassEmptyStringOnDeleteMethod()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('The api_file_id argument can\'t be empty string');
		ApiFiles::delete('');
	}

	public function testDeleteReturnException()
	{
		$status_code = 500;
		$id = '123';

		$client_mock = Mockery::mock(ApiFilesClient::class);

		$client_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse($status_code));

		$this->app->instance(ApiFilesClient::class, $client_mock);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"ApiFiles got status code $status_code on try delete the file with id $id"
		);

		ApiFiles::delete($id);
	}

	public function testDelete()
	{
		$status_code = 200;
		$id = '123';
		$content = 'patricia';

		$client_mock = Mockery::mock(ApiFilesClient::class);

		$client_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse($status_code, $content));

		$this->app->instance(ApiFilesClient::class, $client_mock);

		$response = ApiFiles::delete($id);

		$this->assertSame($content, $response->getContents());
	}

	public function testIfRequestIsMadeToCorrectUri()
	{
		$response = new Response(200, ['X-Foo' => 'Bar'], '');
		$mock = new MockHandler([$response]);
		$handlerStack = HandlerStack::create($mock);

		$container = [];
		$history = Middleware::history($container);
		$handlerStack->push($history);

		$base_uri = 'foo.bar.com';
		$client = new ApiFilesClient([
			'base_uri' => "https://$base_uri",
			'handler' => $handlerStack
		]);

		$this->app->instance(ApiFilesClient::class, $client);

		$filename = __DIR__ . '/../Support/Stubs/dummy_file';
		ApiFiles::send($filename);

		$request = $container[0]['request'];

		$this->assertSame($base_uri, $request->getUri()->getHost());
		$this->assertSame('/api/file', $request->getUri()->getPath());
	}
}

//phpcs:ignore
class DummyGuzzleResponse
{
	private $status;
	private $content;

	public function __construct($status = 200, $content = '')
	{
		$this->status = $status;
		$this->content = $content;
	}

	public function getStatusCode()
	{
		return $this->status;
	}

	public function getBody()
	{
		return $this;
	}

	public function getContents()
	{
		return $this->content;
	}
}
