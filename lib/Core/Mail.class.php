<?

class Core_Mail
{

	protected $_mail;

	protected $_transport;

	protected $_transportConfig;

	public function __construct ($transportType = 'sendmail', $transportConfig = null)
	{

		$this->_transportConfig = $transportConfig;
		
		$this->_setTransport($transportType);
		
		$this->_mail = new Zend_Mail();
	
	}

	protected function _setTransport ($type = 'sendmail')
	{

		switch ($type)
		{
			case 'sendmail':
				$this->_transport = new Zend_Mail_Transport_Sendmail($this->_transportConfig);
				break;
			
			case 'smtp':
				$config = $this->_transportConfig;
				$server = array_shift($config);
				$config = array_shift($config);
				
				$this->_transport = new Zend_Mail_Transport_Smtp($server, $config);
				break;
			
			default:
				throw new Exception('Mail Transport Type not supported!');
				exit();
				break;
		}
	
	}

	public function send ($to, $from, $subject, $bodyText, $bodyHTML = null)
	{

		$this->_mail->setSubject($subject);
		$this->_mail->setBodyText($bodyText);
		
		if (! is_null($bodyHTML))
		{
			$this->_mail->setBodyHtml($bodyHTML);
		}
		
		if (is_array($from))
		{
			if (isset($from['email']) && isset($from['name']))
			{
				// get the first 2 elements and set the first as email and second as name
				$this->_mail->setFrom($from['email'], $from['name']);
			}
			else
			{
				$this->_mail->setFrom(array_shift($from));
			}
		}
		else
		{
			$this->_mail->setFrom($from);
		}
		
		if (is_array($to))
		{
			if (isset($to['email']) && isset($to['name']))
			{
				$this->_mail->addTo($to['email'], $to['name']);
			}
			else
			{
				foreach ($to as $data)
				{
					if (isset($data['email']) && isset($data['name']))
					{
						$this->_mail->addTo($data['email'], $data['name']);
					}
					else
					{
						$this->_mail->addTo(array_shift($data));
					}
				}
			}
		}
		else
		{
			$this->_mail->addTo($to);
		}
		
		return $this->_mail->send($this->_transport);
	
	}

}