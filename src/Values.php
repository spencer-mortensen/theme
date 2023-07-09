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

use Exception;

class Values
{
	private static $reVariable = "\x03\\{\\$([a-zA-Z0-9_\\-]+)\\}\x03DusSX";

	private $variables;
	private $evaluated;

	public function __construct ()
	{
		$this->variables = [];
		$this->evaluated = [];
	}

	public function set (string $key, string $value): void
	{
		$this->variables[$key] = $this->value($key, $value);
		$this->evaluated[$key] = false;
	}

	private function value (string $key, string $value): string
	{
		if (!array_key_exists($key, $this->variables)) {
			return $value;
		}

		return str_replace('{$' . $key . '}', $this->variables[$key], $value);
	}

	public function get (string $key): string
	{
		if (!array_key_exists($key, $this->variables)) {
			return '';
		}

		if (!$this->evaluated[$key]) {
			$value = $this->variables[$key];
			unset($this->variables[$key]);

			$this->variables[$key] = $this->evaluate($value);
			$this->evaluated[$key] = true;
		}

		return $this->variables[$key];
	}

	private function evaluate (string $input): string
	{
		$output = '';
		$i = 0;
		$n = strlen($input);

		while (preg_match(self::$reVariable, $input, $match, PREG_OFFSET_CAPTURE, $i) === 1) {
			$iBegin = $match[0][1];
			$key = $match[1][0];

			$output .= substr($input, $i, $iBegin - $i) . $this->get($key);
			$i = $iBegin + strlen($match[0][0]);
		}

		if ($i < $n) {
			$output .= substr($input, $i);
		}

		return $output;
	}
}
