<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MagentoCloud;

/**
 * Contains logic for generating random strings and hashes as used for passwords
 */
class Password
{
    /**
     * Generates admin password using default Magento settings
     * @param int $length the length of the random string
     * @return string
     */
    public static function generateRandomString(int $length) : string
    {
        $charsLowers = "abcdefghijklmnopqrstuvwxyz";
        $charsUppers = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charsDigits = "0123456789";
        $chars = $charsLowers . $charsUppers . $charsDigits;
        $output = "";
        $lc = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = random_int(0, $lc);
            $output .= $chars[$rand]; // random character in $chars
        }
        return $output;
    }

    /**
     * Generates salt and hash for the admin password using default Magento settings
     * @param string $password The password we will generate a hash of
     * @return string The hash + salt + version
     */
    public static function generatePassword(string $password) : string
    {
        $saltLength = 32;
        $salt = static::generateRandomString($saltLength);
        $version = 1;
        $hash = hash('sha256', $salt . $password);
        return implode(
            ':',
            [
                $hash,
                $salt,
                $version
            ]
        );
    }
}
