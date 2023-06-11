<?php

namespace SpencerMortensen\Theme;

class Values
{
	private static $reVariable = "\x03\\{\\$([a-zA-Z0-9_\\-]+)\\}\x03DusSX";

	private $variables;

	public function __construct ()
	{
		$this->variables = [];
	}

	public function get (string $key): ?string
	{
		return $this->variables[$key] ?? null;
	}

	public function set (string $key, string $value): void
	{
		$this->variables[$key] = $this->expand($value);
	}

	private function expand (string $input): string
	{
		$output = '';
		$n = strlen($input);
		$i = 0;

		while (preg_match(self::$reVariable, $input, $match, PREG_OFFSET_CAPTURE, $i) === 1) {
			$iBegin = $match[0][1];
			$key = $match[1][0];
			$value = $this->variables[$key] ?? '';

			$output .= substr($input, $i, $iBegin - $i) . $value;
			$i = $iBegin + strlen($match[0][0]);
		}

		if ($i < $n) {
			$output .= substr($input, $i);
		}

		return $output;
	}
}
