export class ActualizationData{
  names: string;
  surname: string;
  docNum: string;
  docuemntType: number;

  constructor(
    names: string,
    surname: string,
    docNum: string,
    docuemntType: number){
      this.names = names;
      this.surname = surname;
      this.docNum = docNum;
      this.docuemntType = docuemntType;
  }


  getNames(){
    return this.names;
  }

  getSurname(){
    return this.surname;
  }

  getDocNum(){
    return this.docNum;
  }

  getDocuemntType(){
    return this.docuemntType;
  }



  setNames(names:string){
    this.names = names;
  }

  setSurname(surname:string){
    this.surname = surname;
  }

  setDocNum(docNum:string){
    this.docNum = docNum;
  }

  setDocuemntType(docuemntType:number){
    this.docuemntType = docuemntType;
  }


}
