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
                        'CN' => $billingAddress->getFullName(),                 // Optional
                        'EMAIL' => $purchase->getUser()->getEmail(),            // Optional
                        'OWNERZIP' => $billingAddress->getPostalCode(),         // Optional
                        'OWNERADDRESS' => $billingAddress->getStreetLine(),     // Optional
                        'OWNERCTY' => $billingAddress->getCountry()->getName(), // Optional
                        'OWNERTOWN' => $billingAddress->getCity(),              // Optional
                        'OWNERTELNO' => $billingAddress->getPhoneNumber(),      // Optional
                        'lang'      => $request->getLocale(),
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
an easy way to communicate with the Dotpay API, then you can use the plugin directly::

    $plugin = $container->get('payment.plugin.ogone');

.. _JMSPaymentCoreBundle: https://github.com/schmittjoh/JMSPaymentCoreBundle/blob/master/Resources/doc/index.rst
