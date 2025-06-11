<?php

declare(strict_types=1);
// api/src/Encoder/MultipartDecoder.php

namespace App\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use function is_array;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }


    /** @return array<string>|null */
    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        return array_map(static function ($element) {
            if (is_string($element)) {
                $decoded = json_decode($element, true);
                return is_array($decoded) ? $decoded : $element;
            }
            return $element; // return unchanged if not a string
            }, $request->request->all()) + $request->files->all();
    }

    public function supportsDecoding(string $format): bool
    {
        return $format === self::FORMAT;
    }
}
