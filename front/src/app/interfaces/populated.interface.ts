export interface Populateds{
  error: number;
  response: Populated[];
}

export interface Populated{
  id: string;
  text: string;
  status: string;
  order: number;
  danecode: string;
  cityId: string;
}
