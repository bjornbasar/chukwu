<?
// Token Required Fields
// consumerKey
// consumerSecret
// ip
// type (access, temp, request)

/**
 * Authentication process used by the REST Server
 *
 * @author Sheldon Senseng
 *        
 */
define('RESTAUTH_NONCE_LIMIT', 86400);

class Core_RestAuth
{

	protected $_db;

	protected $_accessToken;

	protected $_tempToken;

	protected $_requestToken;

	protected $_consumerKey;

	protected $_consumerSecret;

	protected $_callbackUrl;

	protected $_redirectUrl;

	protected $_type;

	protected $_id;

	public function __construct ()
	{

		$this->_getConsumerAuth();
	
	}

	protected function _getConsumerAuth ()
	{
		// $this->_db = new Core_Sqlite( 'oauth' );
		$this->_consumerKey = $_SERVER['HTTP_CONSUMERKEY'];
		$this->_consumerSecret = $_SERVER['HTTP_CONSUMERSECRET'];
		$this->_accessToken = @$_SERVER['HTTP_ACCESSTOKEN'];
		$this->_requestToken = @$_SERVER['HTTP_REQUESTTOKEN'];
		$this->_callbackUrl = @$_SERVER['HTTP_CALLBACKURL'];
		$this->_type = @$_SERVER['HTTP_TYPE'];
		$this->_id = @$_SERVER['HTTP_ID'];
		$this->_redirectUrl = @$_SERVER['HTTP_REDIRECTURL'];
	
	}

	public function getHeaders ($data = array())
	{

		$headers = array('key' => $this->_consumerKey, 'secret' => $this->_consumerSecret, 'access' => $this->_accessToken, 'request' => $this->_requestToken, 'callbackurl' => $this->_callbackUrl, 'redirecturl' => $this->_redirectUrl, 'type' => $this->_type, 'id' => $this->_id);
		
		if (isset($data) && is_array($data))
		{
			foreach ($headers as $key => $value)
			{
				if (! in_array($key, $data))
				{
					unset($headers[$key]);
				}
			}
		}
		return $headers;
	
	}

	public function verifyConsumerAuth ($key = null, $secret = null)
	{

		if (is_null($key))
		{
			$key = $this->_consumerKey;
		}
		if (is_null($secret))
		{
			$secret = $this->_consumerSecret;
		}
		
		$query = "select * from restauthconsumers where key = ? and secret = ?";
		$result = $this->_db->getRow($query, array($key, $secret));
		
		if (count($result) < 1)
		{
			return false;
		}
		
		return true;
	
	}

	public function generateToken ($type, $data)
	{
		// add nonce and type
		$data['RESTAUTH_TYPE'] = $type;
		$data['RESTAUTH_NONCE'] = time();
		
		$hash = Core_Helper::generateHash(serialize($data), ENCRYPTION_SECRET);
		
		$data['RESTAUTH_HASH'] = $hash;
		
		$token = Core_Helper::encrypt(serialize($data));
		
		return $token;
	
	}

	public function verifyToken ($type, $token)
	{

		$data = unserialize(Core_Helper::decrypt($token));
		
		// verify hash
		if (! isset($data['RESTAUTH_HASH']))
		{
			return false;
		}
		
		$hash = $data['RESTAUTH_HASH'];
		
		unset($data['RESTAUTH_HASH']);
		
		if ($hash != Core_Helper::generateHash(serialize($data), ENCRYPTION_SECRET))
		{
			return false;
		}
		
		// verify nonce
		if (! isset($data['RESTAUTH_NONCE']))
		{
			return false;
		}
		
		$nonce = $data['RESTAUTH_NONCE'];
		
		if ((time() - $nonce) > RESTAUTH_NONCE_LIMIT)
		{
			return false;
		}
		
		// verify type
		if (! isset($data['RESTAUTH_TYPE']))
		{
			return false;
		}
		
		if ($data['RESTAUTH_TYPE'] != $type)
		{
			return false;
		}
		
		return true;
	
	}

}