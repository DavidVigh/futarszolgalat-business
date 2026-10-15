<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule from Futarszolgalat_Business_Logic.pdf was violated.
 * Rendered as a 422 JSON response in bootstrap/app.php.
 */
class UzletiSzabalyException extends RuntimeException {}
