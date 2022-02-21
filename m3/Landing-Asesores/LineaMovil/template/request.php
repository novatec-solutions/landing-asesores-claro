
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="http://www.amx.com/co/schema/mobile/common/aplicationIntegration/Comunes/v1.0" xmlns:v11="http://www.amx.com/co/schema/mobile/aplicationIntegration/RSCustomeCusIDSyncMain/v1.0">
   <soapenv:Header>
      <v1:headerRequest>
         <v1:channel>?</v1:channel>
         <!--Optional:-->
         <v1:transactionId>?</v1:transactionId>
         <!--Optional:-->
         <v1:ipApplication>?</v1:ipApplication>
      </v1:headerRequest>
   </soapenv:Header>
   <soapenv:Body>
      <v11:rootReadRequest>
         <v11:readRequest>
            <v11:fixedAccount> <?= $data->fixedAccount ?> </v11:fixedAccount>
            <v11:state> <?= $data->state ?> </v11:state>
         </v11:readRequest>
      </v11:rootReadRequest>
   </soapenv:Body>
</soapenv:Envelope>
