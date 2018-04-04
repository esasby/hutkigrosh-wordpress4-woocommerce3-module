<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 05.03.2018
 * Time: 12:09
 */

namespace esas\hutkigrosh\wrappers\woocommerce;

use WC_Order;

class OrderWrapperWoocommerce extends \esas\hutkigrosh\wrappers\OrderWrapper
{
    private $wc_order;

    /**
     * OrderWrapperWoocommerce constructor.
     */
    public function __construct(WC_Order $wc_order)
    {
        $this->wc_order = $wc_order;
    }


    /**
     * Уникальный номер заказ в рамках CMS
     * @return string
     */
    public function getOrderId()
    {
        return $this->wc_order->get_order_number();
    }

    /**
     * Полное имя покупателя
     * @return string
     */
    public function getFullName()
    {
        return $this->wc_order->get_shipping_first_name() . ' ' . $this->wc_order->get_shipping_last_name();
    }

    /**
     * Мобильный номер покупателя для sms-оповещения
     * (если включено администратором)
     * @return string
     */
    public function getMobilePhone()
    {
        return $this->wc_order->get_billing_phone();
    }

    /**
     * Email покупателя для email-оповещения
     * (если включено администратором)
     * @return string
     */
    public function getEmail()
    {
        return $this->wc_order->get_billing_email();
    }

    /**
     * Физический адрес покупателя
     * @return string
     */
    public function getAddress()
    {
        return $this->wc_order->get_shipping_country() . ' '
            . $this->wc_order->get_shipping_city() . ' '
            . $this->wc_order->get_shipping_address_1() . ' '
            . $this->wc_order->get_shipping_address_2();
    }

    /**
     * Общая сумма товаров в заказе
     * @return string
     */
    public function getAmount()
    {
        return $this->wc_order->get_total();
    }

    /**
     * Валюта заказа (буквенный код)
     * @return string
     */
    public function getCurrency()
    {
        return $this->wc_order->get_currency();
    }

    /**
     * Массив товаров в заказе
     * @return OrderProductWrapper[]
     */
    public function getProducts()
    {
        return $this->wc_order->get_items();
    }

    /**
     * BillId (идентификатор хуткигрош) успешно выставленного счета
     * @return mixed
     */
    public function getBillId()
    {
        // TODO: Implement getBillId() method.
    }

    /**
     * Текущий статус заказа в CMS
     * @return mixed
     */
    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }
}