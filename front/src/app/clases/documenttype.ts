export class Documenttype{
  documenttypeId: number;
  description: string;
  status: string;
  order: number;
  externalId: number;


  constructor(
    documenttypeId: number,
    description: string,
    status: string,
    order: number,
    externalId: number){
    this.documenttypeId = documenttypeId;
    this.description = description;
    this.status = status;
    this.order = order;
    this.externalId = externalId;
  }
}
