<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwTurnstile\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

/**
 * Reads and writes the module settings in the configuration table.
 */
final class GeneralDataConfiguration implements DataConfigurationInterface
{
    public const AWTURNSTILE_SITE_KEY = 'AWTURNSTILE_SITE_KEY';
    public const AWTURNSTILE_SECRET_KEY = 'AWTURNSTILE_SECRET_KEY';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        return [
            'site_key' => (string) $this->configuration->get(static::AWTURNSTILE_SITE_KEY),
            'secret_key' => (string) $this->configuration->get(static::AWTURNSTILE_SECRET_KEY),
        ];
    }

    public function updateConfiguration(array $configuration): array
    {
        if (!$this->validateConfiguration($configuration)) {
            return ['Invalid configuration payload.'];
        }

        $siteKey = trim((string) $configuration['site_key']);
        $secretKey = trim((string) $configuration['secret_key']);

        $this->configuration->set(static::AWTURNSTILE_SITE_KEY, $siteKey);
        $this->configuration->set(static::AWTURNSTILE_SECRET_KEY, $secretKey);

        return [];
    }

    public function validateConfiguration(array $configuration): bool
    {
        return \array_key_exists('site_key', $configuration)
            && \array_key_exists('secret_key', $configuration);
    }
}
