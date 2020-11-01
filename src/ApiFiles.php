<?php

namespace jedsonmelo\ApiFiles;

use GuzzleHttp\Client as Guzzle;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ApiFiles
{
	public function send($filename, $delete_after = false)
	{
		if (!file_exists($filename)) {
			throw new FileNotFoundException("the $filename file does not exist");
		}

		$file = fopen($filename, 'rb');
		if (!$file) {
			throw new InvalidFileException(
				"The $filename doesnt have content or could not be open"
			);
		}

		$response = self::sendContent($file, $filename);

		if (is_resource($file)) {
			fclose($file);
		}

		if ($delete_after === true) {
			try {
				unlink($filename);
			} catch (\Exception $e) {
				throw new \Exception("It was not possible to delete file $filename");
			}
		}

		return $response;
	}

	public function get(string $api_file_id)
	{
		if (!$api_file_id) {
			throw new \InvalidArgumentException('The api_file_id argument can\'t be empty string');
		}

		$access_token = config('apifiles.access_token');
		$url = config('apifiles.url');
		$response = app(Guzzle::class)->request(
			'GET',
			"$url/api/file/$api_file_id",
			[
				'headers' => [
					'Authorization' => "Bearer $access_token",
				],
				'exceptions' => false,
			]
		);

		$status_code = $response->getStatusCode();

		if ($status_code != 200) {
			throw new \RuntimeException(
				"ApiFiles got status code $status_code on try get the file with id $api_file_id"
			);
		}

		return $response;
	}

	public function sendContent($content, $filename)
	{
		$access_token = config('apifiles.access_token');
		$url = config('apifiles.url');
		$response = app(Guzzle::class)->request(
			'POST',
			"$url/api/file",
			[
				'headers' => [
					'Authorization' => "Bearer $access_token",
				],
				'multipart' => [
					[
						'name' => 'file',
						'contents' => $content,
						'filename' => $filename,
					],
				],
			]
		);

		$status_code = $response->getStatusCode();

		if ($status_code != 200) {
			throw new \RuntimeException(
				"ApiFiles got status code $status_code on try send the file with id $api_file_id"
			);
		}

		$response->id = $response->getBody()->getContents();

		return $response;
	}
}
