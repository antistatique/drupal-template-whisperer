/**
* Attached on the Template Whisperer Condition Plugin
* @preserve
**/

(function ($, window, Drupal) {
  Drupal.behaviors.blockTemplateWhispererSummary = {
    attach: function attach() {
      if (typeof $.fn.drupalSetSummary === 'undefined') {
        return;
      }

      function checkboxesSummary(context) {
        var vals = [];
        var $checkboxes = $(context).find('input[type="checkbox"]:checked + label');
        var il = $checkboxes.length;
        for (var i = 0; i < il; i++) {
          vals.push($($checkboxes[i]).html());
        }
        if (!vals.length) {
          vals.push(Drupal.t('Not restricted'));
        }
        return vals.join(', ');
      }

      $('[data-drupal-selector="edit-visibility-template-whisperer"]').drupalSetSummary(checkboxesSummary);
    }
  };

})(jQuery, window, Drupal);