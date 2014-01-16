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
        shaout:    Your SHA-1 OUT passphrase
        debug:     Set it to true for TEST environment
        utf8:      Set it to true to use UTF8 characters in the Ogone requests

        api:
            user: Your api user
            password: Your api password

        design:
            tp:
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

Additional informations
-------------
**API:**
The User API is not your main user account.
You have to create a new user account into Ogone Administration -> Users -> Create new user.
Into the creation form you have to select Admin profile and pick the box User "API".

**Design:**
All design data must be written with quotation marks

**Logo:**
According to the Ogone Technical Support, the logo must be located behind a https URL.
If you don't have https, you can store your logo into the Ogone Administration. Image hosting is an option so you need to activate it into Configuration -> Account -> Your options (You will find many hosting options depending on your image size). I recommend to contact sales support to know the cost of theses options (They are all available at 0€ only into test environment).

**UTF8:**
By default, Ogone uses ISO character encoding. However, it supports the usage of UTF-8, if the merchant calls the appropriate pages.
Also in ogone itself, Technical information/Global security parameters, you have to change the Character encoding to "UTF-8".
