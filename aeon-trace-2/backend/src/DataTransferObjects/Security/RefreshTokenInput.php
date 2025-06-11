<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Security;

use Symfony\Component\Serializer\Annotation\SerializedName;

class RefreshTokenInput
{
    public function __construct(
        #[SerializedName('refresh_token')]
        public string $refreshToken,
    ) {
    }
}
