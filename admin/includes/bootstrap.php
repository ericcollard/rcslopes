<?php
/**
 * À inclure en TOUT DÉBUT de chaque script de page (avant tout output HTML).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/TableRegistry.php';
require_once __DIR__ . '/CrudEngine.php';
require_once __DIR__ . '/FormProcessor.php';
require_once __DIR__ . '/HtmlSanitizer.php';
require_once __DIR__ . '/ImageManager.php';
require_once __DIR__ . '/AdminManager.php';
require_once __DIR__ . '/helpers.php';

Auth::startSession();
