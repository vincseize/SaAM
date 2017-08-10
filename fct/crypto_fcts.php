<?php
define('CRYPT_CBIT_CHECK', 32);


function encrypt($text) {
    $text_num = str_split($text, CRYPT_CBIT_CHECK);
    $text_num = CRYPT_CBIT_CHECK - strlen($text_num[count($text_num)-1]);

    for ($i=0;$i<$text_num; $i++)
        $text = $text . chr($text_num);

    $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');
    mcrypt_generic_init($cipher, CRYPT_CKEY, CRYPT_CIV);

    $decrypted = mcrypt_generic($cipher, $text);
    mcrypt_generic_deinit($cipher);

    return base64_encode($decrypted);
}


function decrypt($encrypted_text) {
    $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', 'cbc', '');
    mcrypt_generic_init($cipher, CRYPT_CKEY, CRYPT_CIV);

    $decrypted = mdecrypt_generic($cipher, base64_decode($encrypted_text));
    mcrypt_generic_deinit($cipher);

    $last_char = substr($decrypted,-1);

    for($i=0; $i<(CRYPT_CBIT_CHECK-1); $i++) {
        if(chr($i) == $last_char) {
            $decrypted = substr($decrypted, 0, strlen($decrypted)-$i);
            break;
        }
    }
    return $decrypted;
}
?>
