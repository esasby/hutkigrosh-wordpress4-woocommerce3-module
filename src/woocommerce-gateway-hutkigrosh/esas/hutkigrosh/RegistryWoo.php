<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 01.10.2018
 * Time: 12:05
 */

namespace esas\hutkigrosh;


use esas\hutkigrosh\lang\TranslatorWoo;
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
}