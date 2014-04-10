=====
Usage
=====
With the Payment Plugin Controller (Recommended)
------------------------------------------------
http://jmsyst.com/bundles/JMSPaymentCoreBundle/master/usage

You can configure some custom fields :

.. code-block :: php

    <?php

    class PaymentController
    {
        ...

        $form = $this->getFormFactory()->create('jms_choose_payment_method', null, array(
                'amount'   => $order->getAmount(),
                'currency' => 'EUR',
                'default_method' => 'ogone_gateway', // Optional
                'predefined_data' => array(
                    'ogone_gateway' => array(
                        'tp' => 'http://www.myshop.com/template.html'           // Optional
                        'PM' => $pm                                             // Optional - Example value: "CreditCard" - Note: You can consult the list of PM values on Ogone documentation
                        'BRAND' => $brand                                       // Optional - Example value: "VISA" - Note: If you send the BRAND field without sending a value in the PM field (‘CreditCard’ or ‘Purchasing Card’), the BRAND value will not be taken into account.
                        'CN' => $billingAddress->getFullName(),                 // Optional
                        'EMAIL' => $purchase->getUser()->getEmail(),            // Optional
                        'OWNERZIP' => $billingAddress->getPostalCode(),         // Optional
                        'OWNERADDRESS' => $billingAddress->getStreetLine(),     // Optional
                        'OWNERCTY' => $billingAddress->getCountry()->getName(), // Optional
                        'OWNERTOWN' => $billingAddress->getCity(),              // Optional
                        'OWNERTELNO' => $billingAddress->getPhoneNumber(),      // Optional
                        'lang'      => $request->getLocale(),                   // 5 characters maximum, for e.g: fr_FR
                        'ORDERID'   => '123456',                                // Optional, 30 characters maximum
                    ),
                ),
            ));

        ...
    }

Without the Payment Plugin Controller
-------------------------------------
The Payment Plugin Controller is made available by the CoreBundle and basically is the
interface to a persistence backend like the Doctrine ORM. It also performs additional
integrity checks to validate transactions. If you don't need these checks, and only want
an easy way to communicate with the Ogone API, then you can use the plugin directly::

    $plugin = $container->get('payment.plugin.ogone');

.. _JMSPaymentCoreBundle: https://github.com/schmittjoh/JMSPaymentCoreBundle/blob/master/Resources/doc/index.rst

Use Ogone's transaction feedback via callback request
-----------------------------------------------------
When a payment is captured, Ogone can send the parameters listed in ETS\\Payment\\OgoneBundle\\Response\\FeedbackResponse::$fields
in a request on your ACCEPTURL, EXCEPTIONURL, CANCELURL or DECLINEURL to enable you to perform a database update.
You can activate this option in the Technical information page > "Transaction feedback" tab > "HTTP redirection in the browser" section:
"I would like to receive transaction feedback parameters on the redirection URLs".

You would then have to define an action behind the url you would choose to give Ogone, which could look like this:

.. code-block :: php

    ...

    public function callbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $orderId = $request->get('orderID');

        if (null === $order = $em->getRepository('MyBundle:Order')->find($orderId)) {
            throw new NotFoundHttpException(sprintf('unable to find order with id [%s]', $orderId));
        }

        if (null === $instruction = $order->getPaymentInstruction()) {
            $this->get('logger')->info(sprintf('[Ogone - callback] No payment instruction found for OrderId [%s].', $orderId));

            return new Response('No payment instruction');
        }

        try {
            $this->get('payment.ogone')->handleTransactionFeedback($instruction);
        } catch (NoPendingTransactionException $e) {
            $this->get('logger')->info($e->getMessage());

            return new Response('Nothing pending');
        }

        $em->flush();

        $this->get('logger')->info(sprintf('[Ogone - callback] Payment instruction %s successfully updated', $instruction->getId()));

        return new Response('OK');
    }
