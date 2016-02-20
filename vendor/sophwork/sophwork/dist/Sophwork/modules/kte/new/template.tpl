<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title> Title </title>
		<meta name="description" content="">
		<link rel="stylesheet" href="">
	</head>
	<body>
		<header>
			<nav>
			</nav>
		</header>
		<section>
			<?php foreach($items as $key => $value): ?>
				<a href="<?php KTE::e($value->url)?>"><?php KTE::e($value->caption)?></a>
			<?php endforeach; ?>
		</section>
		<aside>
		</aside>
		<footer>
		</footer>
	</body>
</html>