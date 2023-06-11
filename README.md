# Filesystem

This project is available as a Composer package:   
[spencer-mortensen/theme](https://packagist.org/packages/spencer-mortensen/theme)

## Usage:

```
$values = new Values();
$values->set('canonicalUrl', $canonicalUrl);

$theme = new Theme($values, $sitePath, $siteUrl);

$theme->apply('theme/page');
$theme->apply('theme/site');

echo $values->get('html');
```
