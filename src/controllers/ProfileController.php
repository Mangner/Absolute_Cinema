<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/ProfileRepository.php';

class ProfileController extends AppController
{
    private ProfileRepository $profileRepository;

    public function __construct()
    {
        $this->profileRepository = new ProfileRepository();
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'user';

        $userData = $this->profileRepository->getUserById($userId);

        $tickets = $this->profileRepository->getUserTickets($userId);

        $stats = $this->profileRepository->getUserStats($userId);

        $this->render('profile', [
            'user' => $userData,
            'tickets' => $tickets,
            'stats' => $stats,
            'role' => $userRole,
            'isAdmin' => $userRole === 'admin'
        ]);
    }
}
