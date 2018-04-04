<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 05.03.2018
 * Time: 12:17
 */

namespace esas\hutkigrosh\wrappers\woocommerce;

use WC_Settings_API;

class ConfigurationWrapperWoocommerce extends \esas\hutkigrosh\wrappers\ConfigurationWrapper
{
    private $settings_API;

    /**
     * ConfigurationWrapperWoocommerce constructor.
     */
    public function __construct(WC_Settings_API $settings_API)
    {
        $this->settings_API = $settings_API;
    }

    /**
     * Произольно название интернет-мазагина
     * @return string
     */
    public function getShopName()
    {
        $this->settings_API->get_option(self::CONFIG_HG_SHOP_NAME);
    }

    /**
     * Имя пользователя для доступа к системе ХуткиГрош
     * @return string
     */
    public function getHutkigroshLogin()
    {
        $this->settings_API->get_option(self::CONFIG_HG_LOGIN);
    }

    /**
     * Пароль для доступа к системе ХуткиГрош
     * @return string
     */
    public function getHutkigroshPassword()
    {
        $this->settings_API->get_option(self::CONFIG_HG_PASSWORD);
    }

    /**
     * Включен ли режим песчоницы
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->isOptionOn($this->settings_API->get_option(self::CONFIG_HG_SANDBOX));
    }

    /**
     * Уникальный идентификатор услуги в ЕРИП
     * @return string
     */
    public function getEripId()
    {
        return $this->settings_API->get_option(self::CONFIG_HG_ERIP_ID);
    }

    /**
     * Включена ля оповещение клиента по Email
     * @return boolean
     */
    public function isEmailNotification()
    {
        return $this->isOptionOn($this->settings_API->get_option(self::CONFIG_HG_EMAIL_NOTIFICATION));
    }

    /**
     * Включена ля оповещение клиента по Sms
     * @return boolean
     */
    public function isSmsNotification()
    {
        return $this->isOptionOn($this->settings_API->get_option(self::CONFIG_HG_SMS_NOTIFICATION));
    }

    /**
     * Итоговый текст, отображаемый клменту после успешного выставления счета
     * Чаще всего содержит подробную инструкцию по оплате счета в ЕРИП
     * @return string
     */
    public function getCompletionText()
    {
        $this->settings_API->get_option(self::CONFIG_HG_COMPLETION_TEXT);
    }

    /**
     * Какой статус присвоить заказу после успешно выставления счета в ЕРИП (на шлюз Хуткигрош_
     * @return string
     */
    public function getBillStatusPending()
    {
        return "pending";
    }

    /**
     * Какой статус присвоить заказу после успешно оплаты счета в ЕРИП (после вызова callback-а шлюзом ХуткиГрош)
     * @return string
     */
    public function getBillStatusPayed()
    {
        // TODO: Implement getBillStatusPayed() method.
    }

    /**
     * Какой статус присвоить заказу в случаче ошибки выставления счета в ЕРИП
     * @return string
     */
    public function getBillStatusFailed()
    {
        // TODO: Implement getBillStatusFailed() method.
    }

    /**
     * Какой статус присвоить заказу после успешно оплаты счета в ЕРИП (после вызова callback-а шлюзом ХуткиГрош)
     * @return string
     */
    public function getBillStatusCanceled()
    {
        // TODO: Implement getBillStatusCanceled() method.
    }

    private function isOptionOn($option)
    {
        return strtolower($option) == 'on';
    }
}