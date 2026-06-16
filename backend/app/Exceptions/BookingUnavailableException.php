<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown inside a booking transaction when a locked, authoritative
 * availability re-check fails, so the transaction rolls back and the
 * caller can return a clean "no availability" response.
 */
class BookingUnavailableException extends RuntimeException {}
