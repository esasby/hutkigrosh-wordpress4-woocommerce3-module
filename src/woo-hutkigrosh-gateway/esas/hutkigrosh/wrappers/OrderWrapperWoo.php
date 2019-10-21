<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 27.09.2018
 * Time: 13:08
 */

namespace esas\hutkigrosh\wrappers;

use Throwable;
use WC_Order;
use WC_Order_Item;

class OrderWrapperWoo extends OrderSafeWrapper
{
    private $wc_order;

    /**
     * OrderWrapperWoo constructor.
     */
    public function __construct($order_id)
    {
        parent::__construct();
        $this->wc_order = wc_get_order($order_id);
    }


    /**
     * Уникальный номер заказ в рамках CMS
     * @return string
     * @throws Throwable
     */
    public function getOrderIdUnsafe()
    {
        return $this->wc_order->get_id();
    }

    public function getOrderNumber()
    {
        return $this->wc_order->get_order_number(); // может отличаться от внутреннего идентификатора, если используются хуки, меняющие номера заказаов
    }


    /**
     * Полное имя покупателя
     * @throws Throwable
     * @return string
     */
    public function getFullNameUnsafe()
    {
        $fullName = trim($this->wc_order->get_billing_first_name() . ' ' . $this->wc_order->get_billing_last_name());
        if ($fullName == "")
            $fullName = trim($this->wc_order->get_shipping_first_name() . ' ' . $this->wc_order->get_shipping_last_name());
        return $fullName;
    }

    /**
     * Мобильный номер покупателя для sms-оповещения
     * (если включено администратором)
     * @throws Throwable
     * @return string
     */
    public function getMobilePhoneUnsafe()
    {
        return $this->wc_order->get_billing_phone();
    }

    /**
     * Email покупателя для email-оповещения
     * (если включено администратором)
     * @throws Throwable
     * @return string
     */
    public function getEmailUnsafe()
    {
        return $this->wc_order->get_billing_email();
    }

    /**
     * Физический адрес покупателя
     * @throws Throwable
     * @return string
     */
    public function getAddressUnsafe()
    {
        $address = trim($this->wc_order->get_billing_country() . ' '
            . $this->wc_order->get_billing_city() . ' '
            . $this->wc_order->get_billing_address_1() . ' '
            . $this->wc_order->get_billing_address_2());
        if ($address == "")
            $address = trim($this->wc_order->get_shipping_country() . ' '
                . $this->wc_order->get_shipping_city() . ' '
                . $this->wc_order->get_shipping_address_1() . ' '
                . $this->wc_order->get_shipping_address_2());
        return $address;
    }

    /**
     * Общая сумма товаров в заказе
     * @throws Throwable
     * @return string
     */
    public function getAmountUnsafe()
    {
        return $this->wc_order->get_total();
    }

    /**
     * Валюта заказа (буквенный код)
     * @throws Throwable
     * @return string
     */
    public function getCurrencyUnsafe()
    {
        return $this->wc_order->get_currency();
    }

    /**
     * Массив товаров в заказе
     * @throws Throwable
     * @return OrderProductWrapper[]
     */
    public function getProductsUnsafe()
    {
        $products = $this->wc_order->get_items();
        foreach ($products as $product)
            $productsWrappers[] = new OrderProductWrapperWoo($product);
        return $productsWrappers;
    }

    const BILLID_METADATA_KEY = 'hutkigrosh_bill_id';

    /**
     * BillId (идентификатор хуткигрош) успешно выставленного счета
     * @throws Throwable
     * @return mixed
     */
    public function getBillIdUnsafe()
    {
        return $this->wc_order->get_meta(self::BILLID_METADATA_KEY);
    }

    /**
     * Текущий статус заказа в CMS
     * @return mixed
     * @throws Throwable
     */
    public function getStatusUnsafe()
    {
        return $this->wc_order->get_status();
    }

    /**
     * Обновляет статус заказа в БД
     * @param $newStatus
     * @return mixed
     * @throws Throwable
     */
    public function updateStatus($newStatus)
    {
        $this->wc_order->update_status($newStatus);
    }

    /**
     * Сохраняет привязку billid к заказу
     * @param $billId
     * @return mixed
     * @throws Throwable
     */
    public function saveBillId($billId)
    {
        $this->wc_order->update_meta_data( self::BILLID_METADATA_KEY, $billId);
        $this->wc_order->save();
    }
}