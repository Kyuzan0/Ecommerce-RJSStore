<?php
/**
 * Bridge entry point for shared hosting (e.g. InfinityFree).
 *
 * On hosts where the document root is the project root (not public/),
 * this file forwards every request to the real front controller.
 */
require_once __DIR__ . '/public/index.php';
