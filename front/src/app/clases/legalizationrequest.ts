export class LegalizationRequest{

  prepaidMin:string;
  greetings:string;
  docType:string;
  docNum:string;
  expeditionDate:string;
  name:string;
  surname:string;
  birthDate:string;
  indicativeMin:string;
  phone:string;
  mail:string;
  department:string;
  city:string;
  municipality:string;
  neighborhood:string;
  address:string; // dirección Armada
  postalCode:string; // lo deje, pero se puede enviar vacío
  channel:string;

  constructor(
    prepaidMin:string,
    greetings:string,
    docType:string,
    docNum:string,
    expeditionDate:string,
    name:string,
    surname:string,
    birthDate:string,
    indicativeMin:string,
    phone:string,
    mail:string,
    department:string,
    city:string,
    municipality:string,
    neighborhood:string,
    address:string,
    postalCode:string,
    channel:string
    ){
      this.prepaidMin = prepaidMin;
      this.greetings = greetings;
      this.docType = docType;
      this.docNum = docNum;
      this.expeditionDate = expeditionDate;
      this.name = name;
      this.surname = surname;
      this.birthDate = birthDate;
      this.indicativeMin = indicativeMin;
      this.phone = phone;
      this.mail = mail;
      this.department = department;
      this.city = city;
      this.municipality = municipality;
      this.neighborhood = neighborhood;
      this.address = address;
      this.postalCode = postalCode;
      this.channel = channel;
  }

  }
