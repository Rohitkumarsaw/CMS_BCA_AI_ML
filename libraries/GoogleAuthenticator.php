<?php

class GoogleAuthenticator
{
    private static $codeLength = 6;

    public static function generateSecret($length = 16)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secret = self::base32Decode($secret);
        $timeSlice = pack('N', $timeSlice);
        $timeSlice = str_pad($timeSlice, 8, chr(0), STR_PAD_LEFT);
        $hash = hash_hmac('sha1', $timeSlice, $secret, true);
        $offset = ord($hash[19]) & 0x0f;
        $code = (
                (ord($hash[$offset]) & 0x7f) << 24 |
                (ord($hash[$offset + 1]) & 0xff) << 16 |
                (ord($hash[$offset + 2]) & 0xff) << 8 |
                (ord($hash[$offset + 3]) & 0xff)
            ) % pow(10, self::$codeLength);
        return str_pad($code, self::$codeLength, '0', STR_PAD_LEFT);
    }

    public static function verify($code, $secret, $discrepancy = 1)
    {
        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    public static function getQRCodeUrl($label, $secret, $issuer = '')
    {
        $url = 'otpauth://totp/' . rawurlencode($label);
        $params = ['secret' => $secret];
        if ($issuer) {
            $params['issuer'] = $issuer;
        }
        $url .= '?' . http_build_query($params, '', '&');
        return $url;
    }

    private static function base32Decode($data)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper($data);
        $data = str_replace('=', '', $data);
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $val = strpos($chars, $data[$i]);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }
        return $output;
    }
}
