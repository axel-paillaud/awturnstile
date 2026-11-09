<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwTurnstile\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GeneralFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site_key', TextType::class, [
                'label' => $this->trans('Site key', 'Modules.Awturnstile.Admin'),
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('secret_key', TextType::class, [
                'label' => $this->trans('Secret key', 'Modules.Awturnstile.Admin'),
                'help' => $this->trans('Cloudflare test keys can be used on local environments.', 'Modules.Awturnstile.Admin'),
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ]);
    }
}
