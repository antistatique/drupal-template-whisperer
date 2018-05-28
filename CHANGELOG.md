CHANGELOG
---------

## NEXT RELEASE
 - Fix Token suggestion my crash on unused suggestion lookup - Issue 2974817.

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
