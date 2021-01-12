## 1.4.0 ##

* Fixed deprecation warnings in unit tests under Moodle 3.10. Credit goes to @ewallah.
* Fixed tests syntax to be compatible with PHPUnit 8.5.
* Added support for generating version.php code requiring Moodle 3.10.

## 1.3.0 ##

* Added support for generating contenttype plugin templates. Credit goes to Ferran
  Recio (@ferranrecio).
* Moodle versions 3.8 and 3.9 can be selected as required versions.
* URLs in boilerplates use the HTTPS protocol explicitly.
* Fixed typos and errors. Credit goes to @kritisingh1.

## 1.2.3 ##

* Fixed generating skeletons for activity modules. Credit goes to Leo Auri (@leoauri)
  for the fix.
* Fixed travis-ci configuration. Credit goes to Jonathan Champ (@jrchamp).
* Added support for generating minimal db/install.xml files for activity modules.

## 1.2.2 ##

* Added support for generating version.php code requiring Moodle 3.7

## 1.2.1 ##

* Fixed bug - auth plugins not setting the authtype property. Credit goes to
  Geoffrey Van Wyk (@systemovich) for the report and the fix suggestion.
* Fixed bug #90 - the name of the XMLDB upgrade function for activity modules.
* Added support for generating version.php code requiring Moodle 3.6.

## 1.2.0 ##

* Privacy API implemented. The plugin does not store any personal data.
* Fixed bug #87 - invalid language file name for activity modules.

## 1.1.1 ##

* Fixed a bug leading to generating the provider.php file with a syntax error in some
  cases.

## 1.1.0 ##

* Added support to generate privacy API related code (refer to cli/example.yaml).
  Special thanks to Michael Hughes for the initial implementation.
* Improved the component type and name fields usability - autodisplay the plugin type
  prefix so that it is more intuitive what the name field should hold.
* Added support to generate plugins requiring Moodle 3.4 and 3.5
* Make mustache loader path configurable, allowing better integration with moosh.
  Credit goes to Tomasz Muras.

## 1.0.0 ##

* Added support to generate plugins requiring Moodle 3.3 and 3.2.
* Added support for setting default values of some recipe file form fields
* Fixed the risk of having the generated ZIP file corrupted with debugging data
* Fixed some formal coding style violations


## 0.9.0 ##

* Initial version submitted to the Moodle plugins directory as a result of
  GSOC 2016
