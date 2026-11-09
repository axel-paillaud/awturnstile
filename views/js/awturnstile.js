/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

// A Turnstile token is single-use: reset the widget after each AJAX
// submission of ps_emailsubscription so a new attempt gets a fresh token.
document.addEventListener('DOMContentLoaded', function () {
  if (typeof window.jQuery === 'undefined') {
    return;
  }

  window.jQuery(document).ajaxComplete(function (event, xhr, settings) {
    if (
      typeof window.turnstile === 'undefined'
      || typeof window.psemailsubscription_subscription === 'undefined'
      || settings.url !== window.psemailsubscription_subscription
    ) {
      return;
    }

    document.querySelectorAll('.cf-turnstile').forEach(function (widget) {
      window.turnstile.reset(widget);
    });
  });
});
