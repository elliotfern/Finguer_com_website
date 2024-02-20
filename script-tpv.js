<script>
    $(document).ready(function(){
        // Evento click del botón de pagar
        $('#pay-button').click(function(event){
            event.preventDefault(); // Evitar que se envíe el formulario

            // Datos de la transacción
            var merchantCode = '999008881';
            var merchantOrder = '11';
            var terminal = '001';
            var amount = '4444'; // en céntimos de euro
            var currency = '978'; // Código ISO 4217 para euros (EUR)
            var transactionType = '0'; // Tipo de transacción, por ejemplo '0' para autorización y captura
            var merchantURL = 'https://finguer.com';
            var key = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7'; // Clave secreta proporcionada por Redsys

            // Construir formulario con los datos de la transacción
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'https://sis-t.redsys.es:25443/sis/realizarPago'; // URL del TPV de Redsys

            // Agregar campos ocultos al formulario
            var fields = {
                'Ds_Merchant_MerchantCode': merchantCode,
                'Ds_Merchant_Terminal': terminal, // Terminal del comercio
                'Ds_Merchant_Order': merchantOrder,
                'Ds_Merchant_Amount': amount,
                'Ds_Merchant_Currency': currency,
                'Ds_Merchant_TransactionType': transactionType,
                'Ds_Merchant_MerchantURL': merchantURL,
                'Ds_Merchant_MerchantSignature': '', // Este campo se completará más adelante
            };

            // Calcular la firma HMAC SHA-256
            var signature = CryptoJS.HmacSHA256(
                fields['Ds_Merchant_Amount'] + fields['Ds_Merchant_Order'] + fields['Ds_Merchant_MerchantCode'] + fields['Ds_Merchant_Currency'] + fields['Ds_Merchant_TransactionType'] + fields['Ds_Merchant_MerchantURL'],
                key
            );
            fields['Ds_Merchant_MerchantSignature'] = signature.toString(CryptoJS.enc.Base64);

            // Agregar campos al formulario
            for (var key in fields) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            // Agregar formulario al cuerpo del documento y enviarlo automáticamente
            document.body.appendChild(form);
            
            // Depuración: Imprimir el formulario en la consola
            console.log("Formulario antes de enviar:");
            console.log(form);

            form.submit();
        });
    });
</script>