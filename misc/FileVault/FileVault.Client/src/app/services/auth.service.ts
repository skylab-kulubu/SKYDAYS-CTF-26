import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { baseUrl } from '../models/BaseUrl';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  apiName = 'auth';

  constructor(private http: HttpClient) {}

  login(loginRequest: LoginRequest): Observable<any> {
    return this.http.post<any>(`${baseUrl}/${this.apiName}/login`, loginRequest);
  }

  register(registerRequest: RegisterRequest): Observable<any> {
    return this.http.post<any>(`${baseUrl}/${this.apiName}/register`, registerRequest);
  }

  vipRegister(registerRequest: RegisterRequest): Observable<any> {
    return this.http.post<any>(`${baseUrl}/${this.apiName}/VIPRegister`, registerRequest);
  }
}
