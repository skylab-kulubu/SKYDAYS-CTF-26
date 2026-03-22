import { Injectable } from '@angular/core';
import { TokenService } from './token.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { baseUrl } from '../models/BaseUrl';

@Injectable({
  providedIn: 'root',
})
export class FileService {
  apiName = 'file'

  constructor(private tokenService: TokenService, private http: HttpClient) {}

  getMyFileNames(): Observable<string[]> {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.tokenService.getToken()}`);
    return this.http.get<string[]>(`${baseUrl}/${this.apiName}/GetMyFileNames`, { headers });
  }

  getMyUsedSpaceAsMB(): Observable<number> {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.tokenService.getToken()}`);
    return this.http.get<number>(`${baseUrl}/${this.apiName}/GetMyUsedSpaceAsMB`, { headers });
  }

  download(fileName: string): Observable<{ blob: Blob; fileName: string }> {
    const headers = new HttpHeaders({ Authorization: `Bearer ${this.tokenService.getToken()}`});

    return this.http
      .get(`${baseUrl}/${this.apiName}/Download?fileName=${fileName}`, {headers, responseType: 'blob',observe: 'response'})
      .pipe(
        map(response => {
          return { blob: response.body as Blob, fileName };
        })
      );
  }

  uploadFile(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);
    const headers = new HttpHeaders({'Authorization': `Bearer ${this.tokenService.getToken()}`});
    return this.http.post<any>(`${baseUrl}/${this.apiName}/UploadFile`, formData, { headers });
  }

  delete(fileName: string): Observable<any> {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.tokenService.getToken()}`);
    return this.http.delete<any>(`${baseUrl}/${this.apiName}/Delete?fileName=${fileName}`, { headers });
  }
}
