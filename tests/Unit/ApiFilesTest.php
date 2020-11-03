<?php

namespace jedsonmelo\ApiFiles\Tests\Unit;

use Mockery;
use GuzzleHttp\ClientInterface as Guzzle;
use jedsonmelo\ApiFiles\Tests\TestCase;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use jedsonmelo\ApiFiles\Facades\ApiFiles;

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
		$mock = Mockery::mock(Guzzle::class);

		$mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse);

		$this->app->instance(Guzzle::class, $mock);

		$filename = __DIR__ . '/../Support/Stubs/dummy_file';
		$response = ApiFiles::send($filename);

		$this->assertNotNull($response->id);
	}

	public function testGetInvalidFile()
	{
		$guzzle_mock = Mockery::mock(Guzzle::class);

		$guzzle_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse(404));

		$this->app->instance(Guzzle::class, $guzzle_mock);

		$file = 'arquivo-que-nao-existe.zip';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"ApiFiles got status code 404 on try get the file with id $file"
		);

		ApiFiles::get($file);
	}

	public function testGetFile()
	{
		$mock = Mockery::mock(Guzzle::class);

		$mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse);

		$this->app->instance(Guzzle::class, $mock);

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
		$mock = Mockery::mock(Guzzle::class);

		$content = 'biscoito';
		$mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse(200, $content));

		$this->app->instance(Guzzle::class, $mock);

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

		$guzzle_mock = Mockery::mock(Guzzle::class);

		$guzzle_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse($status_code));

		$this->app->instance(Guzzle::class, $guzzle_mock);

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

		$guzzle_mock = Mockery::mock(Guzzle::class);

		$guzzle_mock->shouldReceive('request')
			->andReturn(new DummyGuzzleResponse($status_code, $content));

		$this->app->instance(Guzzle::class, $guzzle_mock);

		$response = ApiFiles::delete($id);

		$this->assertSame($content, $response->getContents());
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
