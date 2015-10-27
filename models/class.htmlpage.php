<?php
	
/**
		Code for making a html page within bootstrap
**/

	
/* USAGE:

For a standard page:
	$page = new htmlPage("This is the Title");
	print $page->getStart();
	.. print html in your script ..
	print $page->getEnd();

To add a js or css to head:
	$page = new htmlPage("This is the Title");
	$page->addHead("<script src='...'></script>");
	print $page->getStart();
	.. print html in your script ..
	print $page->getEnd();

To build the entire page before rendering it:
	$page = new htmlPage("This is the Title");
	$page->addHead("<script src='...'></script>");
	$page->addBody("<div>...</div>");
	$page->addBody("<div>...</div>");
	$page->addBody("<div>...</div>");
	print $page->getEntireDocument();

*/

class htmlPage
{
	public $open;
	public $close;
	public $head_parts = array();
	public $body_parts = array();
	
	function __construct($title = Null)
	{
		// Set defaults
		$this->open = "<!DOCTYPE html>\n<html lang='en'>\n";
		
		// The head is made from an array of parts.  This sets the first part as the default
		$this->head_parts[] = '
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		' . (empty($title) ? '' : '<title>'.$title.'</title>') . '
		
      <!-- Latest compiled and minified CSS -->
		<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"-->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Optional theme -->
		<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css"-->
		<link rel="stylesheet" href="assets/css/bootstrap-theme.min.css">
		
		<link rel="stylesheet" href="assets/css/custom.css">
		
      <!-- Apple Icons - look into http://cubiq.org/add-to-home-screen -->
      <link rel="apple-touch-icon" sizes="57x57" href="assets/img/apple-icon-57x57.png" />
      <link rel="apple-touch-icon" sizes="72x72" href="assets/img/apple-icon-72x72.png" />
      <link rel="apple-touch-icon" sizes="114x114" href="assets/img/apple-icon-114x114.png" />
      <link rel="apple-touch-icon" sizes="144x144" href="assets/img/apple-icon-144x144.png" />
      <link rel="icon" type="image/png" href="assets/img/favicon-32x32.png" sizes="32x32" />
      <link rel="icon" type="image/png" href="assets/img/favicon-16x16.png" sizes="16x16" />
		
      
      
      <!-- PLACING JSCRIPT IN HEAD OUT OF SIMPLICITY - http://stackoverflow.com/questions/10994335/javascript-head-body-or-jquery -->
		<!-- Latest compiled and minified JavaScript -->
		
		<!--
      script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script
      -->
		
		<!-- Local version for development here -->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/bootstrap.js"></script>
		<script src="assets/js/jquery.validate.min.js"></script>
		
		
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="https://www.google.com/recaptcha/api.js"></script>
		';
		
		$this->close = "</html>";
		
	}
	
	// PRINT METHODS
	//--------------------------------------------------------
	// Starts html and ends with the body tag
	public function printStart() {
		print self::getStart();
	}
	
	// Dump the entire document at once
	public function printEntireDocument() {
		print self::getEntireDocument();
	}
	
	// Dump the closing body and html
	public function printEnd() {
		print self::getEnd();
	}
	
	
	// Compresses head_parts into a single head string
	public function getHead() {
		$html = "\t<head>" . implode("\n",$this->head_parts) . "\n\t</head>";
		return $html;
	}
	
	// Compresses body_parts into a single body string
	public function getBody() {
		$html = "\t<body>" . implode("\n",$this->body_parts) . "\n\t</body>";
		return $html;
	}
	
	// Add a section to the body
	public function addBody($string) {
		$this->body_parts[] = $string;
	}
	
	// Add a section to the body
	public function addHead($string) {
		$this->head_parts[] = $string;
	}
	
	// Wrap the existing body with some elements
	public function wrapBody($prefix = '', $suffix = '') {
		if (!empty($prefix)) array_unshift($this->body_parts,$prefix);
		if (!empty($suffix)) array_push($this->body_parts,$suffix);
	}
	
	// Returns an entire document
	public function getEntireDocument()
	{
		$html =	$this->open .
			self::getHead() .
			self::getBody() .
			$this->close;
		return $html;
	}
	
	// Starts a default document up to the body
	public function getStart() {
		$html = $this->open . 
			self::getHead() . 
			"\t<body>\n";
		return $html;
	}
	
	
	// Close out body and html
	public function getEnd() {
		$html = "\t</body>" . $this->close;
		return $html;
	}
}	
	
// A bootstrap panel
class bootstrapPanel {
	
	private $header;
	private $body;
	private $footer;
	private $icon;	//ok-sign, exclamation-sign
	private $type;	//primary, success, info, warning, danger
	
	function __construct($type = '')
	{
		$this->type = $type;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function setIcon($icon)
	{
		$this->icon = $icon;
	}
	
	public function setBody($html)
	{
		$this->body = $html;
	}
	
	public function setHeader($html)
	{
		$this->header = $html;
	}
	
	public function setFooter($html)
	{
		$this->footer = $html;
	}
	
	private function getHeader()
	{
		$html = '';
		if ($this->icon)
		{
			$html .= '<span class="glyphicon glyphicon-' . $this->icon . '" aria-hidden="true"></span>';
		}
		if ($this->header)
		{
			$html .= '<span class="sr-only">' . strip_tags($this->header) . '</span>' . $this->header;
		}
		if (!empty($html)) $html = '<div class="panel-heading">' . $html . '</div>';
		return $html;
	}
	
	
	private function getBody()
	{
		$html = !empty($this->body) ? '<div class="panel-body">' . $this->body . '</div>' : '';
		return $html;
	}
	
	
	private function getFooter()
	{
		$html = !empty($this->footer) ? '<div class="panel-footer">' . $this->footer . '</div>' : '';
		return $html;
	}
	
	
	public function getPanel()
	{
		$html = array();
		$html[] = '<div class="panel'. (!empty($this->type) ? ' panel-'.$this->type : '') . '">';
		$html[] = self::getHeader();
		$html[] = self::getBody();
		$html[] = self::getFooter();
		$html[] = '</div>';
		return implode('',$html);
	}
	
}
	
	
?>