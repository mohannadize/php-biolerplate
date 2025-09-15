<?php

function isCurrentRoute(string $route, bool $match_strictly = false)
{
	$pathname = $_SERVER["REQUEST_URI"];
	if ($match_strictly) {
		return $pathname === $route;
	}
	return str_starts_with($pathname, $route);
}

function activeSidenavLink(string $route, bool $match_strictly = false)
{
	return isCurrentRoute($route, $match_strictly) ? "bg-white/80 text-black hover:bg-white/70!" : "";
}

class AdminLayout extends HTML
{
	public function __destruct()
	{
		$output = ob_get_clean();

		ob_start();
?>
		<div class="min-h-screen relative flex">
			<aside class="pb-2 pt-4 px-2 w-[300px] sticky top-0 max-w-full h-screen bg-gray-800 text-white flex flex-col">
				<h1 class="text-lg font-bold text-center">Admin Dashboard</h1>
				<div class="flex flex-col mt-4 [&>*]:hover:bg-white/10 [&>*]:rounded-sm [&>*]:p-2 [&>*]:transition-colors [&_i]:pe-8 [&_i]:ps-2 text-white/70 grow-1 text-sm">
					<a class="<?= activeSidenavLink('/admin/', true) ?>" href="/admin/"><i class="fas fa-dashboard"></i>Dashboard</a>
					<a class="<?= activeSidenavLink('/admin/settings', true) ?>" href="/admin/settings"><i class="fa fa-cog"></i>Settings</a>
					<a href="/admin/logout"><i class="fa fa-right-from-bracket"></i>Logout</a>
				</div>
				<a href="/" class="px-4 py-2 border-1 border-white/30 hover:border-white hover:bg-white/90 hover:text-black w-full text-center text-sm rounded-md transition-colors"><i class="far fa-home pe-6"></i>Back to website</a>
			</aside>
			<div class="p-4 h-[200vh] w-full">
				<?= $output; ?>
			</div>
		</div>
<?php
		echo (ob_get_clean());
		parent::__destruct();
	}
}
