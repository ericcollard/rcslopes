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
