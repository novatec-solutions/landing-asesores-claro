<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://servicios.paq.claro.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <ser:listarOfertaComercial>
         <!--Optional:-->
         <request>
            <tipoConsulta>M</tipoConsulta>
            <datoConsulta><?=$AccountId?></datoConsulta>
            <spcode></spcode>
            <canal>MICLARO</canal>
            <sessionId>13213</sessionId>
         </request>
      </ser:listarOfertaComercial>
   </soapenv:Body>
</soapenv:Envelope>