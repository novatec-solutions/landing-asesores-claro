export interface ValidatePinResponse{
  error: number;
  isPinValid: boolean;
  response: string;
  data: Data;
}

export interface Data{
  names: string;
  surname: string;
  docNum: string;
  docuemntType: number;
}
