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
	private $values;
	private $css;
	private $js;

	public function __construct (string $sitePath, string $siteUrl)
	{
		$this->sitePath = $sitePath;
		$this->siteUrl = $siteUrl;
		$this->values = new Values();
		$this->css = [];
		$this->js = [];
	}

	public function apply (string $key): void
	{
		$path = "{$this->sitePath}/{$key}";
		$childNames = Directory::read($path);

		$this->addDependencies($path, $childNames, '.css', $this->css);
		$this->addDependencies($path, $childNames, '.js', $this->js);
		$this->addKeys($path, $childNames);
	}

	private function addDependencies (string $directoryPath, array &$childNames, string $extension, array &$dependencies): void
	{
		if (!isset($childNames[$extension])) {
			return;
		}

		unset($childNames[$extension]);

		$filePath = "{$directoryPath}/{$extension}";

		if (!is_file($filePath)) {
			return;
		}

		$contents = file_get_contents($filePath);
		$dependencyPaths = explode("\n", trim($contents));

		foreach ($dependencyPaths as $dependencyPath) {
			$dependencies[$dependencyPath] = true;
		}
	}

	private function addKeys (string $path, array &$childNames): void
	{
		foreach ($childNames as $childName) {
			if (!Text::startsWith($childName, '.')) {
				continue;
			}

			$childPath = "{$path}/{$childName}";

			if (!is_file($childPath)) {
				continue;
			}

			$key = substr($childName, 1);
			$value = file_get_contents($childPath);
			$this->values->set($key, $value);
		}
	}

	public function set (string $key, string $value): void
	{
		$this->values->set($key, $value);
	}

	public function get (string $key): string
	{
		$this->finalizeType('css', $this->css);
		$this->finalizeType('js', $this->js);

		return $this->values->get($key);
	}

	private function finalizeType (string $type, array $dependencies): void
	{
		$files = $this->getFiles($dependencies, ".{$type}");
		$html = $this->getDependenciesHtml($type, $files);
		$this->values->set($type, $html);
	}

	private function getFiles (array $dependencies, string $extension): array
	{
		$files = [];

		foreach ($dependencies as $key => $true) {
			$path = "{$this->sitePath}/{$key}";

			if (is_file($path)) {
				$files[$key] = true;
			} elseif (is_dir($path)) {
				$this->getDirectoryFiles($path, $key, $extension, $files);
			}
		}

		return $files;
	}

	private function getDirectoryFiles (string $path, string $key, string $extension, array &$files): void
	{
		$childNames = Directory::read($path);

		foreach ($childNames as $childName) {
			$childPath = "{$path}/{$childName}";
			$childKey = "{$key}/{$childName}";

			if (is_file($childPath)) {
				if (Text::endsWith($childName, $extension)) {
					$files[$childKey] = true;
				}
			} elseif (is_dir($childPath)) {
				$this->getDirectoryFiles($childPath, $childKey, $extension, $files);
			}
		}
	}

	private function getDependenciesHtml (string $type, array $files): string
	{
		$elements = [];

		foreach ($files as $key => $true) {
			$elements[] = self::getElementHtml($type, "{$this->siteUrl}{$key}");
		}

		return implode("\n", $elements);
	}

	private static function getElementHtml (string $type, string $url): string
	{
		$urlHtml = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return str_replace('{$url}', $urlHtml, self::$elements[$type]);
	}
}
