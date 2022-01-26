export interface Citys{
  error: number;
  response: City[];
}

export interface City{
  id: string;
  departmentId: string;
  text: string;
  status: string;
  order: number;
  danecode: string;
}
