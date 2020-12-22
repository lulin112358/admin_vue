<?php


namespace rsa;


class Rsa
{
    /**
     * 签名
     * @param $data
     * @return string
     */
    public static function sign($data) {
        $rsa = new \phpseclib\Crypt\RSA();
        $pub_key = file_get_contents(__DIR__."/key/pub.key");
        $rsa->loadKey($pub_key);
        $rsa->setSignatureMode(\phpseclib\Crypt\RSA::SIGNATURE_PKCS1);
        $plaintext = self::sort($data);
        $signature = $rsa->sign($plaintext);
        return base64_encode($signature);
    }

    /**
     * 验签
     * @param $msg
     * @param $signature
     * @return bool|string
     */
    public static function verify($msg, $signature) {
        $rsa = new \phpseclib\Crypt\RSA();
        $pri_key = file_get_contents(__DIR__."/key/pri.key");
        $rsa->loadKey($pri_key);
        $rsa->setSignatureMode(\phpseclib\Crypt\RSA::SIGNATURE_PKCS1);
        $plaintext = self::sort($msg);
        return $rsa->verify($plaintext, base64_decode($signature));
    }

    /**
     * 参数排序
     * @param $data
     * @return string
     */
    public static function sort($data) {
        # 去空
        $data = array_filter($data);
        # 参数排序
        ksort($data);
        $plaintext = http_build_query($data);
        $plaintext = urldecode($plaintext);
        return $plaintext;
    }
}
