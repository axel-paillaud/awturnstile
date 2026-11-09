<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwTurnstile\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

class AwTurnstileController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function index(): Response
    {
        return $this->render('@Modules/awturnstile/views/templates/admin/awturnstile.html.twig');
    }
}
