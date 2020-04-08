<?php
namespace altef\output\email;

/**
 * Use PHP's internal mail function.
 */
class Sendmail {
	private $template_dir;

	public function __construct($template_directory) {
		$this->template_dir = $template_directory;
	}
	
	public function  send($to, $from, $subject, $template, $data) {
		$template_data = $this->loadTemplate($template . '.html');
		$message = $this->inject($data, $template_data);
		$message = wordwrap($message, 70, "\r\n");
		$headers = "From: $from\r\n";
		$headers .= "Reply-To: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		return mail($to, $subject, $message, $headers);
	}
	
	private function loadTemplate($template) {
		if (is_file($this->template_dir . $template))
			return file_get_contents($this->template_dir . $template);
		throw new \Exception("Template not found: $template");
	}
	
	protected function inject($data, $template) {
		foreach($data as $key=>$value) {
			$template = str_replace('*|'.$key.'|*', $value, $template);
		}
		return $template;
	}
}




?>