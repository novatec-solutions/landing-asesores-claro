
export class ValidatePinLegalizationRequest{
  pin: number;
  prepaidMin: string;
  docType: string;
  docNum: string;
  expeditionDate: string;
  lastName: string;
  channel: string;

  constructor(
    pin: number,
    prepaidMin: string,
    docType: string,
    docNum: string,
    expeditionDate: string,
    lastName: string,
    channel: string
   ){
    this.pin = pin;
    this.prepaidMin = prepaidMin;
    this.docType = docType;
    this.docNum = docNum;
    this.expeditionDate = expeditionDate;
    this.lastName = lastName;
    this.channel = channel;
  }

  setPin(pin: number){
    this.pin = pin;
  }

  setPrepaidMin(prepaidMin: string){
    this.prepaidMin = prepaidMin;
  }

  setDocType(docType: string){
    this.docType = docType;
  }

  setDocNum(docNum: string){
    this.docNum = docNum;
  }

  setExpeditionDate(expeditionDate: string){
    this.expeditionDate = expeditionDate;
  }

  setLastName(lastName: string){
    this.lastName = lastName;
  }

  setChannel(channel: string){
    this.channel = channel;
  }


  getPin(){
    return this.pin;
  }

  getPrepaidMin(){
    return this.prepaidMin;
  }

  getDocType(){
    return this.docType;
  }

  getDocNum(){
    return this.docNum;
  }

  getExpeditionDate(){
    return this.expeditionDate;
  }

  getLastName(){
    return this.lastName;
  }

  getChannel(){
    return this.channel;
  }
}



