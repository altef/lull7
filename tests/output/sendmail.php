<?php


if (requiredInput(['email'], "You must supply an email address to send to.")) {
	$mail = new \altef\output\email\Sendmail($config['email_template_directory']);

	p("Sending welcome email");
	$mail->send($email, $config['email']['from'], $config['email']['subjects']['welcome'], 'welcome', ['url'=>'no-url']);

	p("Sending forgotten password email");
	$mail->send($email, $config['email']['from'], $config['email']['subjects']['forgot'], 'forgot', ['url'=>'no-url']);
	
	p("(Check your email)");
}