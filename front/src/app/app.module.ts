import { AppRoutingModule } from './app-routing.module';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppComponent } from './app.component';
import { ActualizacionComponent } from './Components/actualizacion/actualizacion.component';
import { ActualizacionDatosComponent } from './Components/actualizacion-datos/actualizacion-datos.component';
import { ValidacionOtpComponent } from './Components/validacion-otp/validacion-otp.component';
import { FormsModule } from '@angular/forms';
import { NgSelect2Module } from 'ng-select2';
import { HttpClientModule } from '@angular/common/http';

@NgModule({
  declarations: [
    AppComponent,
    ActualizacionComponent,
    ActualizacionDatosComponent,
    ValidacionOtpComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    AppRoutingModule,
    NgSelect2Module,
    HttpClientModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
