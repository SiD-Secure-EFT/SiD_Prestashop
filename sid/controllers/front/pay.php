<?php
/*
 * Copyright (c) 2018 PayGate (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */

/**
 * @since 1.0.0
 */
class SIDPayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $this->registerStylesheet( 'sid-popup', 'modules/' . $this->module->name . '/css/sidPopupStyle.min.css', array( 'position' => 'bottom', 'priority' => 1000 ) );
        $this->registerJavascript( 'sid-popup', 'modules/' . $this->module->name . '/js/sidPopup.min.js', array( 'position' => 'bottom', 'priority' => 1000 ) );
        $this->registerJavascript( 'sid-payment', 'modules/' . $this->module->name . '/js/sid-iframe-payment.js', array( 'position' => 'bottom', 'priority' => 1100 ) );

        $SID_MERCHANT    = Configuration::get( 'SID_MERCHANT' );
        $SID_PRIVATE_KEY = Configuration::get( 'SID_PRIVATE_KEY' );
        $SID_CURRENCY    = 'ZAR';
        $SID_COUNTRY     = 'ZA';
        $SID_REFERENCE   = $this->context->cart->id;
        $SID_AMOUNT      = $this->context->cart->getOrderTotal( true, 3 );
        // Hashes the variables
        $SID_CONSISTENT = Tools::strtoupper( hash( 'sha512', $SID_MERCHANT . $SID_CURRENCY . $SID_COUNTRY . $SID_REFERENCE . $SID_AMOUNT . $SID_PRIVATE_KEY ) );

        $this->context->smarty->assign(
            array(
                'SID_MERCHANT'    => $SID_MERCHANT,
                'SID_CURRENCY'    => $SID_CURRENCY,
                'SID_COUNTRY'     => $SID_COUNTRY,
                'SID_REFERENCE'   => $SID_REFERENCE,
                'SID_AMOUNT'      => $SID_AMOUNT,
                'SID_PRIVATE_KEY' => $SID_PRIVATE_KEY,
                'SID_CONSISTENT'  => $SID_CONSISTENT,
            )
        );

        $this->setTemplate( 'module:' . $this->module->name . '/views/templates/front/payment.tpl' );
    }
}
