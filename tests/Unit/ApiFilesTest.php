<?php

namespace jedsonmelo\ApiFiles\Tests\Unit;

use Mockery;
use GuzzleHttp\Client as Guzzle;
use jedsonmelo\ApiFiles\ApiFiles;
use jedsonmelo\ApiFiles\Tests\TestCase;
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
