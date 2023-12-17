<?php

/**
 * Copyright (C) 2017 Spencer Mortensen
 *
 * This file is part of Theme.
 *
 * Theme is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Theme is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Theme. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <spencer@lens.guide>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2023 Spencer Mortensen
 */

namespace SpencerMortensen\Theme;

class Theme
{
	private static $elements = [
		'css' => '<link href="{$url}" rel="stylesheet" type="text/css">',
		'js' => '<script src="{$url}" defer></script>'
	];

	private $sitePath;
	private $siteUrl;

	public function __construct (string $sitePath, string $siteUrl)
	{
		$this->sitePath = $sitePath;
		$this->siteUrl = $siteUrl;
	}

	public function apply (string $key, Values $values): void
	{
		$path = "{$this->sitePath}/{$key}";
		$url = "{$this->siteUrl}{$key}/";

		$childNames = self::readDirectory($path);

		$this->addDirectory('css', $childNames, $path, $url, $values);
		$this->addDirectory('js', $childNames, $path, $url, $values);
		$this->addValues($childNames, $path, $values);
	}

	private function addDirectory (string $type, array &$childNames, string $path, string $url, Values $values): void
	{
		if (!isset($childNames[$type])) {
			return;
		}

		$childPath = "{$path}/{$type}";

		if (!is_dir($childPath)) {
			return;
		}

		$childUrl = "{$url}{$type}/";
		$this->addFiles($type, $childPath, $childUrl, $values);
		unset($childNames[$type]);
	}

	private function addFiles (string $type, string $path, string $url, Values $values): void
	{
		$childNames = self::readDirectory($path);

		$tail = ".{$type}";
		$elements = [];

		foreach ($childNames as $childName) {
			if (self::isTail($childName, $tail)) {
				$elements[] = $this->getElementHtml($type, "{$url}{$childName}");
				continue;
			}

			$childPath = "{$path}/{$childName}";
			$childUrl = "{$url}{$childName}/";

			if (is_dir($childPath)) {
				$this->addFiles($type, $childPath, $childUrl, $values);
			}
		}

		if (0 < count($elements)) {
			$values->set($type, '{$' . $type . '}' . implode("\n", $elements) . "\n");
		}
	}

	private function getElementHtml (string $type, string $url): string
	{
		$urlHtml = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return str_replace('{$url}', $urlHtml, self::$elements[$type]);
	}

	private function addValues (array $childNames, string $path, Values $values): void
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
			$values->set($key, $value);
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
