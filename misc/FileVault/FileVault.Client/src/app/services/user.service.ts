import { Injectable } from '@angular/core';
import { TokenService } from './token.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { baseUrl } from '../models/BaseUrl';

@Injectable({
  providedIn: 'root',
})
export class UserService {
  apiName = 'user'

  constructor(private tokenService: TokenService, private http: HttpClient) {}

  goVIP(): Observable<any> {
    const headers = new HttpHeaders({ Authorization: `Bearer ${this.tokenService.getToken()}`});
    return this.http.post<any>(`${baseUrl}/${this.apiName}/login`, {}, {headers});
  }
}
