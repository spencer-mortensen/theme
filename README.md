# Filesystem

This project is available as a Composer package:   
[spencer-mortensen/theme](https://packagist.org/packages/spencer-mortensen/theme)

## Usage:

```
$theme = new Theme($sitePath, $siteUrl);
$theme->set('canonicalUrl', $canonicalUrl);
$theme->apply('theme/page');
$theme->apply('theme/site');
$html = $theme->get('html');

echo $html;


=== theme/site/.html ===
<html>
<head>
{$css}{$js}<title>{$title}</title>
</head>
<body></body>
</html>

=== theme/site/.title ===
Masterlined

=== theme/site/.js ===
script.js
js/
```
