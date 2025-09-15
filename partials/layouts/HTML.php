<?php
class HTML
{
	public function __construct(public string $title, public string $lang = 'en')
	{
		ob_start();
	}

	public function __destruct()
	{
		$output = ob_get_clean();

		ob_start();
?>

		<!DOCTYPE html>
		<html lang="<?= $this->lang; ?>">

		<head>
			<meta charset="UTF-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<base href="%BASE%/">

			<title><?= $this->title; ?></title>
			<meta name="description" content="">
			<link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&display=swap" rel="stylesheet" />

			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&family=Geist:wght@100..900&display=swap" rel="stylesheet">

			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

			<link href="/src/styles/tailwind.css" rel="stylesheet" />
			<link href="/src/styles/global.scss" rel="stylesheet" />

			<script src="https://cdnjs.cloudflare.com/ajax/libs/htmx/2.0.6/htmx.min.js" integrity="sha512-fzOjdYXF0WrjlPAGWmlpHv2PnJ1m7yP8QdWj1ORoM7Bc4xmKcDRBOXSOZ4Wedia0mjtGzXQX1f1Ah1HDHAWywg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
			<script defer src="/js/signature_pad.umd.min.js"></script>
		</head>

		<body hx-boost="true" class="flex flex-col min-h-screen">
			<?= $output; ?>
		</body>

		</html>

<?php
		die(ob_get_clean());
	}
}
