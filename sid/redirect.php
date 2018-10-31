<?php
/*
 * Copyright (c) 2018 PayGate (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
 */

include dirname( __FILE__ ) . '/../../config/config.inc.php';
include dirname( __FILE__ ) . '/../../init.php';
include dirname( __FILE__ ) . '/sid.php';

$context = Context::getContext();
$SID  = new SID();
$cart = new Cart($context->cart->id);

$address = new Address($cart->id_address_invoice);
$country = new Country($address->id_country);
$state   = null;
if ($address->id_state) {
    $state = new State($address->id_state);
}

$customer = new Customer($cart->id_customer);

$SID_MERCHANT    = Configuration::get('SID_MERCHANT'); // Gets the merchant ID which is set in the sid.php page
$SID_PRIVATE_KEY = Configuration::get('SID_PRIVATE_KEY'); // Gets the private key which is set it the sid.php page
$currency_order  = new Currency($cart->id_currency);
$currency_module = $SID->getCurrency();

if (!Validate::isLoadedObject($address) or !Validate::isLoadedObject($customer) or !Validate::isLoadedObject($currency_module)) {
    die( $SID->getL('SID error: (invalid address or customer)') );
}

// Check currency of payment
if ($currency_order->id != $currency_module->id) {
    $cookie->id_currency = $currency_module->id;
    $cart->id_currency   = $currency_module->id;
    $cart->update();
}

// Merchant details
$productsInCart  = $cart->getProducts(); // Gets products fromt the cart
$SID_MERCHANT    = $SID_MERCHANT; // Gets the merchant ID
$SID_CURRENCY    = 'ZAR'; // The currency is set in Rand as SID is only for Soutth Africa
$SID_COUNTRY     = 'ZA'; // The country is set to Soutth Africa
$SID_REFERENCE   = $cart->id; // Gets refrence form the cart
$SID_AMOUNT      = $cart->getOrderTotal(true, 3);
$SID_PRIVATE_KEY = $SID_PRIVATE_KEY; // Private key
// Hashes the variables
$SID_CONSISTENT = Tools::strtoupper(hash('sha512', $SID_MERCHANT.$SID_CURRENCY.$SID_COUNTRY.$SID_REFERENCE.$SID_AMOUNT.$SID_PRIVATE_KEY));
// This passes the variables through
$smarty->assign( array(
    'redirect_text'       => strval('Please wait, redirecting to SID... Thanks.'),
    'cancel_text'         => $SID->getL('Cancel'),
    'cart_text'           => $SID->getL('My cart'),
    'return_text'         => $SID->getL('Return to shop'),
    'SID_URL'             => $SID->getSIDUrl(),
    'SID_MERCHANT'        => $SID_MERCHANT,
    'SID_CURRENCY'        => $SID_CURRENCY,
    'SID_COUNTRY'         => $SID_COUNTRY,
    'SID_REFERENCE'       => $SID_REFERENCE,
    'SID_AMOUNT'          => $SID_AMOUNT,
    'SID_PRIVATE_KEY'     => $SID_PRIVATE_KEY,
    'SID_CONSISTENT'      => $SID_CONSISTENT,
    'products'            => $cart->getProducts(),
    'total_cart_products' => $total_cart_products,
    'url'                 => Tools::getHttpHost( true, true ) . __PS_BASE_URI__,
) );

if (is_file( _PS_THEME_DIR_ . 'modules/'.$SID->name.'/redirect.tpl' ) ) // Passes the information through to the redirect.tpl page
{
    $smarty->display( _PS_THEME_DIR_ .'modules/' . $SID->name . '/redirect.tpl' );
} else {
    $smarty->display( _PS_MODULE_DIR_ . $SID->name . '/views/templates/front/redirect.tpl' );
}