import { ModuleWithProviders, NgModule } from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import { ActualizacionComponent } from './components/actualizacion/actualizacion.component';
import { ValidacionOtpComponent } from './components/validacion-otp/validacion-otp.component';
import { ActualizacionDatosComponent } from './components/actualizacion-datos/actualizacion-datos.component';

const routes: Routes = [
    {path:'', component:ActualizacionComponent},
    {path:'actualizacion',component:ActualizacionComponent},
    {path:'validacion-otp',component:ValidacionOtpComponent},
    {path:'actualizacion-datos',component:ActualizacionDatosComponent},
    {path:'**',component:ActualizacionComponent}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
