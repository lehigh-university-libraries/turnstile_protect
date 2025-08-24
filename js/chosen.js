(function ($, Drupal) {
  Drupal.behaviors.turnstileProtectChosenInit = {
    attach: function (context, settings) {
      if (typeof jQuery.trim !== 'function') {
        jQuery.trim = function(str) {
          return str == null ? "" : String(str).trim();
        };
      }
      $(once('chosen', 'select.chosen-select', context)).each(function () {
        $(this).chosen({
          width: '100%',
          placeholder_text_multiple: Drupal.t('Select routes...'),
          allow_single_deselect: true,
        });
      })
    }
  };
})(jQuery, Drupal);
