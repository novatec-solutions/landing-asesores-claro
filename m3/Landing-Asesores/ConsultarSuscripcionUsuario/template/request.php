<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:user="http://xmlns.oracle.com/schema/RSCustomerDataOTT/userProfileOtt" xmlns:v1="http://www.amx.com/co/schema/mobile/common/aplicationIntegration/Comunes/v1.0" xmlns:cus="http://TargetNamespace.com/customerDataOtt_queryUserOtt_request">
   <soapenv:Header>
      <user:NotifySOAPHeader>
         <!--Optional:-->
         <user:TransId>?</user:TransId>
         <user:Username><?=$data->Username?></user:Username>
         <user:Password><?=$data->Password?></user:Password>
      </user:NotifySOAPHeader>
      <v1:headerRequest>
         <v1:channel>1</v1:channel>
         <!--Optional:-->
         <v1:transactionId>?</v1:transactionId>
         <!--Optional:-->
         <v1:ipApplication>?</v1:ipApplication>
      </v1:headerRequest>
   </soapenv:Header>
   <soapenv:Body>
      <cus:Root-Element>
         <cus:queryUserOttRequest>
            <cus:invokeMethod><?=$data->invokeMethod?></cus:invokeMethod>
            <cus:correlatorId><?=$data->correlatorId?></cus:correlatorId>
            <cus:countryId><?=$data->countryId?></cus:countryId>
            <cus:startDate><?=$data->startDate?></cus:startDate>
            <cus:endDate><?=$data->endDate?></cus:endDate>
            <cus:employeeId><?=$data->employeeId?></cus:employeeId>
            <cus:origin><?=$data->origin?></cus:origin>
            <!--Optional:-->
            <cus:serviceName><?=$data->serviceName?></cus:serviceName>
            <cus:providerId><?=$data->providerId?></cus:providerId>
            <cus:iccidManager><?=$data->iccidManager?></cus:iccidManager>
            <!--Zero or more repetitions:-->
            <cus:extensionInfo>
               <cus:key><?=$data->key?></cus:key>
               <cus:value><?=$data->value?></cus:value>
            </cus:extensionInfo>
         </cus:queryUserOttRequest>
      </cus:Root-Element>
   </soapenv:Body>
</soapenv:Envelope>
