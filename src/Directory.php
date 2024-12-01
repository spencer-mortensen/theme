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

class Directory
{
	public static function read (string $path): array
	{
		$childNames = [];

		$directory = opendir($path);

		for ($childName = readdir($directory); $childName !== false; $childName = readdir($directory)) {
			if (($childName === '.') || ($childName === '..')) {
				continue;
			}

			$childNames[$childName] = $childName;
		}

		closedir($directory);

		return $childNames;
	}
}
