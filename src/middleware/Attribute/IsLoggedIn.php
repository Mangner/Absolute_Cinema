<?php

namespace Middleware\Attribute;

use Attribute;

/**
 * Atrybut oznaczający, że metoda wymaga zalogowanego użytkownika.
 * 
 * Przykład użycia:
 * #[IsLoggedIn]
 * public function dashboard() { ... }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class IsLoggedIn
{
    /**
     * @param bool $redirectOnFail Czy przekierować do logowania (true) czy zwrócić JSON 401 (false)
     */
    public function __construct(
        public bool $redirectOnFail = true
    ) {}
}
