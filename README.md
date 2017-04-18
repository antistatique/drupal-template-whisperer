# Template Whisperer

Provides a formalized way to declare and suggest page templates
using "Template Whisperer".

It is a continuation to something besides the standard
node.html.twig file for a variety of special case pages
like site news lists, contact page, ...

## You need Template Whisperer if

  - You want to generate a specific template of a node
    E.g. `node--article--xyz.html.twig`.
  - You want to allow the editors to freely include pre-defined content on the
    node by themselves.
    E.g. Add a list of the three latest news at the end of the content.

Template Whisperer can do a lot more than that,
but those are some of the obvious uses of Template Whisperer.

## Features

* An administration interface to manage Template Whisperer.

* Use of standard fields for entity support, allowing for translation and
  revisioning of Template Whisperer values added for individual entities.

## Standard usage scenario

1. Install the module.
2. Open admin/structure/template-whisperer.
3. Create your own suggestion by clicking "Add Template Whisperer.
4. Attach a new field "Template Whisperer" for a specific entity.

  4.1 Go to the "Manage fields" of the bundle where
  the Template Whisperer field should appear.
  4.2 Select "Template Whisperer" from the "Add a new field" selector.
  4.3 Fill in a label for the field, e.g. "Template Whisperer",
  and set an appropriate, machine name, e.g. "template_whisperer".
  4.4 Click "Save and continue".
  4.5 If the site supports multiple languages, and translations have been
  enabled for this entity, you can select "Users may translate this field" to
  use Drupal's translation system.

5. When you edit the content of that entity or
  bundle you should then see the new "Template Whisperer"
  section on the Advanced tabs.')

  5.1 Select the template you want to use for that entity or bundle
  5.2 You can now edit your template which is formatted this way:
  `[entity-type-id]--[entity-type]--list-news.html.twig`.

## Template Whisperer versions

Template Whisperer is only available for Drupal 8 !   
The module is ready to be used in Drupal 8, there are no known issues.

This version should work with all Drupal 8 releases, and it is always
recommended to keep Drupal core installations up to date.

## Dependencies

The Drupal 8 version of Template Whisperer requires nothing !
Feel free to use it.

## Supporting organizations

This project is sponsored by Antistatique. We are a Swiss Web Agency,
Visit us at [www.antistatique.net](https://www.antistatique.net) or
[Contact us](mailto:info@antistatique.net).

# Known issues

* In order to uninstall the module any "Template Whisperer"
  fields must first be removed from all entities.
  In order to see whether there are fields blocking the
  module from being uninstalled, load the module uninstall page
  (admin/modules/uninstall) and see if any is listed. This would look like
  something like this:
  _The Template Whisperer field type is used in the following field:
  node.field_template_whisperer_
  In order to uninstall the module, go to the appropriate field settings pages
  and remove any Template Whisperer fields.
  Once this is done it will be possible to uninstall the module.

* In order to uninstall the module any "Template Whisperer"
  entities must first be removed.
  In order to see whether there are entities blocking the
  module from being uninstalled, load the administration interface page
  (admin/structure/template-whisperer) and see if any are listed.
  In order to uninstall the module, delete every entities.
  Once this is done it will be possible to uninstall the module.
