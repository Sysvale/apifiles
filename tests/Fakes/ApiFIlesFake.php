<?php

namespace Tests\Fakes;

use Closure;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\ExpectationFailedException;

class ApiFilesFake
{
	protected static $content;
	protected static $sent_files = [];

	public static function setContents($content)
	{
		self::$content = $content;
	}

	public static function get($api_file_id)
	{
		return new class(self::$content) {
			protected $content;

			public function __construct($content)
			{
				$this->content = $content;
			}
			public function getStatusCode()
			{
				return 200;
			}
			public function getBody()
			{
				return $this;
			}
			public function getContents()
			{
				return $this->content;
			}
		};
	}

	public static function send($path)
	{
		self::$sent_files[] = [
			'path' => $path,
			'content' => file_get_contents($path)
		];

		return (object) [
			'statusCode' => 200,
			'id' => array_key_last(self::$sent_files) . '-api_file.fake'
		];
	}

	public static function assertSent($filename, $callback = null)
	{
		$files = array_column(self::$sent_files, 'path');

		PHPUnit::assertContains($filename, $files, "The file $filename was not sent to ApiFiles");

		if ($callback instanceof Closure) {
			foreach (self::$sent_files as $file) {
				if ($callback($file)) {
					return true;
				}
			}
		}

		throw new ExpectationFailedException("The file $filename was not sent to ApiFiles");
	}
}
