<?php
require_once(dirname(__FILE__) . '/vendor/esas/hutkigrosh-api-php/src/esas/hutkigrosh/CmsPlugin.php');
use esas\hutkigrosh\CmsPlugin;
use esas\hutkigrosh\RegistryWoo;


(new CmsPlugin(dirname(__FILE__) . '/vendor', dirname(__FILE__)))
    ->setRegistry(new RegistryWoo())
    ->init();
