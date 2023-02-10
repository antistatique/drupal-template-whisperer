# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Changed
- re-enable PHPUnit Symfony Deprecation notice

### Fixed
- fix test class $modules property must be declared protected
- fix typo on test testFieldWhitoutTemplate -> testFieldWithoutTemplate
- fix usage of dynamic properties on UiFieldTest
- fix Issue #3312077 - Route entity.template_whisperer.add_form does not exist - by LeoAlcci, christyanpaim, Bohus Ulrych, wengerk
- fix Issue #3312076 - The template_whisperer_suggestion entity type did not specify a view_builder handler - by immaculatexavier, Bohus Ulrych, wengerk

### Added
- add coverage of Drupal 10.1.x
- add call accessCheck on every QueryBuilder

## [4.0.0] - 2022-11-18
### Added
- add official support of drupal 9.5 & 10.0

### Changed
- drop support of drupal below 9.3.x
- bump major release number in order of using Drupal new semver system

### Fixed
- fix deprecated Classy theme replaced by Starterkit for Drupal 10 compatibilities
- fix call to deprecated method assertEqual() replaced by assertEquals() for Drupal 10 compatibilities
- fix deprecated class name Twig_Extension for Drupal 10 compatibilities
- fix instantiation of deprecated class Twig_SimpleFunction for Drupal 10 compatibilities
- fix call to deprecated method drupalPostForm() replaced by submitForm() for Drupal 10 compatibilities
- fix declaration of ::tearDown() must be compatible with parent::tearDown() for PHP 8 compatibilities

## [3.1.0] - 2022-10-21
### Added
- add coverage for Drupal 9.3, 9.4 & 9.5
- add upgrade-status check

### Fixed
- fix docker running tests on Github Actions

### Removed
- remove trigger github actions on every pull-request, keep only push
- remove satackey/action-docker-layer-caching on Github Actions
- drop support of drupal below 9.0

## [3.0.0] - 2022-06-24
### Added
- Issue #3087966 by wengerk: Support for "Negate the condition" on the Template Whisperer Block Layout Visibility configuration

### Fixed
- Issue #3166328 by wengerk: TemplateWhispererManager::getFieldSuggestions may throw `Call to a member function getSuggestion() on null`

### Changed
- Rewrite Travis Integration to use Docker instead of Drupal_TI
- move changelog format in order to use Keep a Changelog standard

## [3.0.0-alpha] - 2020-03-27
### Added
- Issue #3090756 by wengerk: Drupal 9 Readiness
- Issue #3151185 by wengerk: Drupal 9 - Tests deprecation notice assertInternalType

## [2.3.0] - 2019-12-03
### Added
- Issue #3008554 by wengerk, valthebald: Display widget in advanced group only when it exists.
- Issue #3044924 - fix Drupal-CI Composer failure since Drupal 8.7.x+ - Update of drupal/coder squizlabs/php_codesniffer"
- Issue #3087804 by wengerk, AurianaHg: Support for Page level suggestion

## [2.2.0] - 2018-06-05
### Added
- Fix Token suggestion my crash on unused suggestion lookup - Issue 2974817.
- Add better warning of suggestion usage before deletion.
- Fix Undefined index: handler when updating a Template Whisperer Settings - Issue 2935078.

## [2.1.0] - 2018-05-16
### Added
- Fix suggestions too much permissive which leads in wrong suggestions usage - Issue 2944054.
- Update to PHPUnit 6.x & Drupal 8.6.x for testing on TravisCI.
- Fix uninstall/reinstall by removing garbage configurations.

## [2.0.0] - 2018-05-15
### Added
- Improve DEVELOPPING.md with better testing command.
- Fix an issue that make the Suggestion impossible to change in a entity field.
- Add feature to chose which Suggestion are avaialble for selection, by field.
- Add more tests to improve stability.
- Add Block Conditional Visibility.
- Add Token integration.
- Add new permission "administer the template whisperer field" for edit access of field(s).
- Add Hook Update 8001 to migrate the new permission "administer the template whisperer field" & avoid breaking changes.
- Add Twig function to retrieve entity-ies using a given Template Whisperer suggestion.
- Integrate Travis CI.
- Integrate Style CI.

## [1.0.0] - 2017-04-18
### Added
- Refactoring Template Whisperer as ConfigEntity instead of ContentEntity.
- Rewording Template Whisperer with the idea of suggestion(s).
- Adding Usage admin page by Template Whisperer suggestion(s).

## [0.0.0] - 2017-03-07
### Added
- Add BrowserTestBase to test web-based behaviors and interactions.
- First draft.

[Unreleased]: https://github.com/antistatique/drupal-template-whisperer/compare/4.0.0...HEAD
[4.0.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-3.1...4.0.0
[3.1.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-3.0...8.x-3.1
[3.0.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-3.0-alpha...8.x-3.0
[3.0.0-alpha]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-2.3...8.x-3.0-alpha
[2.3.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-2.2...8.x-2.3
[2.2.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-2.1...8.x-2.2
[2.1.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-2.0...8.x-2.1
[2.0.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-1.0...8.x-2.0
[1.0.0]: https://github.com/antistatique/drupal-template-whisperer/compare/8.x-0.0...8.x-1.0
[0.0.0]: https://github.com/antistatique/drupal-template-whisperer/releases/tags/8.x-0.0
