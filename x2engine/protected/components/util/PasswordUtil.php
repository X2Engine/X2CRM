<?php

/*
 * Password Hashing With PBKDF2 (http://crackstation.net/hashing-security.htm).
 * Copyright (c) 2013, Taylor Hornby
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, 
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation 
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

class PasswordUtil {

    // These constants may be changed without breaking existing hashes.
    CONST PBKDF2_HASH_ALGORITHM = "sha256";
    CONST PBKDF2_ITERATIONS = 32768;
    CONST PBKDF2_SALT_BYTES = 24;
    CONST PBKDF2_HASH_BYTES = 24;
    //Hash format information, changing will break old hashes
    CONST HASH_SECTIONS = 4;
    CONST HASH_ALGORITHM_INDEX = 0;
    CONST HASH_ITERATION_INDEX = 1;
    CONST HASH_SALT_INDEX = 2;
    CONST HASH_PBKDF2_INDEX = 3;

    public static function createHash($password) {
        // format: algorithm:iterations:salt:hash
        $salt = self::createSalt();
        return self::PBKDF2_HASH_ALGORITHM . ":" . self::PBKDF2_ITERATIONS . ":" . $salt . ":" .
                base64_encode(self::pbkdf2(
                                self::PBKDF2_HASH_ALGORITHM, $password, $salt, self::PBKDF2_ITERATIONS, self::PBKDF2_HASH_BYTES, true
        ));
    }

    public static function validatePassword($password, $good_hash) {
        $params = explode(":", $good_hash);
        if (count($params) < self::HASH_SECTIONS){
            return false;
        }
        $pbkdf2 = base64_decode($params[self::HASH_PBKDF2_INDEX]);
        return self::slowEquals(
                        $pbkdf2, self::pbkdf2(
                                $params[self::HASH_ALGORITHM_INDEX], $password, $params[self::HASH_SALT_INDEX], (int) $params[self::HASH_ITERATION_INDEX], strlen($pbkdf2), true
                        )
        );
    }

    // Compares two strings $a and $b in length-constant time.
    public static function slowEquals($a, $b) {
        if(!is_string($a) || !is_string($b)){
            return false;
        }
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }
    
    /*
     * Based on Scott's answer: http://stackoverflow.com/a/13733588/179104
     * $length = length of pretty random, pretty unique, alphanumeric string. 
     *
     * Other cited sources: http://us1.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
     * 
     * Alternative extensions/libraries for more options based on the article: 
     * https://paragonie.com/blog/2015/07/how-safely-generate-random-strings-and-integers-in-php
     * - https://github.com/paragonie/random_compat
     * - https://github.com/ircmaxell/RandomLib
     * - https://pecl.php.net/package/libsodium
     */
    public static function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public static function getToken($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited
        
        for ($i=0; $i < $length; $i++) {
            
            $token .= $codeAlphabet[PasswordUtil::crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */

    public static function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true)) {
            throw new CException('PBKDF2 ERROR: Invalid hash algorithm.', 500);
        }
        if ($count <= 0 || $key_length <= 0) {
            throw new CException('PBKDF2 ERROR: Invalid parameters.', 500);
        }
        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);
        $output = "";
        for ($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }
        if ($raw_output) {
            return substr($output, 0, $key_length);
        } else {
            return bin2hex(substr($output, 0, $key_length));
        }
    }
    
    public static function createSalt() {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0 && function_exists('random_bytes')) {
            return base64_encode(random_bytes(self::PBKDF2_SALT_BYTES));
        } elseif (function_exists('mcrypt_create_iv')) {
            return base64_encode(mcrypt_create_iv(self::PBKDF2_SALT_BYTES,
                            MCRYPT_DEV_URANDOM));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $random = openssl_random_pseudo_bytes(self::PBKDF2_SALT_BYTES, $strong);
            if ($strong === true) {
                return base64_encode($random);
            }
        }
        $sha =''; 
        $random ='';
        if(file_exists('/dev/urandom')){
            $fp = @fopen('/dev/urandom', 'rb');
            if($fp){
                if (function_exists('stream_set_read_buffer')) {
                    stream_set_read_buffer($fp, 0);
                }
                $sha = fread($fp, self::PBKDF2_SALT_BYTES);
                fclose($fp);
            }
        }
        for ($i = 0; $i < self::PBKDF2_SALT_BYTES; $i++) {
            $sha = hash('sha256', $sha . mt_rand());
            $char = mt_rand(0, 62);
            $random .= chr(hexdec($sha[$char] . $sha[$char + 1]));
        }
        return base64_encode($random);
    }

}
