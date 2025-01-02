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

	public function apply (string $directoryKey): void
	{
		$childNames = Directory::read("{$this->sitePath}/{$directoryKey}");

		$this->addDependencyKey($directoryKey, $childNames, 'css', $this->css);
		$this->addDependencyKey($directoryKey, $childNames, 'js', $this->js);
		$this->addKeys($directoryKey, $childNames);
	}

	private function addDependencyKey (string $directoryKey, array &$childNames, string $type, array &$dependencies): void
	{
		$extension = ".{$type}";

		if (isset($childNames[$extension])) {
			unset($childNames[$extension]);

			$this->addDependencyList("{$directoryKey}/{$extension}", $type, $dependencies);
		}
	}

	private function addDependencyList (string $fileKey, string $type, array &$dependencies): void
	{
		$dependencies[$fileKey] = false;
		$extension = ".{$type}";
		$minExtension = ".min{$type}";

		$contents = file_get_contents("{$this->sitePath}/{$fileKey}");
		$childKeys = explode("\n", trim($contents));

		foreach ($childKeys as $childKey) {
			$childKey = trim($childKey, '/');

			if (array_key_exists($childKey, $dependencies)) {
				continue;
			}

			if (Text::endsWith($childKey, $extension)) {
				$dependencies[$childKey] = true;
			} elseif (Text::endsWith($childKey, $minExtension)) {
				$this->addDependencyList($childKey, $type, $dependencies);
			} else {
				$this->addDependencyDirectory($childKey, $extension, $dependencies);
			}
		}
	}

	private function addDependencyDirectory (string $key, string $extension, array &$dependencies): void
	{
		$dependencies[$key] = false;
		$childNames = Directory::read("{$this->sitePath}/{$key}");

		foreach ($childNames as $childName) {
			$childKey = "{$key}/{$childName}";

			if (array_key_exists($childKey, $dependencies)) {
				continue;
			}

			if (Text::endsWith($childKey, $extension)) {
				$dependencies[$childKey] = true;
			} elseif (is_dir("{$this->sitePath}/{$childKey}")) {
				$this->addDependencyDirectory($childKey, $extension, $dependencies);
			}
		}
	}

	private function addKeys (string $directoryKey, array &$childNames): void
	{
		foreach ($childNames as $childName) {
			if (!Text::startsWith($childName, '.')) {
				continue;
			}

			$childPath = "{$this->sitePath}/{$directoryKey}/{$childName}";

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
		$this->finalize('css', $this->css);
		$this->finalize('js', $this->js);

		return $this->values->get($key);
	}

	private function finalize (string $type, array $dependencies): void
	{
		$elements = [];

		foreach ($dependencies as $key => $isFile) {
			if ($isFile) {
				$elements[] = self::getElementHtml($type, "{$this->siteUrl}{$key}");
			}
		}

		$html = implode("\n", $elements);
		$this->values->set($type, $html);
	}

	private static function getElementHtml (string $type, string $url): string
	{
		$urlHtml = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return str_replace('{$url}', $urlHtml, self::$elements[$type]);
	}
}
