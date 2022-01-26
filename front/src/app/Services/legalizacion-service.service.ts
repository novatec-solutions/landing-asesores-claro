import { Citys } from './../Interfaces/city.interface';
import { Departaments } from './../Interfaces/departamento.interface';
import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { RequestBody } from '../Interfaces/request-body.interface';
import { Populateds } from '../Interfaces/populated.interface';
import { Basicdata } from '../Interfaces/basicdata.interface';

const base_url = environment.base_url;


@Injectable({
  providedIn: 'root'
})

export class LegalizacionServiceService {

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

  public departaments(body: RequestBody, token: string): Observable<Departaments> {
    this.token = token;
    const url = `${base_url}/v1/depto`;
    return this.http.post<Departaments>(url, body, this.headers);
  }

  public citybydepto(body: RequestBody, token: string): Observable<Citys> {
    this.token = token;
    const url = `${base_url}/v1/citybydepto`;
    return this.http.post<Citys>(url, body, this.headers);
  }

  public populatedbycity(body: RequestBody, token: string): Observable<Populateds> {
    this.token = token;
    const url = `${base_url}/v1/populatedbycity`;
    return this.http.post<Populateds>(url, body, this.headers);
  }

  public getVarious(body: RequestBody, token: string): Observable<Basicdata> {
    this.token = token;
    const url = `${base_url}/v1/getVarious`;
    return this.http.post<Basicdata>(url, body, this.headers);
  }


  public getInitialVarious(body: RequestBody, token: string): Observable<Basicdata> {
    this.token = token;
    const url = `${base_url}/v1/getInitialVarious`;
    return this.http.post<Basicdata>(url, body, this.headers);
  }


  public getVariousByType(body: RequestBody, token: string): Observable<Basicdata> {
    this.token = token;
    const url = `${base_url}/v1/getVariousByType`;
    return this.http.post<Basicdata>(url, body, this.headers);
  }

}
