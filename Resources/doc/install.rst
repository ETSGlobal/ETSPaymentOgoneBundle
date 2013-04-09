============
Installation
============
Dependencies
------------
This plugin depends on the JMSPaymentCoreBundle_, so you'll need to add this to your kernel
as well even if you don't want to use its persistence capabilities.

Configuration
-------------
::

    // YAML
    ets_payment_ogone:
        pspid:     Your seller id
        shain:     Your SHA-1 IN passphrase
        shaout:    Your SHA-1 IN passphrase
        debug:     Set it to true for TEST environment

        api:
            user: Your api user
            password: Your api password

        design:
            title:
            bgColor:
            txtColor:
            tblBgColor:
            tblTxtColor:
            buttonBgColor:
            buttonTxtColor:
            fontType:
            logo:

        redirection:
            accept_url:
            decline_url:
            exception_url:
            cancel_url:
            back_url:
