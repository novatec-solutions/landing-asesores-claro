export class Phonetype{
  phonetypeId: number;
  description: string;
  status: string;
  order: number;
  externalId: number;

  constructor(
    phonetypeId: number,
    description: string,
    status: string,
    order: number,
    externalId: number){
    this.phonetypeId = phonetypeId;
    this.description = description;
    this.status = status;
    this.order = order;
    this.externalId = externalId;
  }
}
