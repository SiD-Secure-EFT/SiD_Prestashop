<?php
/*
 * Copyright (c) 2024 Payfast (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */

/**
 * @since 1.1.0
 */
class SIDPayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $sidUrl         = 'https://www.sidpayment.com/paySID/';
        $sidMerchant    = Configuration::get('SID_MERCHANT');
        $sidPrivateKey  = Configuration::get('SID_PRIVATE_KEY');
        $sidCurrency    = 'ZAR';
        $sidCountry     = 'ZA';
        $sidReference   = $this->context->cart->id;
        $sidAmount      = $this->context->cart->getOrderTotal(true, 3);

        // Hashes the variables
        $sidConsistent = Tools::strtoupper(
            hash(
                'sha512',
                $sidMerchant . $sidCurrency . $sidCountry . $sidReference . $sidAmount . $sidPrivateKey
            )
        );

        $this->context->smarty->assign(
            array(
                'SID_MERCHANT'    => $sidMerchant,
                'SID_CURRENCY'    => $sidCurrency,
                'SID_COUNTRY'     => $sidCountry,
                'SID_REFERENCE'   => $sidReference,
                'SID_AMOUNT'      => $sidAmount,
                'SID_PRIVATE_KEY' => $sidPrivateKey,
                'SID_CONSISTENT'  => $sidConsistent,
                'SID_URL'         => $sidUrl,
            )
        );

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/redirect.tpl');
    }
}
