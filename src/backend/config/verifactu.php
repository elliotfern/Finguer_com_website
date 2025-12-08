<?php

use eseperio\verifactu\Verifactu;

Verifactu::config(
    $_ENV['VERIFACTU_CERT_PATH'],     // ruta .pfx o .p12
    $_ENV['VERIFACTU_CERT_PASSWORD'], // password del certificado
    Verifactu::TYPE_CERTIFICATE,      // o TYPE_SEAL si usas sello
    Verifactu::ENVIRONMENT_SANDBOX    // de momento sandbox
);
