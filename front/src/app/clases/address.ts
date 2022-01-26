
export class Address{
  addressId: number;
  description: string;
  status: string;
  order: number;
  externalId: number;

  constructor(
    addressId: number,
    description: string,
    status: string,
    order: number,
    externalId: number){
    this.addressId = addressId;
    this.description = description;
    this.status = status;
    this.order = order;
    this.externalId = externalId;
  }

}
