<?
// Initial Registration Key
if (!defined('KEY_TEMP'))
{
	define('KEY_TEMP', '94a6654ce2159967720c136776795171');
}

class Core_Hasher
{
	
	public $_key;

	public function __construct ($key = null)
	{
		if (is_null($key))
		{
			$this->_key = KEY_TEMP;
		}
		else
		{
			$this->_key = $key;
		}
	}
	
	public function setKey($key)
	{
		$this->_key = $key;
	}
	
	public function encrypt($plaintext)
	{
	    $key = pack('H*', $this->_key);
		
		$key_size =  strlen($key);
		
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);

		$ciphertext = $iv . $ciphertext;
		
		$ciphertext_base64 = base64_encode($ciphertext);

		return $ciphertext_base64;

	}
	
	public function decrypt($ciphertext)
	{
	    $key = pack('H*', $this->_key);

		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		
		$ciphertext_dec = base64_decode($ciphertext);
		
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		
		$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
		
		return trim($plaintext_dec);
	}
	
	public function generateRandomKey()
	{
		return md5(microtime(true));
	}

	public function __destruct ()
	{
		unset($this->_key);
	}

}