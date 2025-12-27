using amd_recon_server.Models;
using System.Security.Cryptography;
using System.Text;

namespace amd_recon_server.Repositories;

public class AnswerRepository
{
    public AnswerRepository() { }

    public bool TestAnswer(AnswerDto answerDto)
    {
        if (answerDto.Id == 4)
        {
            answerDto.Answer = answerDto.Answer.ToLower();
        }

        var hash = Hash(answerDto.Answer);
        switch (answerDto.Id)
        {
            case 1:
                return hash.Equals("21e24008bebacdb80e432d2435c62ad3b007a05387177ecebde0f023f6f65745");
            case 2:
                return hash.Equals("edcbb4277dbfa19a0fcc82f4328c4afd261f333efda92e664c7cc5b547442a51");
            case 3:
                return hash.Equals("c6d5ed757b66bfd3147a10410e0922b701baf55c401a1491e314d65fba08d15d");
            case 4:
                return hash.Equals("c71a6279e861d8c5fd032f8835b98cff63fabc872038d452b3f70bf2951da7d0");
            default:
                return false;
        }
    }

    public string GetFlag(List<AnswerDto> dtos)
    {
        var total = "";
        foreach (var dto in dtos)
        {
            if (!TestAnswer(dto))
            {
                return "";
            }

            if (dto.Id == 4)
            {
                dto.Answer = dto.Answer.ToLower();
            }

            total += dto.Answer;
        }

        var hash = Hash(total);
        var p1 = hash.Substring(0, 4);
        var p2 = hash.Substring(hash.Length / 2, 4);
        var p3 = hash.Substring(hash.Length - 4, 4);

        var flag = $"SKYDAYS26{{{p1}-{p2}-{p3}}}";
        return flag;
    }

    private string Hash(string input)
    {
        using var sha = SHA256.Create();
        var bytes = Encoding.UTF8.GetBytes(input);
        var hashBytes = sha.ComputeHash(bytes);
        return Convert.ToHexString(hashBytes).ToLowerInvariant();
    }
}
