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
$cart    = $context->cart;

if ( !isset( $_POST["SID_REFERENCE"] ) || empty( $_POST["SID_REFERENCE"] ) ) {
    echo "Order ID is not set or emtpy!";
} else {

    if ( empty( $cart->id ) ) {
        $cart = new Cart( $_POST['SID_REFERENCE'] );
    }

    if ( $cart->OrderExists() ) {
        $SID      = new SID();
        $customer = new Customer( $cart->id_customer );
        Tools::redirect( 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $SID->id . '&key=' . $context->customer->secure_key );
    }

    // Retrieve the variables posted back from SID
    $_RESULT = $_POST['SID_STATUS'];

    /*
     * The result of the transaction. Possible returned values are:
     * COMPLETED
     * CANCELLED
     * CREATED
     * READY
     */

    $order_id = $_POST['SID_REFERENCE'];

    if ( Tools::strtolower( $_RESULT ) == "completed" ) {
        $SID = new SID();
        $SID->validateOrder( (int) $cart->id, _PS_OS_PAYMENT_, $cart->getOrderTotal( true, 3 ), $SID->displayName );
        Tools::redirect( 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $SID->id . '&key=' . $context->customer->secure_key );
    } else if ( Tools::strtolower( $_RESULT ) == "created" || Tools::strtolower( $_RESULT ) == "ready" ) {
        $SID        = new SID();
        $pending_id = $SID->getSIDPendingID();
        $url        = $SID->validateOrder( $cart->id, $pending_id, $cart->getOrderTotal( true, 3 ), $SID->displayName, null );
        Tools::redirect( 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $SID->id . '&key=' . $context->customer->secure_key );
    } else {
        $SID = new SID();
        $url = $SID->returnerrorurl();
        Tools::redirectLink( $url );
    }
}
