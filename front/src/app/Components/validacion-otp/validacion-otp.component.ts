import { ActualizationData } from './../../clases/actualizationData';
import { ValidatePinLegalizationRequest } from './../../clases/validatepinlegalizationrequest';
import { ValidatePinResponse } from './../../Interfaces/validatepinresponse.interface';
import { ValidationService } from './../../Services/validation.service';
import { SeguridadService } from './../../Services/seguridad.service';
import { ResourcesService } from './../../Services/resources.service';
import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import { ResponseApi } from '../../interfaces/response-api.interface';

@Component({
  selector: 'app-validacion-otp',
  templateUrl: './validacion-otp.component.html',
  styleUrls: ['./validacion-otp.component.css']
})
export class ValidacionOtpComponent implements OnInit {
  pin:number=0;
  lineNumber:number = 0;
  public datos:any;
  validatePinLegalizationRequest:ValidatePinLegalizationRequest;
  actualizationData:ActualizationData;

  constructor(
    private router: Router,
    private resourcesService: ResourcesService,
    private seguridadService: SeguridadService,
    private validationService: ValidationService)
    {
    this.validatePinLegalizationRequest
     = this.resourcesService.getValidatePinLegalizationRequest();
    const line:string = this.validatePinLegalizationRequest.getPrepaidMin().toString();
    let lineFormat: string = line.substring(6,10);
    this.lineNumber = Number(lineFormat);
    this.datos = {
      input1:null,
      input2:null,
      input3:null,
      input4:null,
    }
    this.actualizationData = new ActualizationData("",this.validatePinLegalizationRequest.getLastName(),
    this.validatePinLegalizationRequest.getDocNum(),
    Number(this.validatePinLegalizationRequest.getDocType()));
    this.resourcesService.setActualizationData(this.actualizationData);
  }


  ngOnInit(): void {}

  enviarBtn(){
    this.activateSpiner();
    this.pin=this.datos.input1.toString()+this.datos.input2.toString()+this.datos.input3.toString()
    +this.datos.input4.toString();
    /*Obtiene los datos del usuario desde el servicio resourcesService*/
    const validatePinLegalizationRequest:ValidatePinLegalizationRequest
     = this.resourcesService.getValidatePinLegalizationRequest();
     validatePinLegalizationRequest.setPin(this.pin);
    const body = this.requestValidationData();
    const token = this.seguridadService.getEncrypt(body);


    this.validationService.validatePinLegalization(body, token)
      .subscribe((validatePinResponse: ValidatePinResponse) => {
        const error = validatePinResponse.error;
          if(error == 0){
            const actualizationData = new ActualizationData(
              validatePinResponse.data.names,
              validatePinResponse.data.surname,
              validatePinResponse.data.docNum,
              validatePinResponse.data.docuemntType
              );
              this.resourcesService.setActualizationData(actualizationData);
              if(validatePinResponse.isPinValid){
                this.deactivateSpiner();
                this.success(this.resourcesService.getFail9());
                this.router.navigate([`../actualizacion-datos`]);
              }else{
                this.deactivateSpiner();
                const response = validatePinResponse.response;
                this.fail('<strong><basefont size=12> '.concat(response).concat('</basefont></strong>'));
                this.router.navigate([`../validacion-otp`]);
              }
          }
          else{
            this.datos.input1 = "";
            this.datos.input2 = "";
            this.datos.input3 = "";
            this.datos.input4 = "";
            this.deactivateSpiner();
            this.fail(validatePinResponse.response);
          }
        }, (error) => {
          this.datos.input1 = "";
          this.datos.input2 = "";
          this.datos.input3 = "";
          this.datos.input4 = "";
        this.deactivateSpiner();
        this.fail(this.resourcesService.getFail11());
      });
      }


  getOtp(){
    this.activateSpiner();
    const validatePinLegalizationRequest = this.resourcesService.getValidatePinLegalizationRequest();
    const body = this.requestGeneratePin(validatePinLegalizationRequest);
    const token = this.seguridadService.getEncrypt(body);
    this.validationService.generatePin(body, token)
      .subscribe((responseApi: ResponseApi) => {
        this.deactivateSpiner();
        const error = responseApi.error;
        if(error != 0){
          this.fail(responseApi.response);
        }
      }, (error) => {
        this.deactivateSpiner();
        this.fail(this.resourcesService.getFail11());
      });
  }


  //Crea el request para la consulta al servicio/
  requestGeneratePin(validatePinLegalizationRequest: ValidatePinLegalizationRequest):any{
    const body = {
      data: {
        prepaidMin:validatePinLegalizationRequest.getPrepaidMin(),
        docNum:validatePinLegalizationRequest.getDocNum(),
        channel:this.resourcesService.getChannel()
        }
      };
      return body;
  }


    requestValidationData():any{
      const body = {
          data: {
                  pin : this.validatePinLegalizationRequest.getPin(),
                  prepaidMin : this.validatePinLegalizationRequest.getPrepaidMin(),
                  docType : this.validatePinLegalizationRequest.getDocType(),
                  docNum : this.validatePinLegalizationRequest.getDocNum(),
                  expeditionDate : this.validatePinLegalizationRequest.getExpeditionDate(),
                  lastName : this.validatePinLegalizationRequest.getLastName(),
                  channel : this.resourcesService.getChannel()
                }
              };
        return body;
    }




    success(message: string): void{
      Swal.fire({
        imageUrl: './assets/images/success.svg',
        imageWidth: 70,
        imageHeight: 70,
        html: '<p class="alertParagraph">'.concat(message).concat('</p>'),

        confirmButtonText: 'Cerrar',
        confirmButtonColor:'#EF3829',
        });
    }
  /*Muestra un mensaje modal de error*/


  fail(message: string): void{

    Swal.fire({
      imageUrl: './assets/images/fail.svg',
      imageWidth: 100,
      imageHeight: 100,
      html: '<p class="alertParagraph">'.concat(message).concat('</p>'),

      confirmButtonColor: '#EF3829',
      }).then((result) => {
      });
  }

    //activa spiner de espera/
    activateSpiner(): void{
      const divSpinerLoad = document.getElementById('div-spiner-load');
      this.addStyle(divSpinerLoad, 'spinner-border');
      this.addStyle(divSpinerLoad, 'text-danger');
    }


     //activa spiner de espera/
     deactivateSpiner(): void{
        const divSpinerLoad = document.getElementById('div-spiner-load');
        this.remove(divSpinerLoad, 'spinner-border');
        this.remove(divSpinerLoad, 'text-danger');
      }



      //Obtiener el milisegundo actual/
      getTimeMillis(): number{
        let ret = 0;
        try{
          let nd = new Date();
          ret = nd.getMilliseconds();
        }catch(error){
          ret = Math.random();
        }
        return ret;
      }

      //Agrega un estilo css a un elemento/
      addStyle(element: any, style: string): void{
        element.classList.add(style);
      }

      //Agrega un estilo css a un elemento/
      remove(element: any, style: string): void{
        element.classList.remove(style);
      }
}




