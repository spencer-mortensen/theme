<?php

/**
 * Copyright (C) 2017 Spencer Mortensen
 *
 * This file is part of Theme.
 *
 * Theme is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as publi>
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Theme is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public Lic>
 * along with Theme. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <spencer@lens.guide>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2023 Spencer Mortensen
 */

namespace SpencerMortensen\Theme;

class Path
{
	public static function safe (string $input): string
	{
		$inputAtoms = explode('/', $input);
		$outputAtoms = [];

		foreach ($inputAtoms as $atom) {
			if ($atom === '..') {
				array_pop($outputAtoms);
			} elseif ((0 < strlen($atom)) && ($atom !== '.')) {
				$outputAtoms[] = $atom;
			}
		}

		$output = implode('/', $outputAtoms);

		if (substr($input, 0, 1) === '/') {
			$output = '/' . $output;
		}

		if ((substr($input, -1) === '/') && (substr($output, -1) !== '/')) {
			$output .= '/';
		}

		return $output;
	}
}
