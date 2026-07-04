<?php
// ============================================================
// controllers/SlopeController.php  –  Contrôleur Pentes
// ============================================================

namespace controllers;
use models\Comment;
use function jsonResponse;

require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../helpers/response.php';

class CommentController
{

    // ── POST /api/slopes ──────────────────────────────────────

    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // N'accepter que la méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false,'errors'  => ['Méthode non autorisée.']], 405);
            exit;
        }

        // Lecture du corps JSON envoyé par fetch()
        $rawInput = file_get_contents('php://input');
        $input    = json_decode($rawInput, true);

        if (!is_array($input)) {
            jsonResponse(['success' => false,'errors'  => ['Requête invalide.']], 400);
            exit;
        }


// ============================================================
// 1) VÉRIFICATION DU TOKEN CSRF
// ============================================================
        $submittedToken = $input['csrf_token'] ?? '';

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            http_response_code(419); // "Page Expired" (convention courante pour CSRF invalide)
            echo json_encode([
                'success' => false,
                'errors'  => ['Session expirée, veuillez recharger la page et réessayer.']
            ]);
            exit;
        }

// ============================================================
// 2) HONEYPOT — un bot remplit généralement tous les champs
// ============================================================
        if (!empty($input['website'])) {
            // On répond "succès" pour ne pas indiquer au bot qu'il a été détecté,
            // mais on n'insère rien en base.
            http_response_code(201);
            echo json_encode(['success' => true]);
            exit;
        }

// ============================================================
// 3) DÉLAI MINIMUM DE SOUMISSION — un bot soumet quasi instantanément
// ============================================================
        $renderedAt = (int) ($input['form_rendered_at'] ?? 0);
        $elapsed    = time() - $renderedAt;

        if ($renderedAt <= 0 || $elapsed < 2) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'errors'  => ['Soumission trop rapide, veuillez réessayer.']
            ]);
            exit;
        }

// ============================================================
// 4) RATE LIMITING SIMPLE PAR SESSION (ex : 5 commentaires / 10 min)
// ============================================================
        $now            = time();
        $window         = 600; // 10 minutes
        $maxSubmissions = 5;

        $_SESSION['comment_submissions'] = array_filter(
            $_SESSION['comment_submissions'] ?? [],
            fn(int $timestamp) => ($now - $timestamp) < $window
        );

        if (count($_SESSION['comment_submissions']) >= $maxSubmissions) {
            http_response_code(429); // Too Many Requests
            echo json_encode([
                'success' => false,
                'errors'  => ['Trop de commentaires envoyés récemment, veuillez patienter avant de réessayer.']
            ]);
            exit;
        }


        $errors = $this->validateRequired($input);
        if (empty($errors)) $input = $this->sanitize($input);

        if (!empty($errors)) {
            jsonResponse(['success' => false,'errors'  => $errors], 422);
            exit;
        }

        try {
            $commentId = comment::insert($input['comment'], $input['email'], $input['slopeId']);
            jsonResponse(['success' => true,'id'  => $commentId], 201);


        } catch (PDOException $e) {
            // Ne jamais renvoyer le message d'erreur SQL brut au client en production
            error_log('Erreur insertion commentaire : ' . $e->getMessage());
            jsonResponse(['success' => false,'errors'  => ['Une erreur serveur est survenue, veuillez réessayer plus tard.']], 500);
        }
    }

    // ── Validation ────────────────────────────────────────────

    private function validateRequired(array $body): array
    {
        $errors = [];

        if (empty($body['email'])) {
            $errors[] = 'Le champ "email" est obligatoire.';
        }
        else
        {
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email n'est pas valide.";
            }
        }

        if (empty($body['comment'])) {
            $errors[] = 'Le champ "comment" est obligatoire.';
        }
        if (!isset($body['slopeId']) || !is_numeric($body['slopeId'])) {
            $errors[] = 'Le champ "slopeId" doit être un nombre.';
        }

        return $errors;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function sanitize(array $body): array
    {
        $data = [];

        if (isset($body['email'])) {
            $data['email'] = trim(strip_tags($body['email']));
        }
        if (array_key_exists('comment', $body)) {
            // On enleve tous les tags, et on limite à 1000 caractères
            $data['comment'] = substr(trim(strip_tags($body['comment'])),0,1000);
        }
        if (isset($body['slopeId'])) {
            $data['slopeId'] = (int)$body['slopeId'];
        }

        return $data;
    }

}
