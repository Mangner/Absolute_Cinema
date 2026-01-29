<?php

namespace Middleware\Attribute;

use Attribute;

/**
 * Atrybut określający dozwolone metody HTTP dla danej akcji kontrolera.
 * 
 * Przykład użycia:
 * #[AllowedMethods(['GET'])]
 * #[AllowedMethods(['POST'])]
 * #[AllowedMethods(['GET', 'POST'])]
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AllowedMethods
{
    /**
     * @param array<string> $methods Tablica dozwolonych metod HTTP (np. ['GET', 'POST'])
     */
    public function __construct(
        public array $methods
    ) {
        // Normalizacja do uppercase
        $this->methods = array_map('strtoupper', $this->methods);
    }
}
