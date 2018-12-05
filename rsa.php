<?php


class RsaException extends RuntimeException {
  public function __construct($message = "") {
    parent::__construct($message.": ".openssl_error_string());
  }
}

define('PRIVATE_KEY_FILE', 'rsa/private.key');
define('PUBLIC_KEY_FILE', 'rsa/public.pem');


$globalPrivateKey = FALSE;
$globalPublicKeyFileContents = FALSE;
$globalPublicKey = FALSE;

function LoadPublicKeyFile() {
  global $globalPublicKeyFileContents;

  if($globalPublicKeyFileContents === FALSE) {
    $globalPublicKeyFileContents = file_get_contents(PUBLIC_KEY_FILE);
    if($globalPublicKeyFileContents === FALSE) throw new RsaException("Failed to load public key from '".PUBLIC_KEY_FILE."'");
  }

  return $globalPublicKeyFileContents;  
}
function LoadPrivateKey() {
  global $globalPrivateKey;

  if($globalPrivateKey === FALSE) {
    $privateKeyPemString = file_get_contents(PRIVATE_KEY_FILE);
    if($privateKeyPemString === FALSE) throw new RsaException("Failed to load private key from '".PRIVATE_KEY_FILE."'");

    $globalPrivateKey = openssl_get_privatekey($privateKeyPemString);
    if($globalPrivateKey === FALSE) throw new RsaException("Failed to interpret the contents of the private key file");
  }

  return $globalPrivateKey;
}
function LoadPublicKey() {
  global $globalPublicKey;
  global $globalPublicKeyFileContents;

  if($globalPublicKey === FALSE) {
    $publicKeyFileContents = LoadPublicKeyFile();

    $globalPublicKey = openssl_get_publickey($publicKeyFileContents);
    if($globalPublicKey === FALSE) throw new RsaException("Failed to interpret the contents of the public key file");
  }

  return $globalPublicKey;
}


function PrivateKeyEncrypt($data) {
  $privateKey = LoadPrivateKey();

  $dataEncrypted = '';
  $result = openssl_private_encrypt($data, $dataEncrypted, $privateKey);
  if($result === FALSE) throw new RsaException("Failed to encrypt the data '$data'");

  return $dataEncrypted;
}

function PublicKeyDecrypt($dataEncrypted) {
  $publicKey = LoadPublicKey();
  
  $data = '';
  $result = openssl_public_decrypt($dataEncrypted, $data, $publicKey);
  if($result === FALSE) throw new RsaException("Public key decryption failed");

  return $data;
}

function QuickTest() {
  $msg = 'abcd';
  $enc = PrivateKeyEncrypt($msg);
  $dec = PublicKeyDecrypt($enc);
  echo "dec = '$dec'\n";
}

?>