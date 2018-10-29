<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 27.09.2018
 * Time: 12:31
 */

namespace esas\hutkigrosh\wrappers;

use Exception;

class ConfigurationWrapperWoo extends ConfigurationWrapper
{
    private $settings;

    /**
     * ConfigurationWrapperWoo constructor.
     * @param $config
     */
    public function __construct()
    {
        parent::__construct();
        $this->settings = get_option("woocommerce_hutkigrosh_settings", null);
    }


    /**
     * @param $key
     * @return string
     * @throws Exception
     */
    public function getCmsConfig($key)
    {
        return $this->settings[$key];
    }

    /**
     * @param $cmsConfigValue
     * @return bool
     * @throws Exception
     */
    public function convertToBoolean($cmsConfigValue)
    {
        return strtolower($cmsConfigValue) == 'yes';
    }
}