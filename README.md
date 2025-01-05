# Filesystem

This project is available as a Composer package:   
[spencer-mortensen/theme](https://packagist.org/packages/spencer-mortensen/theme)

## Usage:

```
$theme = new Theme($sitePath, $siteUrl);
$theme->set('canonicalUrl', $canonicalUrl);
$theme->apply('theme/site');
$theme->apply('theme/page');
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
theme/site/script.js.list

=== theme/site/script.js.list ===
theme/site/js/
theme/site/script.js
```
