export interface Basicdata{
  error: number;
  response: {
    various: Various[];
  }
}

export interface Various{
  variousId: number;
  description: string;
  type: string;
  order: number;
  status: string;
  externalId: number;
}
