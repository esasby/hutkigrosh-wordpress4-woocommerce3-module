<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 01.10.2018
 * Time: 12:05
 */

namespace esas\hutkigrosh;


use esas\hutkigrosh\lang\TranslatorWoo;
use esas\hutkigrosh\view\client\CompletionPanelWoo;
use esas\hutkigrosh\view\admin\ConfigFormWoo;
use esas\hutkigrosh\view\admin\fields\ConfigFieldCheckbox;
use esas\hutkigrosh\wrappers\ConfigurationWrapperWoo;
use esas\hutkigrosh\wrappers\OrderWrapperWoo;

class RegistryWoo extends Registry
{
    public function createConfigurationWrapper()
    {

        return new ConfigurationWrapperWoo();
    }

    public function createTranslator()
    {
        return new TranslatorWoo();
    }

    public function getOrderWrapper($orderNumber) {
        return new OrderWrapperWoo($orderNumber);
    }

    public function createConfigForm()
    {
        $configForm = new ConfigFormWoo();
        $configForm->addAllExcept([ConfigurationFields::shopName()]);
        $configForm->addField(new ConfigFieldCheckbox(
            'enabled',
            __('enable_disable_payments_gateway', 'woocommerce-hutkigrosh-payments'),
            __('enable_disable_payments_gateway_desc', 'woocommerce-hutkigrosh-payments')
        ));
        return $configForm;
    }

    public function getCompletionPanel($orderWrapper)
    {
        $completionPanel = new CompletionPanelWoo($orderWrapper);
        return $completionPanel;
    }
}