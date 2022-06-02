<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:user="http://xmlns.oracle.com/schema/RSCustomerDataOTT/userProfileOtt" xmlns:v1="http://www.amx.com/co/schema/mobile/common/aplicationIntegration/Comunes/v1.0" xmlns:cus="http://TargetNamespace.com/customerDataOtt_queryOtt_request">
   <soapenv:Header>
      <user:NotifySOAPHeader>
         <!--Optional:-->
         <user:TransId/>
         <user:Username>PA00003102</user:Username>
         <user:Password>aMc0Co3!</user:Password>
      </user:NotifySOAPHeader>
      <v1:headerRequest>
         <v1:channel/>
         <!--Optional:-->
         <v1:transactionId/>
         <!--Optional:-->
         <v1:ipApplication/>
      </v1:headerRequest>
   </soapenv:Header>
   <soapenv:Body>
      <cus:Root-Element>
         <cus:queryOttRequest>
            <cus:invokeMethod>consultarrentascliente</cus:invokeMethod>
            <cus:correlatorId>00000232550e8400e29b41d4a7164466551234</cus:correlatorId>
            <cus:countryId>CO</cus:countryId>
            <!--Optional:-->
            <cus:startDate>2021-07-10T16:18:05Z</cus:startDate>
            <!--Optional:-->
            <cus:endDate>2022-07-15T16:18:05Z</cus:endDate>
            <cus:employeeId>352fegsf</cus:employeeId>
            <cus:origin>MI_CLARO</cus:origin>
            <!--Optional:-->
            <cus:serviceName>consultarrentascliente</cus:serviceName>
            <cus:providerId>PA00003102</cus:providerId>
            <cus:iccidManager>AMCOCO</cus:iccidManager>
            <!--Zero or more repetitions:-->
            <cus:extensionInfo>
               <cus:key>CUSTOMERID</cus:key>
               <cus:value>11841025</cus:value>
            </cus:extensionInfo>
          </cus:queryOttRequest>
      </cus:Root-Element>
   </soapenv:Body>
</soapenv:Envelope>