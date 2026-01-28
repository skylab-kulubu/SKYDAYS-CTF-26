#include <Windows.h>
#include <iostream>
#include <string>
#include <sstream>
#include <iomanip>

const uint16_t saved_hwid[] =
{
    0x155, 0x156, 0x157, 0x150, 0x151, 0x152, 0x104, 0x105, // abcdef01 ^ 0x134
    0x151, 0x100, 0x104, 0x151, 0x15b, 0x143 // e40eow ^ 0x134
};

constexpr size_t length = sizeof(saved_hwid) / sizeof(uint16_t);

DWORD GetVolumeSerial()
{
    DWORD volumeSerialNumber = 0;
    if (GetVolumeInformationA("C:\\", NULL, 0, &volumeSerialNumber, NULL, NULL, NULL, 0)) // msdn'den referans alinabilir
    {
        return volumeSerialNumber;
    }
    return 0;
}

bool CheckHWID(const std::string& userHWID)
{
    if (userHWID.length() != length)
        return false;
    for (size_t i = 0; i < userHWID.length(); ++i)
    {
        if ((uint16_t)userHWID[i] != (uint16_t)(saved_hwid[i] ^ 0x134))
        {
            return false;
        }
    }
    return true;
}

int main()
{
    std::cout << "Press a key to begin authentication";
    std::cin.get();

    std::stringstream ss;
    ss << std::hex << std::setw(8) << std::setfill('0') << GetVolumeSerial();
    std::string userHWID = ss.str();

    std::cout << "Your HWID: " << userHWID << std::endl;

    userHWID = userHWID + "e40eow"; // bi tik daha zorlasin diye

    if (CheckHWID(userHWID)) // su an hookla ugrasmadan sadece xor yapilarak cozulebilir hooku zorunlu tutmaya calisacagim bu gostermelik
    {
        uint8_t keys[] = // gunu kurtarmalik yaptim ileride degistirilebilir
        {                 // (if statement patchlenmesin diye flagin dogru printlenmesi icin hwidnin de dogru olmasi lazim)
            0x12, 0x09, 0x1a, 0x00, 0x04, 0x1f, 0x43, 0x6e,
            0x0d, 0x5b, 0x5f, 0x0e
        };
        std::string output = "";
        for (size_t i = 0; i < 12; ++i)
        {
            output += (char)(userHWID[i] ^ keys[i]);
        }

        std::cout << "flag{" << output << "}" << std::endl;
    }
    else
    {
        std::cout << "Not authorized. Terminating.\n";
    }

    std::cin.get();
    return 0;
}
