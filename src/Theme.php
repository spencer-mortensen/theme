<?php

namespace SpencerMortensen\Theme;

class Theme
{
	private static $elements = [
		'css' => '<link href="{$url}" rel="stylesheet" type="text/css">',
		'js' => '<script src="{$url}" defer></script>'
	];

	private $values;
	private $sitePath;
	private $siteUrl;

	public function __construct (Values $values, string $sitePath, string $siteUrl)
	{
		$this->values = $values;
		$this->sitePath = $sitePath;
		$this->siteUrl = $siteUrl;
	}

	public function apply (string $key): void
	{
		$path = "{$this->sitePath}/{$key}";
		$url = "{$this->siteUrl}{$key}/";

		$childNames = self::readDirectory($path);

		$this->addDirectory('css', $childNames, $path, $url);
		$this->addDirectory('js', $childNames, $path, $url);
		$this->addValues($childNames, $path);
	}

	private function addDirectory (string $type, array &$childNames, string $path, string $url): void
	{
		if (!isset($childNames[$type])) {
			return;
		}

		$childPath = "{$path}/{$type}";

		if (!is_dir($childPath)) {
			return;
		}

		$childUrl = "{$url}{$type}/";
		$this->addFiles($type, $childPath, $childUrl);
		unset($childNames[$type]);
	}

	private function addFiles (string $type, string $path, string $url): void
	{
		$childNames = self::readDirectory($path);

		$tail = ".{$type}";
		$elements = [];

		foreach ($childNames as $childName) {
			if (!self::isTail($childName, $tail)) {
				continue;
			}

			$elements[] = $this->getElementHtml($type, "{$url}{$childName}");
		}

		if (0 < count($elements)) {
			$this->values->set($type, '{$' . $type . '}' . implode("\n", $elements) . "\n");
		}
	}

	private function getElementHtml (string $type, string $url): string
	{
		$urlHtml = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return str_replace('{$url}', $urlHtml, self::$elements[$type]);
	}

	private function addValues (array $childNames, string $path): void
	{
		$head = '.';

		foreach ($childNames as $childName) {
			if (!self::isHead($childName, $head)) {
				continue;
			}

			$childPath = "{$path}/{$childName}";

			if (!is_file($childPath)) {
				continue;
			}

			$key = substr($childName, strlen($head));
			$value = file_get_contents($childPath);
			$this->values->set($key, $value);
		}
	}

	private static function readDirectory (string $path): array
	{
		$childNames = [];

		$directory = opendir($path);

		for ($childName = readdir($directory); $childName !== false; $childName = readdir($directory)) {
			if (($childName !== '.') && ($childName !== '..')) {
				$childNames[$childName] = $childName;
			}
		}

		closedir($directory);

		return $childNames;
	}

	private static function isHead (string $haystack, string $needle): bool
	{
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}

	private static function isTail (string $haystack, string $needle): bool
	{
		$length = strlen($needle);

		return ($length < strlen($haystack))
			&& (substr_compare($haystack, $needle, -$length) === 0);
	}
}
