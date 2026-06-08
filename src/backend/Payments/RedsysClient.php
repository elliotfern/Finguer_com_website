<?php

namespace App\Payments;

class RedsysClient
{
    private string $merchantCode;
    private string $terminal;
    private string $key;
    private string $endpoint;

    public function __construct(
        string $merchantCode,
        string $terminal,
        string $key,
        bool $testMode = true
    ) {
        $this->merchantCode = $merchantCode;
        $this->terminal     = $terminal;
        $this->key          = $key;
        $this->endpoint     = $testMode
            ? 'https://sis-t.redsys.es:25443/apl02/services/SerClsWSConsulta'
            : 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
    }

    public function consultaOperacion(string $order, int $transactionType = 0): array
    {
        $order = trim($order);

        if ($order === '') {
            return ['status' => 'error', 'message' => 'Order vacío'];
        }

        $soap = $this->buildSoap($order, $transactionType);
        $response = $this->sendRequest($soap);

        if (!$response) {
            return ['status' => 'error', 'message' => 'No response from Redsys'];
        }

        return $this->parseResponse($response);
    }

    private function buildSoap(string $order, int $transactionType): string
    {
        $payload = "<Version Ds_Version=\"0.0\"><Message><Transaction>"
            . "<Ds_MerchantCode>{$this->merchantCode}</Ds_MerchantCode>"
            . "<Ds_Terminal>{$this->terminal}</Ds_Terminal>"
            . "<Ds_Order>{$order}</Ds_Order>"
            . "<Ds_TransactionType>{$transactionType}</Ds_TransactionType>"
            . "</Transaction></Message></Version>";

        $signature  = $this->generateSignature($order, $payload);
        $cadenaXML  = '<Messages>' . $payload
            . '<Signature>' . $signature . '</Signature>'
            . '<SignatureVersion>HMAC_SHA256_V1</SignatureVersion>'
            . '</Messages>';

        return '<?xml version="1.0"?>'
            . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservices.apl02.redsys.es">'
            . '<soapenv:Header/>'
            . '<soapenv:Body>'
            . '<web:consultaOperaciones>'
            . '<cadenaXML><![CDATA[' . $cadenaXML . ']]></cadenaXML>'
            . '</web:consultaOperaciones>'
            . '</soapenv:Body>'
            . '</soapenv:Envelope>';
    }

   private function generateSignature(string $order, string $payload): string
{
    $key = base64_decode($this->key);
    
    // Padding del order a múltiplos de 8 (igual que civitatis)
    $l        = ceil(strlen($order) / 8) * 8;
    $padded   = $order . str_repeat("\0", $l - strlen($order));
    $keyOrder = substr(
        openssl_encrypt($padded, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"),
        0,
        $l
    );

    return base64_encode(hash_hmac('sha256', $payload, $keyOrder, true));
}

    private function sendRequest(string $soap): ?string
    {
        try {
            $ch = curl_init($this->endpoint);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $soap,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml;charset=utf-8',
                    'Accept: text/xml',
                    'SOAPAction: consultaOperaciones',
                ],
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                return null;
            }

            return $response;

        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseResponse(string $xml): array
    {
        // Extraer el contenido del CDATA de la respuesta SOAP
        if (preg_match('/<consultaOperacionesReturn>(.*?)<\/consultaOperacionesReturn>/s', $xml, $matches)) {
            $inner = html_entity_decode($matches[1]);
        } else {
            return ['status' => 'error', 'message' => 'No consultaOperacionesReturn in response', 'raw' => $xml];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        if (!$dom->loadXML($inner)) {
            return ['status' => 'error', 'message' => 'Invalid inner XML', 'raw' => $inner];
        }

        $xpath = new \DOMXPath($dom);

        // Comprobar error
        $errorNodes = $xpath->query('//*[local-name()="Ds_ErrorCode"]');
        if ($errorNodes->length > 0) {
            return [
                'status'     => 'error',
                'error_code' => trim($errorNodes->item(0)->nodeValue),
                'raw'        => $inner,
            ];
        }

        $responseCode = null;
        $nodes = $xpath->query('//*[local-name()="Ds_Response"]');
        if ($nodes->length > 0) {
            $responseCode = trim($nodes->item(0)->nodeValue);
        }

        return [
            'status'        => 'success',
            'paid'          => ($responseCode === '0000'),
            'response_code' => $responseCode,
            'raw'           => $inner,
        ];
    }
}