<?php
/**
 * Email Service Component class for Amazon SES
 *
 * Send e-mails with Amazon Simple Email Service
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * Based on original class from: http://bakery.cakephp.org/articles/dankroad/2011/01/30/integrate_amazon_simple_email_service_ses_into_existing_application
 *
 * Usage:
 * 
 * Load EmailService in your controller components
 * var $components = array('EmailService');
 *
 * In your actions choose aws_ses as delivery method
 * $this->EmailService->delivery = 'aws_ses'; // or 'aws_ses_raw' if you want complex e-mail with attachments
 *
 * @author Lucas Ferreira
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @copyright Copyright 2011, Burn web.studio - http://www.burnweb.com.br/
 * @version 1.1b
 */

App::import('Vendor', 'aws-sdk', array('file' => 'sdk.class.php')); 
App::import('Component', 'Email'); 

class EmailServiceComponent extends EmailComponent { 
    
	var $ses_options = array();
	var $response = null;

	function getAmazonSES()
	{
		$ses = new AmazonSES();
		return $ses;
	}
	
	function _aws_ses_raw()
	{
		$ses = $this->getAmazonSES();
		
		// remove unnecessary headers...
		$uhs[] = '0: This part of the E-mail should never be seen. If';
		$uhs[] = '1: you are reading this, consider upgrading your e-mail';
		$uhs[] = '2: client to a MIME-compatible client.';
		foreach($uhs as $uh)
		{
			if(($p=array_search($uh, $this->__header)))
			{
				unset($this->__header[$p]);
			}
		}
		
		if(!is_array($this->to)) $this->to = array($this->to);
		$this->__header[] = 'To: ' . implode(', ', array_map(array($this, '_formatAddress'), $this->to));
		
		$this->__header[] = 'Subject: ' . $this->_encode($this->subject);
		
		$header = implode("\r\n", $this->__header);
		$message = implode("\r\n", $this->__message);
		$data = base64_encode($header . "\r\n\r\n" . $message . "\r\n\r\n\r\n.");
		
		$options = array_merge($this->ses_options, array( 
			'Source' => $this->from 
		));
		
		$this->response = $ses->send_raw_email(array('Data' => $data), $options); 
		
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error sending raw email from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $ok;
	}

	function _aws_ses()
	{
		$ses = $this->getAmazonSES();
		
		$destination = array( 
			'ToAddresses' => explode(',', $this->to) 
		);
		if($this->cc && !empty($this->cc))
		{
			$destination = array_merge($destination, array( 
				'CcAddresses' => $this->cc
			));
		}
		if($this->bcc && !empty($this->bcc))
		{
			$destination = array_merge($destination, array( 
				'BccAddresses' => $this->bcc
			));
		}	
		
		$options = $this->ses_options;
		if($this->replyTo && !empty($this->replyTo))
		{
			$options = array_merge($options, array( 
				'ReplyToAddresses' => explode(',', $this->replyTo)
			));
		}
			
		$message = array( 
			'Subject' => array( 
				'Data' => $this->subject,
				'Charset' => $this->charset
			), 
			'Body' => array() 
		); 
		if($this->textMessage != null)
		{ 
			$message['Body']['Text'] = array( 
				'Data' => $this->textMessage,
				'Charset' => $this->charset 
			); 
		} 
		if($this->htmlMessage != null)
		{ 
			$message['Body']['Html'] = array( 
				'Data' => $this->htmlMessage,
				'Charset' => $this->charset 
			); 
		} 
		
		$this->response = $ses->send_email($this->from, $destination, $message, $options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error sending email from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $ok; 
	}
	
	function verifyEmailAddress($email)
	{
		$ses = $this->getAmazonSES();
		
		$this->response = $ses->verify_email_address($email, $this->ses_options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error verify email address from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $ok;
	}
	
	function deleteVerifiedEmailAddress($email)
	{
		$ses = $this->getAmazonSES();
		
		$this->response = $ses->delete_verified_email_address($email, $this->ses_options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error delete_verified_email_address from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $ok;
	}
	
	function listVerifiedEmailAddresses()
	{
		$ses = $this->getAmazonSES();
		
		$this->response = $ses->list_verified_email_addresses($this->ses_options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error list_verified_email_addresses from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $this->response->body;
	}
	
	function getSendQuota()
	{
		$ses = $this->getAmazonSES();
		
		$this->response = $ses->get_send_quota($this->ses_options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error get send quota from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $this->response->body;
	}
	
	function getSendStatistics()
	{
		$ses = $this->getAmazonSES();
		
		$this->response = $ses->get_send_statistics($this->ses_options); 
		$ok = $this->response->isOK(); 
		if(!$ok)
		{ 
			$this->log('Error get send statistics from AWS SES: ' . $this->response->body->asXML(), 'debug'); 
		}
		
		return $this->response->body;
	}
    
}
?>