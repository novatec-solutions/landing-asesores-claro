<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="http://www.amx.com/co/schema/mobile/common/aplicationIntegration/Comunes/v1.0" xmlns:v11="http://www.amx.com/co/schema/mobile/aplicationIntegration/RSCustomeCusIDSyncMain/v1.0">
   <soapenv:Header>
      <v1:headerRequest>
         <v1:channel>1</v1:channel>
         <!--Optional:-->
         <v1:transactionId><?=$data->transactionId?></v1:transactionId>
         <!--Optional:-->
         <v1:ipApplication>1</v1:ipApplication>
      </v1:headerRequest>
   </soapenv:Header>
   <soapenv:Body>
      <v11:rootRemoveRequest>
         <v11:removeRequest>
            <!--Optional:-->
            <v11:customerId><?=$data->customerId?></v11:customerId>
            <!--Optional:-->
            <v11:providerId><?=$data->providerId?></v11:providerId>
            <!--Optional:-->
               <v11:state>I</v11:state>
         </v11:removeRequest>
      </v11:rootRemoveRequest>
   </soapenv:Body>
</soapenv:Envelope>