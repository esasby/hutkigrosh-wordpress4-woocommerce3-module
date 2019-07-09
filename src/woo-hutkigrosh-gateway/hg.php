<?php

include_once('hutkigrosh_init.php');

use esas\hutkigrosh\controllers\ControllerAddBill;
use esas\hutkigrosh\controllers\ControllerAlfaclick;
use esas\hutkigrosh\controllers\ControllerCompletionPage;
use esas\hutkigrosh\controllers\ControllerNotify;
use esas\hutkigrosh\Registry;
use esas\hutkigrosh\view\admin\ConfigForm;
use esas\hutkigrosh\utils\Logger as HgLogger;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_HUTKIGROSH_GATEWAY extends WC_Payment_Gateway
{
    /**
     * @var ConfigForm
     */
    private $configForm;

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
        $this->id = "hutkigrosh";
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();
        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();
        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("plugin_title", 'woocommerce-hutkigrosh-payments');
        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("plugin_description", 'woocommerce-hutkigrosh-payments');
        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = Registry::getRegistry()->getConfigurationWrapper()->getPaymentMethodName();
        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;
        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;
        // Supports the default description
        $this->supports = array('');
        $this->description = wpautop(Registry::getRegistry()->getConfigurationWrapper()->getPaymentMethodDetails());
        // Save settings
        if (is_admin()) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        add_action('woocommerce_api_gateway_hutkigrosh', array($this, 'hutkigrosh_callback'));
        // добавляем хук для отображение ошибок, почему-то в wooсщььуксу ошибки валидации настроек не отображются по умолчанию
        add_action('woocommerce_update_options_payment_gateways_hutkigrosh', array($this, 'display_settings_errors'));
        add_filter('woocommerce_thankyou_' . $this->id, array($this, 'hutkigrosh_thankyou_text'));
    } // End __construct()

    /**
     * Переопределяем метод для подключения собственных валидаторов
     * @param string $key
     * @param array $field
     * @param array $post_data
     * @return string|void
     * @throws Exception
     */
    public function get_field_value($key, $field, $post_data = array())
    {
        $value = parent::get_field_value($key, $field, $post_data);
        $validationResult = $this->configForm->validate($key, $value);
        if (!$validationResult->isValid())
            throw new Exception($validationResult->getErrorTextFull()); //TODO
        return $value;
    }

    // Build the administration fields for this specific Gateway
    public function init_form_fields()
    {
        $this->configForm = Registry::getRegistry()->getConfigForm();
        $this->form_fields = $this->configForm->generate();
    }

    public function display_settings_errors()
    {
        $this->display_errors();
    }

    // Submit payment and handle response
    public function process_payment($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $orderWrapper = Registry::getRegistry()->getOrderWrapper($order_id);
            // проверяем, привязан ли к заказу billid, если да,
            // то счет не выставляем, а просто прорисовываем старницу
            if (empty($orderWrapper->getBillId())) {
                $controller = new ControllerAddBill();
                $controller->process($orderWrapper);
            }
            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } catch (Throwable $th) {
            wc_add_notice($th->getMessage(), 'error');
            return array(
                'result' => 'error',
                'redirect' => $this->get_return_url($order)
            );
        }
    }


    function hutkigrosh_thankyou_text($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $controller = new ControllerCompletionPage(
                admin_url('admin-ajax.php') . "?action=alfaclick",
                $order->get_checkout_order_received_url());
            $completionPanel = $controller->process($order_id);
            $completionPanel->render();
        } catch (Throwable $e) {
            HgLogger::getLogger("payment")->error("Exception:", $e);
        }
    }

    public function alfaclick_callback()
    {
        try {
            $controller = new ControllerAlfaclick();
            $controller->process($_POST['billid'], $_POST['phone']);
        } catch (Throwable $e) {
            HgLogger::getLogger("alfaclick")->error("Exception: ", $e);
        }
        wp_die();
    }


    public function hutkigrosh_callback()
    {
        try {
            $billId = $_GET['purchaseid'];
            $controller = new ControllerNotify();
            $controller->process($billId);
        } catch (Throwable $e) {
            HgLogger::getLogger("callback")->error("Exception:", $e);
        }
    }


}