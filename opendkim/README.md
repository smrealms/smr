OpenDKIM
========
Place your DKIM private key here with the name `<selector>.private`,
where `<selector>` is the DKIM selector.

If you do not place a DKIM key here, one will be generated when you
first start the `smtp` service. It will then be up to you to make
sure that this DKIM key is accepted by the `smrealms.de` domain.
