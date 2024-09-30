<?php
/*
 * Copyright (c) 2024 Payfast (Pty) Ltd
 * Author: App Inlet (Pty) Ltd
 * Released under the GNU General Public License
 */

const ORDER_CONFIRM_URL = 'index.php?controller=order-confirmation&id_cart=';
const ID_MODULE = '&id_module=';
const KEY = '&key=';

class SIDValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess(): void
    {
        // Clean incoming POST data
        $sid =  new SID();
        $_POST = $sid->cleanPostData($_POST);
        $sidReference = $_POST['SID_REFERENCE'] ?? null;

        if (empty($sidReference)) {
            die('Order ID is not set or empty!');
        }

        // Restore the cart using the SID_REFERENCE
        $this->context->cart = new Cart($sidReference);
        $this->context->cookie->id_cart = $sidReference;
        $cart = $this->context->cart;

        // Redirect if the order already exists
        if ($cart->OrderExists()) {
            $this->redirectToOrderConfirmation($cart);
        }

        // Handle the transaction status
        $this->processTransactionStatus($cart, strtolower(trim($_POST['SID_STATUS'])));
    }

    /**
     * Process transaction status and take appropriate action.
     */
    protected function processTransactionStatus($cart, $resultStatus): void
    {
        switch ($resultStatus) {
            case "completed":
                $this->handleCompletedOrder($cart);
                break;
            case "created":
            case "ready":
                $this->handlePendingOrder($cart);
                break;
            default:
                $this->handleError();
                break;
        }
    }

    /**
     * Handle order when the transaction is marked as completed.
     */
    protected function handleCompletedOrder($cart): void
    {
        $sid = new SID();
        $sid->validateOrder(
            (int)$cart->id,
            Configuration::get('PS_OS_PAYMENT'),
            $cart->getOrderTotal(true, Cart::BOTH),
            $sid->displayName,
            null,
            $_POST['SID_TNXID']
        );
        $this->redirectToOrderConfirmation($cart);
    }

    /**
     * Handle pending transactions ("created" or "ready" statuses).
     */
    protected function handlePendingOrder($cart): void
    {
        $sid = new SID();
        $pending_id = $sid->getSIDPendingID();
        $sid->validateOrder(
            (int)$cart->id,
            $pending_id,
            $cart->getOrderTotal(true, Cart::BOTH),
            $sid->displayName
        );
        $this->redirectToOrderConfirmation($cart);
    }

    /**
     * Handle error cases in SID validation.
     */
    protected function handleError(): void
    {
        $sid = new SID();
        Tools::redirectLink($sid->returnerrorurl());
    }

    /**
     * Redirect to the order confirmation page.
     */
    protected function redirectToOrderConfirmation($cart): void
    {
        $customer = new Customer($cart->id_customer);
        Tools::redirect(
            ORDER_CONFIRM_URL . $cart->id . ID_MODULE . (int)$this->module->id . KEY . $customer->secure_key
        );
    }
}
