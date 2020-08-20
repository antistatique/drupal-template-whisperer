CHANGELOG
---------

## NEXT RELEASE
 - Issue #3087966 by wengerk: Support for "Negate the condition" on the Template Whisperer Block Layout Visibility configuration
 - Issue #3166328 by wengerk: TemplateWhispererManager::getFieldSuggestions may throw `Call to a member function getSuggestion() on null`
 - Rewrite Travis Integration to use Docker instead of Drupal_TI

## 8.x-3.0-alpha (2020-03-27)
 - Issue #3090756 by wengerk: Drupal 9 Readiness
 - Issue #3151185 by wengerk: Drupal 9 - Tests deprecation notice assertInternalType

## 8.x-2.3 (2019-12-03)
 - Issue #3008554 by wengerk, valthebald: Display widget in advanced group only when it exists.
 - Issue #3044924 - fix Drupal-CI Composer failure since Drupal 8.7.x+ - Update of drupal/coder squizlabs/php_codesniffer"
 - Issue #3087804 by wengerk, AurianaHg: Support for Page level suggestion
 
## 8.x-2.2 (2018-06-05)
 - Fix Token suggestion my crash on unused suggestion lookup - Issue 2974817.
 - Add better warning of suggestion usage before deletion.
 - Fix Undefined index: handler when updating a Template Whisperer Settings - Issue 2935078.

## 8.x-2.1 (2018-05-16)
 - Fix suggestions too much permissive which leads in wrong suggestions usage - Issue 2944054.
 - Update to PHPUnit 6.x & Drupal 8.6.x for testing on TravisCI.
 - Fix uninstall/reinstall by removing garbage configurations.

## 8.x-2.0 (2018-05-15)
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

## 8.x-1.0 (2017-04-18)
 - Refactoring Template Whisperer as ConfigEntity instead of ContentEntity.
 - Rewording Template Whisperer with the idea of suggestion(s).
 - Adding Usage admin page by Template Whisperer suggestion(s).

## 8.x-0.0 (2017-03-07)
 - Add BrowserTestBase to test web-based behaviors and interactions.

## 8.x-0.0 (2017-02-27)
 - First draft.
