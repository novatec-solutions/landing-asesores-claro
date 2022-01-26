import { ActualizationData } from './../clases/actualizationData';
import { ValidatePinLegalizationRequest } from './../clases/validatepinlegalizationrequest';
import { Address } from './../clases/address';
import { Greeting } from './../clases/greeting';
import { Documenttype } from './../clases/documenttype';
import { Phonetype } from './../clases/phonetype';
import { Basicdata, Various } from './../Interfaces/basicdata.interface';
import { ValidatePinResponse } from './../Interfaces/validatepinresponse.interface';
import { Injectable } from '@angular/core';
import { concat } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class ResourcesService {

  phonetypes: Phonetype[];
  documenttypes: Documenttype[];
  greetings: Greeting[]
  Addresses: Address[];
  channel: string = "";
  redireccion: string = "";
  validatePinLegalizationRequest: ValidatePinLegalizationRequest;
  actualizationData: ActualizationData;
  timeout:string;
  fail1:string;
  fail2:string;
  fail3:string;
  fail4:string;
  fail5:string;
  fail6:string;
  fail7:string;
  fail8:string;
  fail9:string;
  fail10:string;
  fail11:string;
  fail12:string;
  loaded:boolean = false;

  constructor() {
    this.phonetypes= new Array();
    this.documenttypes= new Array();
    this.greetings= new Array();
    this.Addresses= new Array();
    this.validatePinLegalizationRequest = new ValidatePinLegalizationRequest(0,"","","","","","");
    this.actualizationData = new  ActualizationData("","","",0);
  }

  public loadInitialResources(basicdata: Basicdata) {
    this.loaded = false;
    const variousArray  = basicdata.response.various;
    variousArray.forEach((various: Various) => {
      const variousId = various.variousId;
      let description = various.description;
      const type = various.type;
      const order = various.order;
      const status = various.status;
      const externalId = various.externalId;
      if(type == "REDIRECCION"){
        this.setRedireccion(description);
      }else{
        if(type == "TIMEOUT"){
          this.setTimeout(description);
        }else{
          if(type == "FAIL1"){
            description = description.toLowerCase();
            description = description.charAt(0).toUpperCase().concat(description.slice(1));
            this.setFail1(description);
          }else{
            if(type == "FAIL2"){
              description = description.toLowerCase();
            description = description.charAt(0).toUpperCase().concat(description.slice(1));
              this.setFail2(description);
            }else{
              if(type == "FAIL3"){
                description = description.toLowerCase();
            description = description.charAt(0).toUpperCase().concat(description.slice(1));
                this.setFail3(description);
              }else{
                if(type == "FAIL4"){
                  description = description.toLowerCase();
                  description = description.charAt(0).toUpperCase().concat(description.slice(1));
                  this.setFail4(description);
                }else{
                  if(type == "FAIL5"){
                    description = description.toLowerCase();
                    description = description.charAt(0).toUpperCase().concat(description.slice(1));
                    this.setFail5(description);
                  }else{
                    if(type == "FAIL6"){
                      description = description.toLowerCase();
                      description = description.charAt(0).toUpperCase().concat(description.slice(1));
                      this.setFail6(description);
                    }else{
                      if(type == "FAIL7"){
                        description = description.toLowerCase();
                        description = description.charAt(0).toUpperCase().concat(description.slice(1));
                        this.setFail7(description);
                      }else{
                        if(type == "FAIL8"){
                          description = description.toLowerCase();
                          description = description.charAt(0).toUpperCase().concat(description.slice(1));
                          this.setFail8(description);
                        }else{
                          if(type == "FAIL9"){
                            description = description.toLowerCase();
                            description = description.charAt(0).toUpperCase().concat(description.slice(1));
                            this.setFail9(description);
                          }else{
                            if(type == "FAIL10"){
                              description = description.toLowerCase();
                              description = description.charAt(0).toUpperCase().concat(description.slice(1));
                              this.setFail10(description);
                            }else{
                              if(type == "FAIL11"){
                                description = description.toLowerCase();
                                description = description.charAt(0).toUpperCase().concat(description.slice(1));
                                this.setFail11(description);
                              }else{
                                if(type == "FAIL12"){
                                  description = description.toLowerCase();
                                  description = description.charAt(0).toUpperCase().concat(description.slice(1));
                                  this.setFail12(description);
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    });

    this.loaded = true;
  }





  public loadResources(basicdata: Basicdata) {
    this.documenttypes = new Array();
    this.greetings = new Array();
    this.phonetypes = new Array();
    this.Addresses = new Array();
    const variousArray  = basicdata.response.various;
    variousArray.forEach((various: Various) => {
      const variousId = various.variousId;
      const description = various.description;
      const type = various.type;
      const order = various.order;
      const status = various.status;
      const externalId = various.externalId;
      if(type == "SALUDO"){
        this.greetings.push(new Greeting(variousId,description,status,order,externalId));
      }else{
        if(type == "TELEFONO"){
          this.phonetypes.push(new Phonetype(variousId,description,status,order,externalId));
        }else{
          if(type == "DOCUMENTO"){
            this.documenttypes.push(new Documenttype(variousId,description,status,order,externalId));
          }else{
            if(type == "DIRECCION"){
              this.Addresses.push(new Address(variousId,description,status,order,externalId));
            }else{
              if(type == "REDIRECCION"){
                this.setRedireccion(description);
              }else{
                if(type == "TIMEOUT"){
                  this.setTimeout(description);
                }else{
                  if(type == "FAIL2"){
                    this.setFail2(description);
                  }
                }
              }
            }
          }
        }
      }
    });
  }

  public externalId(documentTypeId: number): number{
    let extarnalId = 0;
    this.documenttypes.forEach((dt: Documenttype)=> {
      if(dt.documenttypeId == documentTypeId){
        extarnalId = dt.externalId;
      }
    });
    return extarnalId;
  }

  public getChannel():string{
    return this.channel;
  }

  public setChannel(channel: string):void{
    this.channel = channel;
  }

  public setValidatePinLegalizationRequest(validatePinLegalizationRequest:ValidatePinLegalizationRequest){
    this.validatePinLegalizationRequest = validatePinLegalizationRequest;
  }

  public getValidatePinLegalizationRequest(){
    return this.validatePinLegalizationRequest;
  }

  public setActualizationData(actualizationData:ActualizationData){
    this.actualizationData = actualizationData;
  }

  public getActualizationData():ActualizationData{
    return this.actualizationData;
  }

  public setRedireccion(redireccion:string):void{
    this.redireccion = redireccion;
  }

  public getRedireccion():string{
    return this.redireccion;
  }

  public setTimeout(timeout:string):void{
    this.timeout = timeout;
  }

  public getTimeout():string{
    return this.timeout;
  }



  public setFail1(fail1:string):void{
    this.fail1 = fail1;
  }

  public getFail1():string{
    return this.fail1;
  }


  public setFail2(fail2:string):void{
    this.fail2 = fail2;
  }

  public getFail2():string{
    return this.fail2;
  }

  public setFail3(fail3:string):void{
    this.fail3 = fail3;
  }

  public getFail3():string{
    return this.fail3;
  }

  public setFail4(fail4:string):void{
    this.fail4 = fail4;
  }

  public getFail4():string{
    return this.fail4;
  }


  public setFail5(fail5:string):void{
    this.fail5= fail5;
  }

  public getFail5():string{
    return this.fail5;
  }


  public setFail6(fail6:string):void{
    this.fail6 = fail6;
  }

  public getFail6():string{
    return this.fail6;
  }


  public setFail7(fail7:string):void{
    this.fail7 = fail7;
  }

  public getFail7():string{
    return this.fail7;
  }

  public setFail8(fail8:string):void{
    this.fail8 = fail8;
  }

  public getFail8():string{
    return this.fail8;
  }

  public setFail9(fail9:string):void{
    this.fail9 = fail9;
  }

  public getFail9():string{
    return this.fail9;
  }


  public setFail10(fail10:string):void{
    this.fail10 = fail10;
  }

  public getFail10():string{
    return this.fail10;
  }

  public setFail11(fail11:string):void{
    this.fail11 = fail11;
  }

  public getFail11():string{
    return this.fail11;
  }


  public setFail12(fail12:string):void{
    this.fail12 = fail12;
  }

  public getFail12():string{
    return this.fail12;
  }


  public getLoaded():boolean{
    return this.loaded;
  }

}
