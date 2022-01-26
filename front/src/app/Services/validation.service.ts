import { ValidatePinResponse } from './../Interfaces/validatepinresponse.interface';
import { ResponseApi } from './../Interfaces/response-api.interface';
import { RequestBody } from './../Interfaces/request-body.interface';
import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';


const base_url = environment.base_url;

@Injectable({
  providedIn: 'root'
})
export class ValidationService {

  private token: string;

  constructor(private http: HttpClient) {
    this.token = "";
  }

  get headers() {
    return {
      headers: {
        'X-SESSION-TOKEN': this.token,
        'X-ORIGIN': 'ANG'
      }
    };
  }

  public generatePin(body: RequestBody, token: string): Observable<ResponseApi> {
    this.token = token;
    const url = `${base_url}/v1/validationLegalizationPin`;
    return this.http.post<ResponseApi>(url, body, this.headers);
  }

  public validatePinLegalization(body: RequestBody, token: string): Observable<ValidatePinResponse> {
    this.token = token;
    const url = `${base_url}/v1/validatePinLegalization`;
    return this.http.post<ValidatePinResponse>(url, body, this.headers);
  }

  public legalizeMin(body: RequestBody, token: string): Observable<ResponseApi> {
    this.token = token;
    const url = `${base_url}/v1/legalizeMin`;
    return this.http.post<ResponseApi>(url, body, this.headers);
  }
}
