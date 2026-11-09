# AwTurnstile

Protects the `ps_emailsubscription` newsletter form with [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/), by [Axelweb](https://axelweb.fr).

## Requirements

- PrestaShop 8.0+ with `ps_emailsubscription`
- PHP 8.1+ with the cURL extension
- Composer

## Installation

1. Run `composer install` in the module directory.
2. Install the module from the back-office.
3. Configure it: fill in the site key and the secret key.

The module stays inactive while a key is missing. Disable the module to turn Turnstile off.

## How it works

| Hook | Role |
|---|---|
| `actionFrontControllerSetMedia` | Loads the Turnstile API and `views/js/awturnstile.js` |
| `displayNewsletterRegistration` | Renders the widget inside the newsletter form |
| `actionNewsletterRegistrationBefore` | Verifies the token with the siteverify API, sets `hookError` on failure |

- Only subscriptions are checked, unsubscriptions are not.
- A network error towards Cloudflare rejects the subscription and is logged in the PrestaShop logs.
- The theme template must call `{hook h='displayNewsletterRegistration'}` inside the `<form>`.
- Turnstile tokens are single-use: `awturnstile.js` resets the widget after each AJAX submission.

## Test keys

For local environments ([Cloudflare docs](https://developers.cloudflare.com/turnstile/troubleshooting/testing/)):

| | Always passes | Always fails |
|---|---|---|
| Site key | `1x00000000000000000000AA` | `2x00000000000000000000AB` |
| Secret key | `1x0000000000000000000000000000000AA` | `2x0000000000000000000000000000000AA` |

## License

[Academic Free License 3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0)
