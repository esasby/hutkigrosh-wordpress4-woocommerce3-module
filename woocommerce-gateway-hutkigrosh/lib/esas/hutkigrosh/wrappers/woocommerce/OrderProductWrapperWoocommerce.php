<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 05.03.2018
 * Time: 12:20
 */

namespace esas\hutkigrosh\wrappers\woocommerce;

use WC_Order_Item;

class OrderProductWrapperWoocommerce extends \esas\hutkigrosh\wrappers\OrderProductWrapper
{
    private $line_item;

    /**
     * OrderProductWrapperWoocommerce constructor.
     * @param $line_item
     */
    public function __construct($line_item)
    {
        $this->line_item = $line_item;
    }


    /**
     * Артикул товара
     * @return string
     */
    public function getInvId()
    {
        return $this->line_item->get_product_id();
    }

    /**
     * Название или краткое описание товара
     * @return string
     */
    public function getName()
    {
        return $this->line_item->get_name();
    }

    /**
     * Количество товароа в корзине
     * @return mixed
     */
    public function getCount()
    {
        return $this->line_item->get_quantity();
    }

    /**
     * Цена за единицу товара
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->line_item->get_total();
    }
}