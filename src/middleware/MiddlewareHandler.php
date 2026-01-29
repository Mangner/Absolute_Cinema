<?php

require_once __DIR__ . '/Attribute/AllowedMethods.php';
require_once __DIR__ . '/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;

/**
 * MiddlewareHandler - Obsługa atrybutów (adnotacji) PHP 8 dla kontrolerów.
 * 
 * Weryfikuje:
 * - Dozwolone metody HTTP (#[AllowedMethods])
 * - Wymóg zalogowania (#[IsLoggedIn])
 */
class MiddlewareHandler
{
    private const SESSION_TIMEOUT = 600; // 10 minut

    /**
     * Sprawdza atrybuty metody kontrolera i wykonuje odpowiednie walidacje.
     * 
     * @param object $controller Instancja kontrolera
     * @param string $action Nazwa metody do wywołania
     * @return bool True jeśli wszystkie walidacje przeszły pomyślnie
     */
    public static function handle(object $controller, string $action): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($controller, $action);
            $attributes = $reflectionMethod->getAttributes();

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();

                // Obsługa #[AllowedMethods]
                if ($attributeInstance instanceof AllowedMethods) {
                    if (!self::checkAllowedMethods($attributeInstance)) {
                        return false;
                    }
                }

                // Obsługa #[IsLoggedIn]
                if ($attributeInstance instanceof IsLoggedIn) {
                    if (!self::checkIsLoggedIn($attributeInstance)) {
                        return false;
                    }
                }
            }

            return true;

        } catch (ReflectionException $e) {
            // Metoda nie istnieje - pozwól routingowi obsłużyć błąd
            return true;
        }
    }

    /**
     * Sprawdza, czy bieżąca metoda HTTP jest dozwolona.
     */
    private static function checkAllowedMethods(AllowedMethods $attribute): bool
    {
        $currentMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        if (!in_array($currentMethod, $attribute->methods, true)) {
            self::respondMethodNotAllowed($attribute->methods);
            return false;
        }

        return true;
    }

    /**
     * Sprawdza, czy użytkownik jest zalogowany i sesja nie wygasła.
     */
    private static function checkIsLoggedIn(IsLoggedIn $attribute): bool
    {
        // Upewnij się, że sesja jest aktywna
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Sprawdź czy użytkownik jest zalogowany
        if (empty($_SESSION['user_id'])) {
            self::respondUnauthorized($attribute->redirectOnFail);
            return false;
        }

        // Sprawdź timeout sesji
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            self::respondSessionExpired($attribute->redirectOnFail);
            return false;
        }

        // Aktualizuj czas ostatniej aktywności
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Odpowiedź 405 Method Not Allowed
     */
    private static function respondMethodNotAllowed(array $allowedMethods): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));

        // Sprawdź czy to żądanie AJAX/API
        if (self::isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
                'allowed_methods' => $allowedMethods
            ]);
        } else {
            // Renderuj widok błędu HTML
            include 'public/views/405.html';
            if (!file_exists('public/views/405.html')) {
                echo '<h1>405 - Method Not Allowed</h1>';
                echo '<p>Dozwolone metody: ' . implode(', ', $allowedMethods) . '</p>';
            }
        }
    }

    /**
     * Odpowiedź 401 Unauthorized
     */
    private static function respondUnauthorized(bool $redirect): void
    {
        if (self::isApiRequest() || !$redirect) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'unauthorized',
                'message' => 'Login required'
            ]);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Odpowiedź dla wygasłej sesji
     */
    private static function respondSessionExpired(bool $redirect): void
    {
        if (self::isApiRequest() || !$redirect) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'unauthorized',
                'message' => 'Session expired. Please login again.'
            ]);
        } else {
            header('Location: /login?expired=1');
            exit();
        }
    }

    /**
     * Sprawdza czy żądanie jest typu API (AJAX/JSON)
     */
    private static function isApiRequest(): bool
    {
        // Sprawdź nagłówek Accept
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }

        // Sprawdź nagłówek X-Requested-With (AJAX)
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        // Sprawdź Content-Type żądania
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            return true;
        }

        return false;
    }
}
