<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use Axelweb\AwTurnstile\Form\GeneralDataConfiguration;
use Axelweb\AwTurnstile\Service\TurnstileVerifier;

class AwTurnstile extends Module
{
    /** Turnstile action name, shown in the Cloudflare analytics */
    public const TURNSTILE_ACTION = 'newsletter';

    /**
     * Verification result for the current request: the same token
     * cannot be verified twice (Cloudflare rejects duplicates).
     *
     * @var bool|null
     */
    private $verified;

    public function __construct()
    {
        $this->name = 'awturnstile';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Axelweb';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Cloudflare Turnstile', [], 'Modules.Awturnstile.Admin');
        $this->description = $this->trans('Protects the newsletter subscription form with Cloudflare Turnstile.', [], 'Modules.Awturnstile.Admin');

        $this->ps_versions_compliancy = [
            'min' => '8.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function install(): bool
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $installed = parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayNewsletterRegistration')
            && $this->registerHook('actionNewsletterRegistrationBefore')
            && Configuration::updateValue(GeneralDataConfiguration::AWTURNSTILE_SITE_KEY, '')
            && Configuration::updateValue(GeneralDataConfiguration::AWTURNSTILE_SECRET_KEY, '');

        // Prevent 'Unable to generate a URL for the named route [...]' error
        if ($installed) {
            Tools::clearSf2Cache();
        }

        return $installed;
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && Configuration::deleteByName(GeneralDataConfiguration::AWTURNSTILE_SITE_KEY)
            && Configuration::deleteByName(GeneralDataConfiguration::AWTURNSTILE_SECRET_KEY);
    }

    /**
     * Redirect to the Symfony configuration page
     */
    public function getContent(): void
    {
        $route = $this->get('router')->generate('awturnstile_form_configuration');
        Tools::redirectAdmin($route);
    }

    /**
     * Load the Turnstile API and the reset script on front pages
     */
    public function hookActionFrontControllerSetMedia(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $this->context->controller->registerJavascript(
            'awturnstile-api',
            'https://challenges.cloudflare.com/turnstile/v0/api.js',
            [
                'server' => 'remote',
                'position' => 'bottom',
                'priority' => 200,
                'attributes' => 'defer',
            ]
        );

        $this->context->controller->registerJavascript(
            'awturnstile-script',
            'modules/' . $this->name . '/views/js/awturnstile.js',
            [
                'position' => 'bottom',
                'priority' => 210,
            ]
        );
    }

    /**
     * Render the Turnstile widget inside the newsletter form
     */
    public function hookDisplayNewsletterRegistration(): string
    {
        if (!$this->isActive()) {
            return '';
        }

        $siteKey = (string) Configuration::get(GeneralDataConfiguration::AWTURNSTILE_SITE_KEY);

        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey, ENT_QUOTES)
            . '" data-action="' . self::TURNSTILE_ACTION . '"></div>';
    }

    /**
     * Reject the subscription if the Turnstile token is missing or invalid.
     * Unsubscription (action = 1) is not checked.
     *
     * @param array $params hookError is passed by reference
     */
    public function hookActionNewsletterRegistrationBefore(array $params): void
    {
        if (!$this->isActive() || (string) ($params['action'] ?? '') === '1') {
            return;
        }

        if ($this->verified === null) {
            $verifier = new TurnstileVerifier(
                (string) Configuration::get(GeneralDataConfiguration::AWTURNSTILE_SECRET_KEY)
            );
            $this->verified = $verifier->verify(
                (string) Tools::getValue('cf-turnstile-response', ''),
                Tools::getRemoteAddr()
            );
        }

        if (!$this->verified) {
            $params['hookError'] = $this->trans(
                'Anti-spam verification failed, please try again.',
                [],
                'Modules.Awturnstile.Shop'
            );
        }
    }

    /**
     * Both keys filled in
     */
    private function isActive(): bool
    {
        return Configuration::get(GeneralDataConfiguration::AWTURNSTILE_SITE_KEY)
            && Configuration::get(GeneralDataConfiguration::AWTURNSTILE_SECRET_KEY);
    }
}
