<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ScamStop extends Module
{
    private $moduleLogFile = _PS_MODULE_DIR_.'scamstop/log';

    public function __construct()
    {
        $this->name = 'scamstop';
        $this->tab = 'administration';
        $this->version = '1.0';
        $this->author = 'Christopher Morton';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('ScamStop');
        $this->description = $this->l('Stops URLs from being added in customer names');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Install.
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (parent::install()
            && $this->registerHook('actionBeforeSubmitAccount')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            return true;
        }

        return false;
    }

    public function hookActionBeforeSubmitAccount()
    {
        $lastname = Tools::getValue('customer_lastname');
        $firstname = Tools::getValue('customer_firstname');
        if (Validate::isUrl($lastname) || Valiidate::isUrl($firstname)) {
            $logData = date(DATE_ISO8601)." REJECTED: $firstname $lastname\n";
            file_put_contents($this->moduleLogFile, $logData, FILE_APPEND);
            $this->context->controller->errors[] = 
                Tools::displayError('Invalid account credentials');
        }
    }
}
