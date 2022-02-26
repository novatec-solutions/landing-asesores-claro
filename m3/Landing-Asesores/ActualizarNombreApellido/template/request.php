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
      <v11:rootModifyRequest>
         <v11:modifyRequest>
            <v11:customerId><?=$data->customerId?></v11:customerId>
            <v11:providerId><?=$data->providerId?></v11:providerId>
            <v11:idNumber><?=$data->idNumber?></v11:idNumber>
            <v11:firstName><?=$data->firstName?></v11:firstName>
            <v11:lastName><?=$data->lastName?></v11:lastName>
         </v11:modifyRequest>
      </v11:rootModifyRequest>
   </soapenv:Body>
</soapenv:Envelope>