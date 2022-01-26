export interface Departaments{
  error: number;
  response: Departament[];
}

export interface Departament{
  id: string;
  text: string;
  status: string;
  order: number;
  danecode: string;
}
