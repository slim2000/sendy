$(document).ready(
	function()
	{
		$('#thankyou_message, #goodbye_message, #confirmation_email').redactor({
			autoresize: true,
			imageUpload: 'includes/create/upload.php',
			convertDivs: false,
			convertLinks: false,
			overlay: false,
			minHeight: 200,
			cleanup: false,
			iframe: true,
			buttons: ['html', '|', 'bold', 'italic', 'deleted', 'formatting', '|', 'link', 'image', 'file', 'table', '|', 'fontcolor', 'backcolor', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', 'alignment', '|', 'horizontalrule']
		});
	}
);