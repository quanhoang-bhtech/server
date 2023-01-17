<?php

/**
 * @var \OC_Defaults $theme
 * @var array $_
 */

$getUserAvatar = static function (int $size) use ($_): string {
	return \OC::$server->getURLGenerator()->linkToRoute('core.avatar.getAvatar', [
		'userId' => $_['user_uid'],
		'size' => $size,
		'v' => $_['userAvatarVersion']
	]);
}

?>
<!DOCTYPE html>
<html class="ng-csp" data-placeholder-focus="false" lang="<?php p($_['language']); ?>" data-locale="<?php p($_['locale']); ?>">

<head data-user="<?php p($_['user_uid']); ?>" data-user-displayname="<?php p($_['user_displayname']); ?>" data-requesttoken="<?php p($_['requesttoken']); ?>">
	<meta charset="utf-8">
	<title>
		<?php
		p(!empty($_['application']) ? $_['application'] . ' - ' : '');
		p($theme->getTitle());
		?>
	</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
	<?php if ($theme->getiTunesAppId() !== '') { ?>
		<meta name="apple-itunes-app" content="app-id=<?php p($theme->getiTunesAppId()); ?>">
	<?php } ?>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-title" content="<?php p((!empty($_['application']) && $_['appid'] != 'files') ? $_['application'] : $theme->getTitle()); ?>">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="theme-color" content="<?php p($theme->getColorPrimary()); ?>">
	<link rel="icon" href="<?php print_unescaped(image_path($_['appid'], 'favicon.ico')); /* IE11+ supports png */ ?>">
	<link rel="apple-touch-icon" href="<?php print_unescaped(image_path($_['appid'], 'favicon-touch.png')); ?>">
	<link rel="apple-touch-icon-precomposed" href="<?php print_unescaped(image_path($_['appid'], 'favicon-touch.png')); ?>">
	<link rel="mask-icon" sizes="any" href="<?php print_unescaped(image_path($_['appid'], 'favicon-mask.svg')); ?>" color="<?php p($theme->getColorPrimary()); ?>">
	<link rel="manifest" href="<?php print_unescaped(image_path($_['appid'], 'manifest.json')); ?>">
	<?php emit_css_loading_tags($_); ?>
	<?php emit_script_loading_tags($_); ?>
	<?php print_unescaped($_['headers']); ?>
</head>

<body id="<?php p($_['bodyid']); ?>">
	<?php include 'layout.noscript.warning.php'; ?>

	<?php foreach ($_['initialStates'] as $app => $initialState) { ?>
		<input type="hidden" id="initial-state-<?php p($app); ?>" value="<?php p(base64_encode($initialState)); ?>">
	<?php } ?>

	<a href="#app-content" class="button primary skip-navigation skip-content"><?php p($l->t('Skip to main content')); ?></a>
	<a href="#app-navigation" class="button primary skip-navigation"><?php p($l->t('Skip to navigation of app')); ?></a>

	<div id="notification-container">
		<div id="notification"></div>
	</div>
	<header role="banner" id="header">
		<div class="header-left">
			<a href="<?php print_unescaped(link_to('', 'index.php')); ?>" id="nextcloud">
				<div class="logo logo-icon">
					<h1 class="hidden-visually">
						<?php p($theme->getName()); ?> <?php p(!empty($_['application']) ? $_['application'] : $l->t('Apps')); ?>
					</h1>
				</div>
			</a>

			<ul id="appmenu" <?php if ($_['themingInvertMenu']) { ?>class="inverted" <?php } ?>>
				<?php foreach ($_['navigation'] as $entry) : ?>
					<li data-id="<?php p($entry['id']); ?>" class="hidden" tabindex="-1">
						<a href="<?php print_unescaped($entry['href']); ?>" <?php if ($entry['active']) : ?> class="active" <?php endif; ?> aria-label="<?php p($entry['name']); ?>">
							<svg width="24" height="20" viewBox="0 0 24 20" alt="" <?php if ($entry['unread'] !== 0) { ?> class="has-unread" <?php } ?>>
								<defs>
									<?php if ($_['themingInvertMenu']) { ?><filter id="invertMenuMain-<?php p($entry['id']); ?>">
											<feColorMatrix in="SourceGraphic" type="matrix" values="-1 0 0 0 1 0 -1 0 0 1 0 0 -1 0 1 0 0 0 1 0" />
										</filter><?php } ?>
									<mask id="hole">
										<rect width="100%" height="100%" fill="white" />
										<circle r="4.5" cx="21" cy="3" fill="black" />
									</mask>
								</defs>
								<image x="2" y="0" width="20" height="20" preserveAspectRatio="xMinYMin meet" <?php if ($_['themingInvertMenu']) { ?> filter="url(#invertMenuMain-<?php p($entry['id']); ?>)" <?php } ?> xlink:href="<?php print_unescaped($entry['icon'] . '?v=' . $_['versionHash']); ?>" style="<?php if ($entry['unread'] !== 0) { ?>mask: url(" #hole");<?php } ?>" class="app-icon"></image>
								<circle class="app-icon-notification" r="3" cx="21" cy="3" fill="red" />
							</svg>
							<div class="unread-counter" aria-hidden="true"><?php p($entry['unread']); ?></div>
							<span>
								<?php p($entry['name']); ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
				<li id="more-apps" class="menutoggle" aria-haspopup="true" aria-controls="navigation" aria-expanded="false">
					<a href="#" aria-label="<?php p($l->t('More apps')); ?>">
						<div class="icon-more-white"></div>
						<span><?php p($l->t('More')); ?></span>
					</a>
				</li>
			</ul>

			<nav role="navigation">
				<div id="navigation" style="display: none;" aria-label="<?php p($l->t('More apps menu')); ?>">
					<div id="apps">
						<ul>
							<?php foreach ($_['navigation'] as $entry) : ?>
								<li data-id="<?php p($entry['id']); ?>">
									<a href="<?php print_unescaped($entry['href']); ?>" <?php if ($entry['active']) : ?> class="active" <?php endif; ?> aria-label="<?php p($entry['name']); ?>">
										<svg width="20" height="20" viewBox="0 0 20 20" alt="" <?php if ($entry['unread'] !== 0) { ?> class="has-unread" <?php } ?>>
											<defs>
												<filter id="invertMenuMore-<?php p($entry['id']); ?>">
													<feColorMatrix in="SourceGraphic" type="matrix" values="-1 0 0 0 1 0 -1 0 0 1 0 0 -1 0 1 0 0 0 1 0"></feColorMatrix>
												</filter>
												<mask id="hole">
													<rect width="100%" height="100%" fill="white" />
													<circle r="4.5" cx="17" cy="3" fill="black" />
												</mask>
											</defs>
											<image x="0" y="0" width="16" height="16" preserveAspectRatio="xMinYMin meet" filter="url(#invertMenuMore-<?php p($entry['id']); ?>)" xlink:href="<?php print_unescaped($entry['icon'] . '?v=' . $_['versionHash']); ?>" style="<?php if ($entry['unread'] !== 0) { ?>mask: url(" #hole");<?php } ?>" class="app-icon"></image>
											<circle class="app-icon-notification" r="3" cx="17" cy="3" fill="red" />
										</svg>
										<div class="unread-counter" aria-hidden="true"><?php p($entry['unread']); ?></div>
										<span class="app-title"><?php p($entry['name']); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</nav>

		</div>
		<div class="header-center" style="
			display: inline-flex;
			align-items: center;
			flex: 1 0;
			white-space: nowrap;
			min-width: 0;
		">
			<a href="https://buildingsmart.fi/" target="_blank" class="icon" style="
				padding: 7px 0;
				padding-left: 52px;
				position: relative;
				height: 100%;
				box-sizing: border-box;
				opacity: 1;
				align-items: center;
				display: flex;
				flex-wrap: wrap;
				overflow: hidden;
			">
				<div class="logo logo-home" style="
					display: inline-flex;
					background-repeat: no-repeat;
					background-size: contain;
					background-position: center;
					width: 30px;
					background-image: url('data:image/png;base64,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');
				">
					<h1 class="hidden-visually">
						bSF Home </h1>
				</div>
			</a><a href="https://wiki.buildingsmart.fi/" target="_blank" class="icon" style="
					padding: 7px 0;
					padding-left: 52px;
					position: relative;
					height: 100%;
					box-sizing: border-box;
					opacity: 1;
					align-items: center;
					display: flex;
					flex-wrap: wrap;
					overflow: hidden;
				">
				<div class="logo logo-wiki" style="display: inline-flex;background-repeat: no-repeat;background-size: contain;background-position: center;width: 30px;background-image: url('data:image/png;base64,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');">
					<h1 class="hidden-visually">
						bSF Wik</h1>
				</div>
			</a>
		</div>
		<div class="header-right">
			<div id="notifications"></div>
			<div id="unified-search"></div>
			<div id="contactsmenu">
				<div class="icon-contacts menutoggle" tabindex="0" role="button" aria-haspopup="true" aria-controls="contactsmenu-menu" aria-expanded="false">
					<span class="hidden-visually"><?php p($l->t('Contacts')); ?></span>
				</div>
				<div id="contactsmenu-menu" class="menu" aria-label="<?php p($l->t('Contacts menu')); ?>"></div>
			</div>
			<div id="settings">
				<div id="expand" tabindex="0" role="button" class="menutoggle" aria-label="<?php p($l->t('Settings')); ?>" aria-haspopup="true" aria-controls="expanddiv" aria-expanded="false">
					<div id="avatardiv-menu" class="avatardiv<?php if ($_['userAvatarSet']) {
																	print_unescaped(' avatardiv-shown');
																} else {
																	print_unescaped('" style="display: none');
																} ?>" data-user="<?php p($_['user_uid']); ?>" data-displayname="<?php p($_['user_displayname']); ?>" <?php if ($_['userStatus'] !== false) { ?> data-userstatus="<?php p($_['userStatus']->getStatus()); ?>" data-userstatus_message="<?php p($_['userStatus']->getMessage()); ?>" data-userstatus_icon="<?php p($_['userStatus']->getIcon()); ?>" <?php }
																																																																																																				if ($_['userAvatarSet']) {
																																																																																																					$avatar32 = $getUserAvatar(32); ?> data-avatar="<?php p($avatar32); ?>" <?php
																																																																																																																						} ?>>
						<?php
						if ($_['userAvatarSet']) { ?>
							<img alt="" width="32" height="32" src="<?php p($avatar32); ?>" srcset="<?php p($getUserAvatar(64)); ?> 2x, <?php p($getUserAvatar(128)); ?> 4x">
						<?php } ?>
					</div>
				</div>
				<nav class="settings-menu" id="expanddiv" style="display:none;" aria-label="<?php p($l->t('Settings menu')); ?>">
					<ul>
						<?php foreach ($_['settingsnavigation'] as $entry) : ?>
							<li data-id="<?php p($entry['id']); ?>">
								<a href="<?php print_unescaped($entry['href']); ?>" <?php if ($entry["active"]) : ?> class="active" <?php endif; ?>>
									<img alt="" src="<?php print_unescaped($entry['icon'] . '?v=' . $_['versionHash']); ?>">
									<?php p($entry['name']) ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</div>
		</div>
	</header>

	<div id="sudo-login-background" class="hidden"></div>
	<form id="sudo-login-form" class="hidden" method="POST">
		<label>
			<?php p($l->t('This action requires you to confirm your password')); ?><br />
			<input type="password" class="question" autocomplete="new-password" name="question" value=" <?php /* Hack against browsers ignoring autocomplete="off" */ ?>" placeholder="<?php p($l->t('Confirm your password')); ?>" />
		</label>
		<input class="confirm" value="<?php p($l->t('Confirm')); ?>" type="submit">
	</form>

	<div id="content" class="app-<?php p($_['appid']) ?>" role="main">
		<?php print_unescaped($_['content']); ?>
	</div>

</body>

</html>
