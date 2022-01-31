
<?php
   if (isset($line)){
        $AccountId = $line;
    }
?>

<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.roaming.claro.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:comprarPaquetes>
         <Request>
            <min><?=$data->AccountId?></min>
            <canal>USSD</canal>
            <grupo>0</grupo>
            <codPaquete><?=$data->codigoPaqueteSaldo?></codPaquete>
            <valorPaquete><?=$data->precio?></valorPaquete>
            <vigenciaPaquete><?=$data->vigencia?></vigenciaPaquete>
            <nombrePaquete><?=$data->nombre?></nombrePaquete>
            <moneda>Pesos</moneda>
            <convertible>0</convertible>
         </Request>
      </ws:comprarPaquetes>
   </soapenv:Body>
</soapenv:Envelope>

