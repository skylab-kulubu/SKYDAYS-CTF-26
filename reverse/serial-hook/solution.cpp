#include <Windows.h>
#include "MinHook.h"

typedef BOOL(WINAPI* pGetVolumeInformationA)(LPCSTR, LPSTR, DWORD, LPDWORD, LPDWORD, LPDWORD, LPSTR, DWORD); // function signature
pGetVolumeInformationA pOriginalGetVolumeInformationA = nullptr; // minhook orijinali buna veriyor orijinal functionu calistirmak icin bunu kullanmak lazim

DWORD dwFakeSerial = 2882400001; // abcdef01 hex -> decimal

BOOL WINAPI HookGetVolumeInformationA(
    LPCSTR lpRootPathName, LPSTR lpVolumeNameBuffer, DWORD nVolumeNameSize,
    LPDWORD lpVolumeSerialNumber, LPDWORD lpMaximumComponentLength,
    LPDWORD lpFileSystemFlags, LPSTR lpFileSystemNameBuffer, DWORD nFileSystemNameSize
) 
{
    BOOL result = pOriginalGetVolumeInformationA(
        lpRootPathName, lpVolumeNameBuffer, nVolumeNameSize,
        lpVolumeSerialNumber, lpMaximumComponentLength,
        lpFileSystemFlags, lpFileSystemNameBuffer, nFileSystemNameSize
    ); // amac ilk orijinali calistirip tum degerleri alip sonra seriali degistirmek

    if (result && lpVolumeSerialNumber != nullptr) {
        *lpVolumeSerialNumber = dwFakeSerial; // serial istenilen degerle degistiriliyor
    }
    MessageBoxA(NULL, "triggered", "hook", 0);
    return result;
}

BOOL APIENTRY DllMain(HMODULE hModule, DWORD ul_reason_for_call, LPVOID lpReserved) {
    if (ul_reason_for_call == DLL_PROCESS_ATTACH) {
        MessageBoxA(0, "entry", "hook", 0);
        if (MH_Initialize() == MH_OK) {
            if (MH_CreateHookApi(L"kernel32",                // bu libraryde
                                 "GetVolumeInformationA",    // bu function
                                 &HookGetVolumeInformationA, // calistirilinca hook'a yonlendiriliyor
                                 reinterpret_cast<LPVOID*>(&pOriginalGetVolumeInformationA)) == MH_OK)
            {
                MH_EnableHook(MH_ALL_HOOKS);
            }
        }
    }
    return TRUE;
}
