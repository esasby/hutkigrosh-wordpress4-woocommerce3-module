<?php

/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 30.09.2018
 * Time: 15:19
 */

namespace esas\hutkigrosh\view\admin;

use esas\hutkigrosh\view\admin\fields\ConfigField;
use esas\hutkigrosh\view\admin\fields\ConfigFieldCheckbox;
use esas\hutkigrosh\view\admin\fields\ConfigFieldList;
use esas\hutkigrosh\view\admin\fields\ConfigFieldPassword;
use esas\hutkigrosh\view\admin\fields\ConfigFieldStatusList;
use esas\hutkigrosh\view\admin\fields\ConfigFieldTextarea;
use esas\hutkigrosh\view\admin\fields\ListOption;

class ConfigFormWoo extends ConfigFormArray
{
    private $orderStatuses;

    /**
     * ConfigFieldsRenderOpencart constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $orderStatuses = $array = wc_get_order_statuses();
        foreach ($orderStatuses as $statusKey => $statusName) {
            $this->orderStatuses[$statusKey] = new ListOption($statusKey, $statusName);
        }
    }

    public function generateTextField(ConfigField $configField)
    {
        $ret = array(
            'title' => $configField->getName(),
            'type' => 'text',
            'desc_tip' => $configField->getDescription()
        );
        if ($configField->hasDefault()) {
            $ret['default'] = $configField->getDefault();
        }
        return $ret;
    }

    public function generateTextAreaField(ConfigFieldTextarea $configField)
    {
        $ret = array(
            'title' => $configField->getName(),
            'type' => 'textarea',
            'desc_tip' => $configField->getDescription(),
            'css' => 'max-width:80%;'
        );
        if ($configField->hasDefault()) {
            $ret['default'] = $configField->getDefault();
        }
        return $ret;
    }

    public function generatePasswordField(ConfigFieldPassword $configField)
    {
        return array(
            'title' => $configField->getName(),
            'type' => 'password',
            'desc_tip' => $configField->getDescription()
        );
    }


    public function generateCheckboxField(ConfigFieldCheckbox $configField)
    {
        $ret = array(
            'title' => $configField->getName(),
            'type' => 'checkbox',
            'desc_tip' => $configField->getDescription(),
        );
        if ($configField->hasDefault()) {
            $ret['default'] = $configField->getDefault() ? "yes" : "no";
        }
        return $ret;
    }

    public function generateListField(ConfigFieldList $configField)
    {
        $ret = array(
            'title' => $configField->getName(),
            'type' => 'select',
            'desc_tip' => $configField->getDescription(),
            'options' => wc_get_order_statuses()
        );
//        if ($configField->hasDefault()) {
//            $ret['default'] = $configField->getDefault();
//        }
        return $ret;
    }

    /**
     * @return ListOption[]
     */
    public function createStatusListOptions()
    {
        return $this->orderStatuses;
    }
}