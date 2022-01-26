export class Greeting{
  greetingId: number;
  description: string;
  status: string;
  order: number;
  externalId: number;


  constructor(
    greetingId: number,
    description: string,
    status: string,
    order: number,
    externalId: number){
    this.greetingId = greetingId;
    this.description = description;
    this.status = status;
    this.order = order;
    this.externalId = externalId;
  }


}
