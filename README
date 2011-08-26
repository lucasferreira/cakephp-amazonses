# EmailServiceComponent

CakePHP E-mail Component for Amazon SES

Based on original class from:
http://bakery.cakephp.org/articles/dankroad/2011/01/30/integrate_amazon_simple_email_service_ses_into_existing_application


## Requisites:

Download and unzip Amazon PHP SDK in your app/vendors folder. Rename de SDK folder to aws-sdk.

Rename the config-sample.inc.php file to config.inc.php and configure with your credentials.


## Usage:

Load EmailService in your controller components

	var $components = array('EmailService');


In your action choose aws_ses as delivery option

	$this->EmailService->delivery = 'aws_ses'; // or 'aws_ses_raw' if you want complex e-mail with attachments


[]'s.
by @lucasferreira