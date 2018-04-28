<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('BILLID_METADATA_KEY', 'hutkigrosh_bill_id');

class WC_HUTKIGROSH_GATEWAY extends WC_Payment_Gateway
{
    const HUTKIGROSH_STOREID = 'hutkigrosh_storeid';
    const HUTKIGROSH_STORE_NAME = 'hutkigrosh_store_name';
    const HUTKIGROSH_LOGIN = 'hutkigrosh_login';
    const HUTKIGROSH_PASSWORD = 'hutkigrosh_pswd';
    const HUTKIGROSH_EMAIL_NOTIFICATION = 'hutkigrosh_email_notification';
    const HUTKIGROSH_SMS_NOTIFICATION = 'hutkigrosh_sms_notification';
    const HUTKIGROSH_SANDBOX = 'hutkigrosh_sandbox';
    const HUTKIGROSH_CHECKOUT_SUCCESS_TEXT = 'hutkigrosh_checkout_success_text';
    const HUTKIGROSH_ORDER_STATUS_ERROR = 'hutkigrosh_order_status_error';

    protected static $plugin_options = null;

    protected static $_instance = null;

    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Setup our Gateway's id, description and other values
    function __construct()
    {
        // The global ID for this Payment method
        $this->id = strtolower(get_class($this));
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();
        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();
        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
            self::$plugin_options[$setting_key] = $value;
        }
        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("plugin_title", 'woocommerce-hutkigrosh-payments');
        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("plugin_description", 'woocommerce-hutkigrosh-payments');
        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = self::$plugin_options['hutkigrosh_payment_method_name'];
        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;
        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;
        // Supports the default description
        $this->supports = array('');
        $this->description = wpautop(self::$plugin_options['hutkigrosh_payment_method_description']);
        // Save settings
        if (is_admin()) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        add_action('woocommerce_api_gateway_hutkigrosh', array($this, 'hutkigrosh_callback'));
        add_filter('woocommerce_thankyou_' . $this->id, array($this, 'pay_buttons'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'hutkigrosh_thankyou_text'), 10, 2);
//        add_action('wp_ajax_alfaclick', array($this, 'alfaclick_callback'));
//        add_action('wp_ajax_nopriv_alfaclick', array($this, 'alfaclick_callback'));
//        add_action('wp_loaded', array( __CLASS__, 'alfaclick' ), 20 );
    } // End __construct()

    public function hutkigrosh_callback()
    {
        try {
            $biilId = $_GET['purchaseid'];
            $this->checkOrderStatus($biilId);
        } catch (Exception $e) {
            echo $e->getMessage(); //TODO не работает вывод клиенту
        }
    }

    protected function checkOrderStatus($purchaseid)
    {
        if (!isset($purchaseid)) {
            throw new Exception('Wrong purchaseid');
        }
        $hg = new \ESAS\HootkiGrosh\HootkiGrosh($this->get_option(self::HUTKIGROSH_SANDBOX));
        $res = $hg->apiLogIn($this->get_option(self::HUTKIGROSH_LOGIN), $this->get_option(self::HUTKIGROSH_PASSWORD));
        if (!$res) {
            $error = $hg->getError();
            $hg->apiLogOut(); // Завершаем сеанс
            throw new Exception($error);
        }
        #дополнительно проверим статус счета в hg
        $hgBillInfo = $hg->apiBillInfo($purchaseid);
        if (empty($hgBillInfo)) {
            $error = $hg->getError();
            $hg->apiLogOut(); // Завершаем сеанс
            throw new Exception($error);
        } else {
            $localOrderInfo = wc_get_order($hgBillInfo['invId']);
            if ($localOrderInfo->get_shipping_first_name() . ' ' . $localOrderInfo->get_shipping_last_name() != $hgBillInfo['fullName']
                && $localOrderInfo->get_total() != $hgBillInfo['amt']) {
                throw new Exception("Unmapped purchaseid");
            }
            if ($hgBillInfo['statusEnum'] == 'Payed') {
                $localOrderInfo->payment_complete();
            } elseif (in_array($hgBillInfo['statusEnum'], array('Outstending', 'DeletedByUser', 'PaymentCancelled'))) {
                $localOrderInfo->update_status("failed", __('order_status_failed', 'woocommerce-hutkigrosh-payments'));
            } elseif (in_array($hgBillInfo['statusEnum'], array('PaymentPending', 'NotSet'))) {
                $localOrderInfo->update_status("pending", __('order_status_pending', 'woocommerce-hutkigrosh-payments'));
            }
        }
    }


    function hutkigrosh_thankyou_text($thankyoutext, $order)
    {
        if ($order->get_payment_method() != 'wc_hutkigrosh_gateway')
            return;
        //TODO Тут можно обратиться в HG для получения деталей по выставленному счету, которые следует показать клиенту
        $message = wpautop(self::$plugin_options['hutkigrosh_checkout_success_text']);
        $message = str_replace("{{order_id}}", $order->get_id(), $message);
        return $message;
    }


    function pay_buttons($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $billId = get_post_meta($order->get_id(), BILLID_METADATA_KEY, true);
            $hg = new \ESAS\HootkiGrosh\HootkiGrosh($this->get_option(self::HUTKIGROSH_SANDBOX));
            $res = $hg->apiLogIn($this->get_option(self::HUTKIGROSH_LOGIN), $this->get_option(self::HUTKIGROSH_PASSWORD));
            if (!$res) {
                $error = $hg->getError();
                $hg->apiLogOut(); // Завершаем сеанс
                throw new Exception($error);
            }
            $webPayRq = new \ESAS\HootkiGrosh\WebPayRq();
            $webPayRq->billId = $billId;
            $webPayRq->returnUrl = $order->get_checkout_order_received_url() . '&webpay_status=payed';
            $webPayRq->cancelReturnUrl = $order->get_checkout_order_received_url() . '&webpay_status=failed';
            $webpayform = $hg->apiWebPay($webPayRq);
            $hg->apiLogOut();
            //echo $webpayform;
            $alfaclickbillID = $billId;
            $alfaclickTelephone = preg_replace("/[^0-9]/", '', $order->get_billing_phone());
            $alfaclickUrl = admin_url('admin-ajax.php');
            $alfaclickButtonLabel = "Выставить счет в Альфа-клик";
            include(ABSPATH . 'wp-content/plugins/woocommerce-gateway-hutkigrosh/templates/buttons.php');
//            wc_get_template('buttons.php', null, 'woocommerce-gateway-hutkigrosh/templates');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function alfaclick_callback()
    {
        $hg = new \ESAS\HootkiGrosh\HootkiGrosh(self::$plugin_options[self::HUTKIGROSH_SANDBOX]);
        $res = $hg->apiLogIn(self::$plugin_options[self::HUTKIGROSH_LOGIN], self::$plugin_options[self::HUTKIGROSH_PASSWORD]);
        if (!$res) {
            echo $hg->getError();
            $hg->apiLogOut();
            exit;
        }
        $alfaclickRq = new \ESAS\HootkiGrosh\AlfaclickRq();
        $alfaclickRq->billId = $_POST['billid']; //TODO может как-то иначе получать параметры запроса
        $alfaclickRq->phone = $_POST['phone'];

        $responceXML = $hg->apiAlfaClick($alfaclickRq);
        $hg->apiLogOut();
        // wc_print_notice не работает здесь, т.к. вызов идет через AJAX
        echo intval($responceXML->__toString()) == '0' ? "error" : "ok";
        wp_die();
    }

    // Build the administration fields for this specific Gateway
    public function init_form_fields()
    {
        $this->form_fields = array(
            //Включение шлюза
            'enabled' => array(
                'title' => __('enable_disable_payments_gateway', 'woocommerce-hutkigrosh-payments'),
                'label' => __('enable_disable_payments_gateway_label', 'woocommerce-hutkigrosh-payments'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            //Id магазина
            self::HUTKIGROSH_STOREID => array(
                'title' => __('hutkigrosh_storeid_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'text',
                'desc_tip' => __('hutkigrosh_storeid_desk', 'woocommerce-hutkigrosh-payments'),
                'placeholder' => '000',
            ),
            //Имя поставщивка услуги в ЕРИП
            self::HUTKIGROSH_STORE_NAME => array(
                'title' => __('hutkigrosh_store_name_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'text',
                'desc_tip' => __('hutkigrosh_store_name_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => '',
            ),
            //Имя пользователя для доступа к системе ХуткиГрош
            self::HUTKIGROSH_LOGIN => array(
                'title' => __('hutkigrosh_login_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'text',
                'desc_tip' => __('hutkigrosh_login_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => '',
            ),
            //Пароль для доступа к системе ХуткиГрош
            self::HUTKIGROSH_PASSWORD => array(
                'title' => __('hutkigrosh_pswd_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'text',
                'desc_tip' => __('hutkigrosh_pswd_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => '',
            ),
            //Имя способа оплаты на странице оформления оплаты (отображается клиенту)
            'hutkigrosh_payment_method_name' => array(
                'title' => __('hutkigrosh_payment_method_name_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'text',
                'desc_tip' => __('hutkigrosh_payment_method_name_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => __('hutkigrosh_payment_method_name_default', 'woocommerce-hutkigrosh-payments'),
            ),
            //Описание способа оплаты для покупателя
            'hutkigrosh_payment_method_description' => array(
                'title' => __('hutkigrosh_payment_method_description_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'textarea',
                'desc_tip' => __('hutkigrosh_payment_method_description_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => __('hutkigrosh_payment_method_description_default', 'woocommerce-hutkigrosh-payments'),
                'css' => 'max-width:80%;'
            ),
            //Текст, отображаемый клиенту при успешном выставлении счета
            self::HUTKIGROSH_CHECKOUT_SUCCESS_TEXT => array(
                'title' => __('hutkigrosh_checkout_success_text_title', 'woocommerce-hutkigrosh-payments'),
                'type' => 'textarea',
                'desc_tip' => __('hutkigrosh_checkout_success_text_desk', 'woocommerce-hutkigrosh-payments'),
                'default' => __('hutkigrosh_checkout_success_text_default', 'woocommerce-hutkigrosh-payments'),
                'css' => 'max-width:80%;'
            ),
            self::HUTKIGROSH_EMAIL_NOTIFICATION => array(
                'title' => __("hutkigrosh_email_notification_title", 'woocommerce-hutkigrosh-payments'),
                'desc_tip' => __('hutkigrosh_email_notification_desk', 'woocommerce-hutkigrosh-payments'),
                'label' => __('hutkigrosh_email_notification_label', 'woocommerce-hutkigrosh-payments'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            self::HUTKIGROSH_SMS_NOTIFICATION => array(
                'title' => __("hutkigrosh_sms_notification_title", 'woocommerce-hutkigrosh-payments'),
                'desc_tip' => __('hutkigrosh_sms_notification_desk', 'woocommerce-hutkigrosh-payments'),
                'label' => __('hutkigrosh_sms_notification_label', 'woocommerce-hutkigrosh-payments'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            self::HUTKIGROSH_SANDBOX => array(
                'title' => __("hutkigrosh_sandbox_title", 'woocommerce-hutkigrosh-payments'),
                'desc_tip' => __('hutkigrosh_sandbox_desk', 'woocommerce-hutkigrosh-payments'),
                'label' => __('hutkigrosh_sandbox_label', 'woocommerce-hutkigrosh-payments'),
                'type' => 'checkbox',
                'default' => 'no',
            )
        );
    }

    /*
        Создание Счета на оплату в системе ЕРИП
    */
    public function add_bill(WC_Order &$order_sybmol_link)
    {
        try {
            $billNewRq = new \ESAS\HootkiGrosh\BillNewRq();
            $billNewRq->eripId = $this->get_option(self::HUTKIGROSH_STOREID);
            $billNewRq->invId = $order_sybmol_link->get_order_number();
            $billNewRq->fullName = $order_sybmol_link->get_shipping_first_name() . ' ' . $order_sybmol_link->get_shipping_last_name();
            $billNewRq->mobilePhone = $order_sybmol_link->get_billing_phone();
            $billNewRq->email = $order_sybmol_link->get_billing_email();
            $billNewRq->fullAddress = $order_sybmol_link->get_shipping_country() . ' ' . $order_sybmol_link->get_shipping_city() . ' ' . $order_sybmol_link->get_shipping_address_1() . ' ' . $order_sybmol_link->get_shipping_address_2();
            $billNewRq->amount = $order_sybmol_link->get_total();
            $billNewRq->currency = $order_sybmol_link->get_currency();
            $billNewRq->notifyByEMail = $this->get_option(self::HUTKIGROSH_EMAIL_NOTIFICATION);
            $billNewRq->notifyByMobilePhone = $this->get_option(self::HUTKIGROSH_SMS_NOTIFICATION);
            // добавляем информацию о заказах
            $line_items = $order_sybmol_link->get_items();
            if (is_array($line_items)) {
                foreach ($line_items as $line_item) {
                    $arItem['invItemId'] = $line_item->get_product_id();
                    $arItem['desc'] = $line_item->get_name();
                    $arItem['count'] = $line_item->get_quantity();
                    $arItem['amt'] = $line_item->get_total();
                    $arItems[] = $arItem;
                    unset($arItem);
                }
            }
            $billNewRq->products = $arItems;


            $hg = new \ESAS\HootkiGrosh\HootkiGrosh($this->get_option(self::HUTKIGROSH_SANDBOX));
            $res = $hg->apiLogIn($this->get_option(self::HUTKIGROSH_LOGIN), $this->get_option(self::HUTKIGROSH_PASSWORD));

            // Ошибка авторизации
            if (!$res) {
                $error = $hg->getError();
                $hg->apiLogOut(); // Завершаем сеанс
                throw new Exception($error);
            }
            $billID = $hg->apiBillNew($billNewRq);
            if (!$billID) {
                $error = $hg->getError();
                $hg->apiLogOut(); // Завершаем сеанс
                throw new Exception($error);
            }
            update_post_meta($order_sybmol_link->get_order_number(), BILLID_METADATA_KEY, $billID);
            return true;
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return false;
        }
    }


    // Submit payment and handle response
    public function process_payment($order_id)
    {
        global $woocommerce;

        $order = wc_get_order($order_id);
        //Создаем заказ в системе ЕРИП
        $hgResp = $this->add_bill($order);
        if ($hgResp) {
            // Remove cart
            $woocommerce->cart->empty_cart();
            // Mark as pending
            $order->update_status('pending', __('order_status_pending', 'woocommerce-hutkigrosh-payments'));
            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            return array(
                'result' => 'error',
                'redirect' => $this->get_return_url($order)
            );
        }
    }
}

/*
	Класс для обработки callback от ЕРИП
*/

//class WC_ERIP extends WC_HUTKIGROSH_GATEWAY
//{
//    public function __construct()
//    {
//        add_action('woocommerce_api_' . strtolower(get_class($this)), array(&$this, 'handle_callback'));
//    }
//
//    public function handle_callback()
//    {
//        $postData = (string)file_get_contents("php://input");
//        $post_array = json_decode($postData, false);
//        $order_id = $post_array->transaction->order_id;
//        $order_key = $post_array->transaction->tracking_id;
//        $status = $post_array->transaction->status;
//        global $woocommerce;
//        $order = wc_get_order($order_id);
//        if (!$order || $order->get_order_key() !== $order_key) {
//            die('ERROR');
//        }
//        if ($post_array->transaction->status == 'successful') {
//            $order->update_status('processing', 'Оплата через ЕРИП произведена');
//            $response = 'OK Данные успешно получены! Заказ поставлен на выполнение.';
//        } else {
//            $response = 'OK Данные успешно получены! Статус заказа не изменён.';
//        }
//        die($response);
//    }
//}