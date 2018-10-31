<?php
/*
 * Copyright (c) 2018 PayGate (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class SID extends PaymentModule
{
    private $_html       = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name    = 'sid'; // Name of module
        $this->tab     = 'payments_gateways'; // Where module will be found
        $this->version = '1.0.0';
        $this->author  = 'SiD Instant EFT';

        $this->currencies      = true;
        $this->currencies_mode = 'radio';
        $this->bootstrap       = true;

        parent::__construct();

        $this->page             = basename( __FILE__, '.php' );
        $this->displayName      = $this->l( 'SID Instant EFT' );
        $this->description      = $this->l( 'Accept payments by SID Instant EFT' );
        $this->confirmUninstall = $this->l( 'Are you sure you want to delete your details ?' );
        if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
            $this->warning = $this->l( 'You are running under localhost, we cannot validate order.' );
        }
    }

    public function install()
    {
        $order_state = new OrderState();
        $langs       = Language::getLanguages();
        foreach ( $langs as $lang ) {
            $order_state->name[$lang['id_lang']] = $this->l( 'Awaiting SID payment' );
        }
        $order_state->color = '#509ACA';

        if ( !parent::install()
            or !Configuration::updateValue( 'SID_URL', 'https://www.sidpayment.com/paySID/' )
            or !Configuration::updateValue( 'SID_MERCHANT', 'PHONE7' )
            or !Configuration::updateValue( 'SID_PRIVATE_KEY', '539B47C2B8D6C4CCFA5CC820A22D9529RldHSDU2NjIKRldHSDU2NjI1MzlCNDdDMkI4RDZDNENDRkE1Q0M4MjBBMjJEOTUyOpDc' )
            or !$this->registerHook( 'paymentOptions' )
            or !$this->registerHook( 'paymentReturn' )
            or !$order_state->add()
            or !Configuration::updateValue( 'SID_PAYMENT_PENDING_STATUS', $order_state->id ) ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $order_state = new OrderState( Configuration::get( 'SID_PAYMENT_PENDING_STATUS' ) );

        if ( !$order_state->delete()
            or !Configuration::deleteByName( 'SID_PAYMENT_PENDING_STATUS' )
            or !Configuration::deleteByName( 'SID_URL' )
            or !Configuration::deleteByName( 'SID_MERCHANT' )
            or !Configuration::deleteByName( 'SID_PRIVATE_KEY' )
            or !parent::uninstall() ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        if ( Tools::isSubmit( 'btnSubmit' ) ) {
            $this->_postValidation();
            if ( !count( $this->_postErrors ) ) {
                $this->_postProcess();
            } else {
                foreach ( $this->_postErrors as $err ) {
                    $this->_html .= $this->displayError( $err );
                }
            }

        }

        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l( 'Settings' ),
                    'icon'  => 'icon-gear',
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l( 'Merchant Code' ),
                        'name'     => 'SID_MERCHANT',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l( 'SID URL' ),
                        'name'     => 'SID_URL',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l( 'Private Key' ),
                        'name'     => 'SID_PRIVATE_KEY',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l( 'Save' ),
                ),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language( (int) Configuration::get( 'PS_LANG_DEFAULT' ) );
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' ) ? Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' ) : 0;
        $this->fields_form                = array();
        $helper->id                       = (int) Tools::getValue( 'id_carrier' );
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink( 'AdminModules', false ) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite( 'AdminModules' );
        $helper->tpl_vars                 = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm( array( $fields_form ) );
    }

    public function getConfigFieldsValues()
    {
        return array(
            'SID_MERCHANT'    => Tools::getValue( 'SID_MERCHANT', Configuration::get( 'SID_MERCHANT' ) ),
            'SID_URL'         => Tools::getValue( 'SID_URL', Configuration::get( 'SID_URL' ) ),
            'SID_PRIVATE_KEY' => Tools::getValue( 'SID_PRIVATE_KEY', Configuration::get( 'SID_PRIVATE_KEY' ) ),
        );
    }

    protected function _postValidation()
    {
        if ( Tools::isSubmit( 'btnSubmit' ) ) {
            if ( !Tools::getValue( 'SID_MERCHANT' ) ) {
                $this->_postErrors[] = $this->l( 'Merchant code is required.' );
            } elseif ( !Tools::getValue( 'SID_URL' ) ) {
                $this->_postErrors[] = $this->l( 'SID url is required.' );
            } elseif ( !Tools::getValue( 'SID_PRIVATE_KEY' ) ) {
                $this->_postErrors[] = $this->l( 'Private key is required.' );
            }

        }
    }

    protected function _postProcess()
    {
        if ( Tools::isSubmit( 'btnSubmit' ) ) {
            Configuration::updateValue( 'SID_MERCHANT', Tools::getValue( 'SID_MERCHANT' ) );
            Configuration::updateValue( 'SID_URL', Tools::getValue( 'SID_URL' ) );
            Configuration::updateValue( 'SID_PRIVATE_KEY', Tools::getValue( 'SID_PRIVATE_KEY' ) );
        }
        $this->_html .= $this->displayConfirmation( $this->l( 'Settings updated' ) );
    }

    public function hookPaymentReturn( $params )
    {
        if ( !$this->active ) {
            return;
        }
        $this->context->smarty->assign(
            array(
                'shop_name' => $this->context->shop->name,
            )
        );
        return $this->fetch( 'module:' . $this->name . '/views/templates/hook/payment_return.tpl' );
    }

    public function getSIDUrl()
    {
        return Configuration::get( 'SID_URL' );
    }

    public function getL( $key )
    {
        $translations = array(
            'Cancel'         => $this->l( 'Cancel' ),
            'My cart'        => $this->l( 'My cart' ),
            'Return to shop' => $this->l( 'Return to shop' ),
        );

        return $translations[$key];
    }

    private static function getHttpHost( $http = false, $entities = false )
    {
        $host = ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'] );
        if ( $entities ) {
            $host = htmlspecialchars( $host, ENT_COMPAT, 'UTF-8' );
        }

        if ( $http ) {
            $host = ( Configuration::get( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://' ) . $host;
        }

        return $host;
    }

    public function returnerrorurl()
    {
        return Tools::redirect( $this->context->link->getModuleLink( $this->name, 'cancel' ) );
    }

    public function getSIDPendingID()
    {
        return Configuration::get( 'SID_PAYMENT_PENDING_STATUS' );
    }

    public function hookPaymentOptions( $params )
    {
        if ( !$this->active ) {
            return;
        }

        $payment_options = [
            $this->getIframePaymentOption(),
        ];

        return $payment_options;
    }

    public function getIframePaymentOption()
    {
        $iframeOption = new PaymentOption();
        $iframeOption->setCallToActionText( $this->l( 'Pay with SID Instant EFT' ) )
            ->setAction( $this->context->link->getModuleLink( $this->name, 'pay', array(), true ) )
            ->setLogo( Media::getMediaPath( _PS_MODULE_DIR_ . $this->name . '/sid_logo.jpg' ) );

        return $iframeOption;
    }
}
