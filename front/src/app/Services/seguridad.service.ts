import { Injectable } from '@angular/core';
import { UUID } from 'angular2-uuid';
import * as CryptoJS from 'crypto-js';
import * as moment from 'moment-timezone';


@Injectable({
  providedIn: 'root'
})


export class SeguridadService {

  private key = '1WR3AqfY33J@5yavQklFkLDmpb6YQY0o';

  constructor() { }

  public getToken(): string {
    return UUID.UUID();
  }

  public getEncrypt(body: any): string {
    body.expDate = moment().tz('America/Bogota').format('YYYY-MM-DD HH:mm:ss');
    body = JSON.stringify(body);
    return CryptoJS.AES.encrypt(body.trim(), this.key.trim()).toString();
  }

  public deEncrypt(dato:string): string {
    var ciphertext = CryptoJS.enc.Hex.parse(dato);
    var pwhash = CryptoJS.SHA1(CryptoJS.enc.Utf8.parse(this.key));
    var key = CryptoJS.enc.Hex.parse(pwhash.toString(CryptoJS.enc.Hex).substr(0, 32));
    var decrypted = CryptoJS.AES.decrypt({
      ciphertext: ciphertext
     }, key, {
        mode:     CryptoJS.mode.ECB,
        padding:  CryptoJS.pad.Pkcs7
    });
    return decrypted.toString(CryptoJS.enc.Utf8);
  }

}
