import { Various } from './../../interfaces/basicdata.interface';
import { RequestInterface } from './../../interfaces/requestinterface.interface';
import { ValidationService } from './../../Services/validation.service';
import { ValidatePinLegalizationRequest } from './../../clases/validatepinlegalizationrequest';
import { ResourcesService } from './../../Services/resources.service';
import { Documenttype } from './../../clases/documenttype';
import { ResponseApi } from './../../Interfaces/response-api.interface';
import { LegalizacionServiceService } from './../../Services/legalizacion-service.service';
import { SeguridadService } from './../../Services/seguridad.service';
import { Component, OnInit } from '@angular/core';
import { Basicdata } from '../../Interfaces/basicdata.interface';
import Swal from 'sweetalert2';
import { Router} from '@angular/router';
import * as jQuery from 'jquery';

@Component({
  selector: 'app-actualizacion',
  templateUrl: './actualizacion.component.html',
  styleUrls: ['./actualizacion.component.css']
})

export class ActualizacionComponent implements OnInit {
  public datos:any;
  documenttypes: Documenttype[];
  submitted:boolean;
  actualizacionForm:any;
  channel: string ="";
  constructor(
    private seguridadService: SeguridadService,
    private legalizacionService: LegalizacionServiceService,
    private validationService: ValidationService,
    private resourcesService: ResourcesService,
    private router: Router
    ) {
      this.loadInitialVarious();
    this.datos = {
      tipoDocum:'',
      numeLinea:null,
      numDocum:null,
      fechaEx:'',
      apellido:'',
      termino:false,
    };
    this.submitted=false;
    this.documenttypes = new Array();
    this.loadVarious();
    this.actualizacionForm=this.getElementById("actualizacionForm");
    this.resourcesService.setChannel(this.channel);
  }

  /*Validación y carga de parametros iniciales*/
  loadParams(data: string,channel: string){
      try{
        var now = new Date();
          var request:RequestInterface =JSON.parse(this.seguridadService.deEncrypt(data));
          var splitDate= request.expDate.split(" ");
          var date=splitDate[0].split("-");
          var time=splitDate[1].split(":");
          var yyyy =Number(date[0]);
          var mm=Number(date[1])-1;
          var dd=Number(date[2]);
          var hh=Number(time[0]);
          var min=Number(time[1]);
          var ss=Number(time[2]);
          var requestDate = new Date(yyyy,mm,dd,hh,min,ss);
          var diferencia = (Number(now) - Number(requestDate))/1000;
          var t = 0;
          try{
            t = Number(this.resourcesService.getTimeout());
          }catch(error){
            t = 20;
          }
        if(diferencia<t){
            if(Number.isInteger(Number(request.lineNumber)) && request.lineNumber.length == 10){
              this.datos.numeLinea = request.lineNumber;
              (<HTMLInputElement>document.getElementById('lineNumber')).disabled = true;
            }
        }else{
          this.fail(this.resourcesService.getFail1());
        }
      }catch(error){
        //console.log(error);
      }
    try{
      this.channel = channel;
      if(this.channel == undefined || this.channel == null){
        this.fail2(this.resourcesService.getFail2());
      }
      this.resourcesService.setChannel(this.channel);
    }catch(error){
      console.log(error);
    }
  }

  ngOnInit(): void {

  }

  clickSelect(){
    let tipoDocum =document.getElementById('selectDocument') as HTMLInputElement;
    if (this.datos.tipoDocum){
      tipoDocum?.classList.remove('select');
      tipoDocum?.classList.add('selected');
    }
  }

  onSubmit(){

  }

  error(){
    this.submitted=true;
    this.router.navigate([`../`]);
  }

  enviarBtn(){

    this.activateSpiner();
    const regex = /\//gi;
    this.datos.fechaEx = this.datos.fechaEx.replace(regex, '-');
    var splitDate= this.datos.fechaEx.split("-");
    //this.datos.fechaEx = splitDate[2].concat(splitDate[1]).concat(splitDate[0]);
    const selectDocument = this.getElementById('selectDocument');
    const documentTypeId = selectDocument.value;
    const documentDescription = selectDocument.options[selectDocument.selectedIndex].text;
    /*Guarda los datos del usuario el el servicio resourcesService*/
    const externalId = this.resourcesService.externalId(documentTypeId);
    const validatePinLegalizationRequest = new ValidatePinLegalizationRequest(
      0,
      this.datos.numeLinea,
      externalId.toString(),
      this.datos.numDocum,
      splitDate[2].concat(splitDate[1]).concat(splitDate[0]),
      this.datos.apellido.trim(),
      this.channel);
    this.resourcesService.setValidatePinLegalizationRequest(validatePinLegalizationRequest);
    const body = this.requestGeneratePin(validatePinLegalizationRequest);
    const token = this.seguridadService.getEncrypt(body);
    this.validationService.generatePin(body, token)
      .subscribe((responseApi: ResponseApi) => {
        const error = responseApi.error;
        if(error == 0){
          this.deactivateSpiner();
          this.router.navigate([`../validacion-otp`]);
        }else{
          this.deactivateSpiner();
          this.fail(responseApi.response);
        }
      }, (error) => {
        this.deactivateSpiner();
        console.log(error);
        this.fail(this.resourcesService.getFail11());
      });

  }

  /*Obtiene la lista de items varios (saludos, tipos de documento, tipos de telefono) desde el servicio*/
  loadVarious(): void {
    try{
      const body = { data: { token: this.seguridadService.getToken() } };
      const token = this.seguridadService.getEncrypt(body);
      this.legalizacionService.getVarious(body, token)
        .subscribe((basicData: Basicdata) => {
          const error = basicData.error;
          if(error == 0){
            this.resourcesService.loadResources(basicData);
            setTimeout(() =>{
              this.documenttypes = this.resourcesService.documenttypes;
            },100)
          }else{
            this.fail(this.resourcesService.getFail3());
          }
        }, (error) => {
          this.fail(this.resourcesService.getFail3());
        });
    }
    catch(error){
      console.log(error);
    }
  }


  /*Obtiene la lista de items varios (saludos, tipos de documento, tipos de telefono) desde el servicio*/
  loadInitialVarious(): void {
    try{
      const body = { data: { token: this.seguridadService.getToken() } };
      const token = this.seguridadService.getEncrypt(body);
      this.legalizacionService.getInitialVarious(body, token)
        .subscribe((basicData: Basicdata) => {
          const error = basicData.error;
          if(error == 0){
            this.resourcesService.loadInitialResources(basicData);
            var loaded = this.resourcesService.getLoaded();
              while (!loaded) {
                loaded = this.resourcesService.getLoaded();
              }
              var urlTree = this.router.parseUrl(this.router.url);
              var lineNumber:string = urlTree.queryParams['data'];
              var channel:string = urlTree.queryParams['channel'];
              window.history.pushState({}, document.title,"/legalizalineapre");
              this.loadParams(lineNumber,channel);
          }else{
            this.fail('Se presentó un error al tratar  de obtener la lista de datos varios. Por favor intente mas tarde.');
          }
        }, (error) => {
          this.fail('Se presentó un error al tratar  de obtener la lista de datos varios. Por favor intente mas tarde.');
        });
    }
    catch(error){
      console.log(error);
    }
  }


  /*Muestra los terminos y condiciones de la pagina*/
  obtenerTyC(): void{
    this.getTyC();
  }

    /*cierra modal*/
    closeModal(): void{
      ($('#dialogo1') as any).modal('hide');
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

 /*Muestra un mensaje modal de error y redirige el navegador*/
 fail2(message: string): void{
  Swal.fire({
    imageUrl: './assets/images/fail.svg',
    imageWidth: 100,
    imageHeight: 100,
    html: '<p class="alertParagraph">'.concat(message).concat('</p>'),
    confirmButtonColor: '#EF3829',
    }).then((result) => {
      location.href=this.resourcesService.getRedireccion();
    });
  }

    /*Obtiene un elemento por su id*/
    getElementById(id: string): any{
      return document.getElementById(id);
    }

 /*Crea el request para la consulta al servicio*/
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

   /*oBTIENE TERMINOS Y CONDICIONES DESDE EL SERVICIO*/
   getTyC():void{
    const body:any ={ data: {type : "TYC"} };
    const token = this.seguridadService.getEncrypt(body);
    this.legalizacionService.getVariousByType(body, token)
    .subscribe((basicdata: Basicdata) => {
      var vari:Various = basicdata.response[0];
      jQuery.noConflict();
      const mtac = document.getElementById('modal-t-a-c');
      if(mtac){
        mtac.innerHTML = vari.description;
        jQuery.noConflict();
        ($('#dialogo1') as any).modal('show');
      }
      }, (error) => {
        this.fail(this.resourcesService.getFail4());
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
