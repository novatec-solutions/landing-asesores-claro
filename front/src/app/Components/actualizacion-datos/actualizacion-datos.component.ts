import { Basicdata, Various } from './../../interfaces/basicdata.interface';
import { ActualizationData } from './../../clases/actualizationData';
import { Alphabet } from './../../clases/alphabet';
import { ValidatePinLegalizationRequest } from './../../clases/validatepinlegalizationrequest';
import { Address } from './../../clases/address';
import { Phonetype } from './../../clases/phonetype';
import { Documenttype } from './../../clases/documenttype';
import { Greeting } from './../../clases/greeting';
import { ResponseApi } from './../../Interfaces/response-api.interface';
import { ValidationService } from './../../Services/validation.service';
import { City } from './../../Interfaces/city.interface';
import { Departament, Departaments } from './../../Interfaces/departamento.interface';
import { LegalizacionServiceService } from './../../Services/legalizacion-service.service';
import { SeguridadService } from './../../Services/seguridad.service';
import { ResourcesService } from './../../Services/resources.service';
import { AfterViewChecked, Component, Input, OnInit } from '@angular/core';
import {FormControl,Validators} from '@angular/forms';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import * as jQuery from 'jquery';
import { Populated } from 'src/app/Interfaces/populated.interface';
import { THIS_EXPR } from '@angular/compiler/src/output/output_ast';


@Component({
  selector: 'app-actualizacion-datos',
  templateUrl: './actualizacion-datos.component.html',
  styleUrls: ['./actualizacion-datos.component.css']
})
export class ActualizacionDatosComponent implements OnInit, AfterViewChecked {

  cityiniciado:boolean=false;
  deptoiniciado:boolean=false;
  data:any;
  submitted:boolean=false;
  greetings: Greeting[] = new Array();
  documenttypes: Documenttype[] = new Array();
  phonetypes: Phonetype[] = new Array();
  addressArray: any[] = new Array();
  Addresses: Address[] = new Array();
  departamentos: Departament[] = new Array();
  citys: City[] = new Array();
  populateds: Populated[] = new Array();
  idDepartamento: string = "0";
  idCitySelected: string = "0";
  idPopulatedSelected: string = "0";
  nombres:string="";
  emailPattern: any = "[^@\s]+@[^@\s]+\.[^@\s]+";
  validatePinLegalizationRequest: ValidatePinLegalizationRequest;
  alphabetArray:Alphabet[]= new Array();
  cardinalArray:string[]= new Array();
  clibBisFlag:boolean = false;

  constructor(
    private router:Router,
    private seguridadService: SeguridadService,
    private resourcesService: ResourcesService,
    private legalizacionService: LegalizacionServiceService,
    private validationService: ValidationService)
    {
      const actualizationData: ActualizationData = this.resourcesService.getActualizationData();
      this.toLoadArrays();
      this.data = {
        prepaidMin:'',
        greet:'',
        documentTypeSelect:  actualizationData.getDocuemntType(),
        nit: actualizationData.getDocNum(),
        expeditionDate: '',
        name: actualizationData.getNames(),
        lastName: actualizationData.getSurname(),
        birthDate:'',
        phonetype1:'',
        phonetype2:'',
        mail:'',
        department:'',
        city:'',
        municipality:'',
        neighborhood:'',
        cityAddress:'',
        poblado:'',
        main:'',
        main2:'',
        viaGen:'',
        viaGenNumber1:'',
        plate:'',
        depto:'',
        terms:false,
        alphabet1:'',
        alphabet2:'',
        cardinal1:'',
        cardinal2:'',
        phonetype:'',
        adr:'',
        builtAdr:'',
        wayNumber:''
      };
      this.deptoiniciado=false;
      this.cityiniciado=false;
      this.validatePinLegalizationRequest = this.resourcesService.getValidatePinLegalizationRequest();
      this.initialLoad();
      this.data.depto = "0";
      this.data.city = "0";
      this.data.poblado = "0";
  }

  ngOnInit(): void {

  }

  ngAfterViewChecked():void{
  }

  /*Evento de envio*/
  enviarBtn(){
    this.activateSpiner();
    const regex = /\//gi;
    const regex2 = /-/gi;
    this.data.prepaidMin = this.validatePinLegalizationRequest.getPrepaidMin();
    this.data.expeditionDate = this.validatePinLegalizationRequest.getExpeditionDate();
    this.data.expeditionDate = this.data.expeditionDate.replace(regex, '');
    this.data.expeditionDate = this.data.expeditionDate.replace(regex2, '');
    let bd = this.data.birthDate.replace(regex, '-');
    var splitDate= bd.split("-");
    bd = splitDate[0].concat("-").concat(splitDate[1]).concat("-").concat(splitDate[2]);
    const body = this.requestRegisterData(bd);
    const token = this.seguridadService.getEncrypt(body);
    this.validationService.legalizeMin(body, token)
      .subscribe((responseApi: ResponseApi) => {
        const error = responseApi.error;
        if(error == 0){
          this.deactivateSpiner();
          const response = responseApi.response;
          this.success(this.resourcesService.getFail12());
        }else{
          this.deactivateSpiner();
          this.fail(responseApi.response);
        }
      }, (error) => {
        this.deactivateSpiner();
        this.fail(this.resourcesService.getFail3());
      });
  }

  builtAddress(){


    const regex = /\//gi;
    let bd = this.data.birthDate.replace(regex, '-');
    var splitDate= bd.split("-");
    bd = splitDate[0].concat("-").concat(splitDate[1]).concat("-").concat(splitDate[2]);

    setTimeout(() => {
      let bis = '';
      if(this.clibBisFlag){
        bis = 'Bis ';
      }
      if(this.data.alphabet1 == 'Selecciona'){
        this.data.alphabet1 = '';
      }
      if(this.data.alphabet2 == 'Selecciona'){
        this.data.alphabet2 = '';
      }
      if(this.data.cardinal1 == 'Selecciona'){
        this.data.cardinal1 = '';
      }
      if(this.data.cardinal2 == 'Selecciona'){
        this.data.cardinal2 = '';
      }
      this.data.department = this.deptoById(this.idDepartamento);
      this.data.municipality = this.populatedById(this.idPopulatedSelected);
      let wayNumber = (<HTMLInputElement>document.getElementById('wayNumber')).value;
      let departamentAddress = '';
      if(this.idDepartamento == "0" || this.idDepartamento == ""){
        departamentAddress = '';
      }else{
        departamentAddress = this.data.department.concat(', ');
      }
      let cityAddress = '';
      if(this.idCitySelected == "0" || this.idCitySelected == ""){
        cityAddress = '';
        this.data.cityAddress = '';
      }else{
        let cityAddressSelected = this.cityById(this.idCitySelected);
        cityAddress = cityAddressSelected.concat(', ');
        this.data.cityAddress = cityAddressSelected;
      }
      let municipalityAddress = '';
      if(this.idPopulatedSelected == "0" || this.idPopulatedSelected == ""){
        municipalityAddress = '';
      }else{
        municipalityAddress = this.data.municipality.concat(', ');
      }
      let neighborhoodAddress = '';
      if(this.data.neighborhood == ''){
        neighborhoodAddress = '';
      }else{
        neighborhoodAddress = this.data.neighborhood.concat('/ ');
      }

      let addressend =''
      //.concat(departamentAddress)
      //.concat(cityAddress)
      //.concat(municipalityAddress)
      //.concat(neighborhoodAddress)
      .concat(this.data.main).concat(' ')
      .concat(wayNumber).concat(' ').concat(bis);
      if(this.data.alphabet1.length>=1){
        addressend = addressend.concat(this.data.alphabet1).concat(' ');
      }
      if(this.data.cardinal1.length>=1){
        addressend = addressend.concat(this.data.cardinal1).concat(' ');
      }
      addressend = addressend.concat(this.data.viaGen).concat(' ');

      if(this.data.alphabet2.length>=1){
        addressend = addressend.concat(this.data.alphabet2).concat(' ');
      }
      addressend = addressend.concat(this.data.plate).concat(' ');
      if(this.data.cardinal2.length>=1){
        addressend = addressend.concat(this.data.cardinal2).concat(' ');
      }
      addressend = (((addressend).replace("   "," ")).replace("  "," ")).trim();


      let addresSelected =''
      .concat(departamentAddress)
      .concat(cityAddress)
      .concat(municipalityAddress)
      .concat(neighborhoodAddress)
      .concat(this.data.main).concat(' ')
      .concat(wayNumber).concat(' ').concat(bis);
      if(this.data.alphabet1.length>=1){
        addresSelected = addresSelected.concat(this.data.alphabet1).concat(' ');
      }
      if(this.data.cardinal1.length>=1){
        addresSelected = addresSelected.concat(this.data.cardinal1).concat(' ');
      }
      addresSelected = addresSelected.concat(this.data.viaGen).concat(' ');

      if(this.data.alphabet2.length>=1){
        addresSelected = addresSelected.concat(this.data.alphabet2).concat(' ');
      }
      addresSelected = addresSelected.concat(this.data.plate).concat(' ');
      if(this.data.cardinal2.length>=1){
        addresSelected = addresSelected.concat(this.data.cardinal2).concat(' ');
      }

      addresSelected = (((addresSelected).replace("   "," ")).replace("  "," ")).trim();


      (<HTMLInputElement>document.getElementById('id-address-selected')).value = addresSelected;

      this.data.builtAdr = addressend;
    },300);
  }

  /*Obtiene la lista de departamentos desde el web service*/
  private deptos(){
    try{
      const body = { data: { token: this.seguridadService.getToken() } };
      const token = this.seguridadService.getEncrypt(body);
      this.legalizacionService.departaments(body, token)
        .subscribe((resp: Departaments) => {
          const error = resp.error;
          if(error == 0){
            this.departamentos = resp.response;
          }else{
            this.fail(this.resourcesService.getFail6());
          }
        }, (error) => {
          console.log(error);
          this.fail(this.resourcesService.getFail6());
        });
    }
    catch(error){
      console.log(error);
    }
  }


  /*carga inicial de combos*/
initialLoad(){
  try{
    const body = { data: { token: this.seguridadService.getToken() } };
    const token = this.seguridadService.getEncrypt(body);
    this.legalizacionService.departaments(body, token)
      .subscribe((resp: Departaments) => {
        const error = resp.error;
        if(error == 0){
          this.departamentos = resp.response;
          this.Addresses = this.resourcesService.Addresses;
          this.Addresses.forEach((address:Address) => {
            let bb: any = {id:address.addressId.toString(),text:address.description};
            this.addressArray.push(bb);
          });
          this.greetings = this.resourcesService.greetings;
          this.documenttypes = this.resourcesService.documenttypes;
          this.phonetypes = this.resourcesService.phonetypes;
        }else{
          this.fail(this.resourcesService.getFail6());
        }
      }, (error) => {
        console.log(error);
        this.fail(this.resourcesService.getFail6());
      });
  }
  catch(error){
    console.log(error);
  }
}



  /*Se activa al cambiar el departamento en el combo de departamento*/
  deptoChanged(): void {
    const selectDepto = document.getElementById('selectDepto') as HTMLInputElement;
    const e = selectDepto.value;
    if(this.citys.length > 0){
      this.deptoiniciado=true;
    }
    this.toSelectCity(e);
    this.builtAddress();
  }

  /*Obtiene la lista de ciudades por el departamento seleccionado*/
  toSelectCity(departmentId: string): void {
    try{
      const body = { data: { depto: {departmentId:Number(departmentId) },token: this.seguridadService.getToken() } };
      const token = this.seguridadService.getEncrypt(body);
      this.legalizacionService.citybydepto(body, token)
        .subscribe((resp: any) => {
          const error = resp.error;
          if(error == 0){
            this.citys = resp.response;
            this.data.city = "0";
            if (this.citys.length>0){
              this.deptoiniciado=true;
            }
            this.idDepartamento = departmentId;
          }else{
            this.idDepartamento = "0";
            this.fail(this.resourcesService.getFail7());
          }
        }, (error) => {
          this.idDepartamento = "0";
          console.log(error);
          this.fail(this.resourcesService.getFail7());
        });
        this.populateds = new Array();
        this.data.poblado = "0";
    }
    catch(error){
      console.log(error);
    }
  }

  /*Obtiene la lista de poblados por La ciudad seleccionada*/
  toSelectPopulated(cityId: string): void {
    try{
      const body = { data: { city: {cityId:Number(cityId) },token: this.seguridadService.getToken() } };
      const token = this.seguridadService.getEncrypt(body);
      this.legalizacionService.populatedbycity(body, token)
        .subscribe((resp: any) => {
          const error = resp.error;
          if(error == 0){
            this.populateds = resp.response;
            this.idCitySelected = cityId;
          }else{
            this.idCitySelected = "0";
            this.fail(this.resourcesService.getFail8());
          }
        }, (error) => {
          this.idCitySelected = "0";
          this.fail(this.resourcesService.getFail8());
        });
    }
    catch(error){
      console.log(error);
    }
  }

  /*Se activa al cambiar la ciudad en el combo de ciudad*/
  changeCity(): void {
    const selectCity = document.getElementById('selectCity') as HTMLInputElement;
    const e = selectCity.value;
    this.idCitySelected = e;
    this.toSelectPopulated(this.idCitySelected);
    this.builtAddress();
  }

  /*Se activa al cambiar el centro poblado*/
  changePopulated(): void {
    const selectPopulated = document.getElementById('selectPopulated') as HTMLInputElement;
    const e = selectPopulated.value;
    this.idPopulatedSelected = e;
    this.builtAddress();
  }

  /*Muestra un mensaje modal de error*/
  fail(message: string): void{
    Swal.fire({
      imageUrl: './assets/images/fail.svg',
      imageWidth: 100,
      imageHeight: 100,
      html: '<p class="alertParagraph">'.concat(message).concat('</p>'),
      confirmButtonText: 'Cerrar',
        confirmButtonColor:'#EF3829',
      }).then((result) => {
      });
  }

    /*Muestra un mensaje modal de exito*/
    success(message: string): void{
      Swal.fire({
        html: '<p class="alertParagraph">'.concat(message).concat('</p>'),
        imageUrl: './assets/images/saved.svg',
        imageWidth: 100,
        imageHeight: 100,
        confirmButtonText: 'Cerrar',
        confirmButtonColor:'#EF3829'
        }).then((result) => {
          location.href=this.resourcesService.getRedireccion();
        });
    }

    error(){
      this.submitted=true;
      this.router.navigate([`../actualizacion-datos`]);
    }

  /*Muestra los terminos y condiciones de la pagina*/
  obtenerTyC(): void{
    this.getTyC();
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


    /*cierra modal*/
    closeModal(): void{
      ($('#dialogo1') as any).modal('hide');
    }

    /*Obtiene el departamento por su id*/
     deptoById(idDepartament:string):string{
       let depto = "";
       this.departamentos.forEach((departament:Departament)=>{
         if(departament.id == idDepartament){
           depto = departament.text;
         }
       });
       return depto;
      }

     /*Obtiene la ciudad por su id*/
     cityById(idCity:string):string{
      let cityReturn = "";
      this.citys.forEach((city:City)=>{
        if(city.id == idCity){
          cityReturn = city.text;
        }
      });
      return cityReturn;
     }

    /*Obtiene el centro poblado por su id*/
    populatedById(idPopulated:string):string{
      let populatedReturn = "";
      this.populateds.forEach((populated:Populated)=>{
        if(populated.id== idPopulated){
          populatedReturn = populated.text;
        }
      });
      return populatedReturn;
     }


     /*Carga los Array alphabetArray y cardinalArray*/
     toLoadArrays():void{
      this.alphabetArray.push(new Alphabet('A'));
      this.alphabetArray.push(new Alphabet('B'));
      this.alphabetArray.push(new Alphabet('C'));
      this.alphabetArray.push(new Alphabet('D'));
      this.alphabetArray.push(new Alphabet('E'));
      this.alphabetArray.push(new Alphabet('F'));
      this.alphabetArray.push(new Alphabet('G'));
      this.alphabetArray.push(new Alphabet('H'));
      this.alphabetArray.push(new Alphabet('I'));
      this.alphabetArray.push(new Alphabet('J'));
      this.alphabetArray.push(new Alphabet('K'));
      this.alphabetArray.push(new Alphabet('L'));
      this.alphabetArray.push(new Alphabet('M'));
      this.alphabetArray.push(new Alphabet('N'));
      this.alphabetArray.push(new Alphabet('Ñ'));
      this.alphabetArray.push(new Alphabet('O'));
      this.alphabetArray.push(new Alphabet('P'));
      this.alphabetArray.push(new Alphabet('Q'));
      this.alphabetArray.push(new Alphabet('R'));
      this.alphabetArray.push(new Alphabet('S'));
      this.alphabetArray.push(new Alphabet('T'));
      this.alphabetArray.push(new Alphabet('U'));
      this.alphabetArray.push(new Alphabet('V'));
      this.alphabetArray.push(new Alphabet('W'));
      this.alphabetArray.push(new Alphabet('X'));
      this.alphabetArray.push(new Alphabet('Y'));
      this.alphabetArray.push(new Alphabet('Z'));
      this.cardinalArray.push('Norte');
      this.cardinalArray.push('Sur');
      this.cardinalArray.push('Este');
      this.cardinalArray.push('Oeste');
     }



     clickSelect(){
      let cardinal2 =document.getElementById('cardinal2') as HTMLInputElement;
      let greet = document.getElementById('greetingSelect') as HTMLInputElement;
      let dir = document.getElementById('direccion') as HTMLInputElement;
      let cardinal1 =document.getElementById('cardinal1') as HTMLInputElement;
      let phone = document.getElementById('phoneTypeSelect') as HTMLInputElement;
      let main = document.getElementById('id-mainway') as HTMLInputElement;
      let depto = document.getElementById('selectDepto') as HTMLInputElement;
      let city = document.getElementById('selectCity') as HTMLInputElement;
      let populated = document.getElementById('selectPopulated') as HTMLInputElement;
      let alphabet = document.getElementById('selectAlphabet') as HTMLInputElement;
      let alphabet2 = document.getElementById('selectAlphabet2') as HTMLInputElement;

      if (this.data.cardinal2){
        cardinal2?.classList.remove('select');
        cardinal2?.classList.add('selected');
        }
      if(this.data.adr){
        dir?.classList.remove('select');
        dir?.classList.add('selected');
        }
      if (this.data.greet){
        greet?.classList.remove('select');
        greet?.classList.add('selected');
        }
      if(this.data.cardinal1){
      cardinal1?.classList.remove('select');
      cardinal1?.classList.add('selected');
        }
      if(this.data.phonetype){
      phone?.classList.remove('select');
      phone?.classList.add('selected');
        }
      if(this.data.main){
      main?.classList.remove('select');
      main?.classList.add('selected');
        }
      if(this.data.depto){
      depto?.classList.remove('select');
      depto?.classList.add('selected');
        }
      if(this.data.city){
        city?.classList.remove('select');
        city?.classList.add('selected');
        }
      if(this.data.poblado){
        populated?.classList.remove('select');
        populated?.classList.add('selected');
        }
      if(this.data.alphabet1){
        alphabet?.classList.remove('select');
        alphabet?.classList.add('selected');
        }
      // ng: alphabet2 id: selectAlphabet2
      if(this.data.alphabet2){
        alphabet2?.classList.remove('select');
        alphabet2?.classList.add('selected');
        }


     }



     /*marca el boton bis como seleccionado o no*/
     clickBis(){
        let bisButton = document.getElementById('bisButton') as HTMLInputElement;
        if(this.clibBisFlag){
        bisButton?.classList.remove('bisselected');
        bisButton?.classList.add('bisdisselected');
        this.clibBisFlag = false;
       }else{
        this.clibBisFlag = true;
        bisButton?.classList.add('bisselected');
        bisButton?.classList.remove('bisdisselected');
       }
       this.builtAddress();
     }


    /*Conforma el request que se enviará al servicio*/
    requestRegisterData(birthDate:string):any{
      const body = {
        data: {
          prepaidMin:this.data.prepaidMin,
          greetings:this.data.greet,
          docType:this.data.documentTypeSelect,
          docNum:this.data.nit.toString(),
          expeditionDate:this.data.expeditionDate,
          name:this.data.name,
          lastName:this.data.lastName,
          birthDate:birthDate,
          indicativeMin:this.data.phonetype1.toString(),
          phone:this.data.phonetype2.toString(),
          mail:this.data.mail,
          department:this.data.department,
          city:this.data.cityAddress,
          municipality:this.data.municipality,
          neighborhood:this.data.neighborhood,
          address:this.data.builtAdr,
          postalCode:"",
          channel:this.resourcesService.getChannel()
         }
        };
        return body;
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


    placaFocusLost():void{
      this.builtAddress();
    }
}
