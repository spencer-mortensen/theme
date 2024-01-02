# Filesystem

This project is available as a Composer package:   
[spencer-mortensen/theme](https://packagist.org/packages/spencer-mortensen/theme)

## Usage:

```
$theme = new Theme($sitePath, $siteUrl);

$values = new Values();
$values->set('canonicalUrl', $canonicalUrl);

$theme->apply('theme/page', $values);
$theme->apply('theme/site', $values);

echo $values->get('html');
```
