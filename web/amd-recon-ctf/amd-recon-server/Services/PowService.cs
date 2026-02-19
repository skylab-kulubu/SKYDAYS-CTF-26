using amd_recon_server.Models;
using System.Security.Cryptography;
using System.Text;

namespace amd_recon_server.Services;

public class PowService
{
    private readonly string _secretKey = "CTF_Gizli_Anahtar_Buraya";

    public (string salt, string signature) GenerateChallenge()
    {
        var salt = Guid.NewGuid().ToString();
        var signature = CreateSignature(salt);
        return (salt, signature);
    }

    public bool Verify(AnswerDto dto, string answerForValidation)
    {
        if (dto.Signature != CreateSignature(dto.Salt)) return false;

        var input = dto.Salt + answerForValidation + dto.Nonce;
        var hashBytes = SHA256.HashData(Encoding.UTF8.GetBytes(input));
        var hashString = Convert.ToHexString(hashBytes).ToLower();

        if (!hashString.StartsWith("00000")) return false;

        char sixthChar = hashString[5];
        return "01234567".Contains(sixthChar);
    }

    private string CreateSignature(string salt)
    {
        using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(_secretKey));
        var hash = hmac.ComputeHash(Encoding.UTF8.GetBytes(salt));
        return Convert.ToHexString(hash);
    }
}
