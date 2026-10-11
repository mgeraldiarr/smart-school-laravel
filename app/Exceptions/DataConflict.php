<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Data yang dikirim valid secara format, tetapi bertentangan dengan data
 * yang sudah ada (misalnya email sudah dipakai akun lain).
 */
class DataConflict extends RuntimeException {}
