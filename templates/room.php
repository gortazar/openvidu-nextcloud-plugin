<?php

/** @var array $_ */
/** @var \OCP\IL10N $l */

style('openviduintegration', 'style');

$roomName = $_['room']['name'] ?? $_['token'];
$meetUrl  = rtrim($_['openViduMeetUrl'], '/');
$apiKey   = $_['openViduApiKey'] ?? '';

// Build the embed URL: <OpenViduMeetUrl>/<token>
// OpenVidu Meet v3 requires the API key to be supplied as the `token` query
// parameter so that the embedded page can authenticate with the OpenVidu server.
$iframeSrc = '';
if ($meetUrl !== '') {
	$iframeSrc = $meetUrl . '/' . urlencode($_['token']);
	if ($apiKey !== '') {
		$iframeSrc .= '?token=' . urlencode($apiKey);
	}
}
?>

<div id="app" class="app-openviduintegration">
	<div id="app-navigation">
		<ul>
			<li>
				<a href="<?php p(link_to('openviduintegration', '')); ?>">
					← <?php p($l->t('Back to rooms')); ?>
				</a>
			</li>
		</ul>
	</div>

	<div id="app-content" class="openvidu-room-view">
		<?php if ($iframeSrc === ''): ?>
			<div class="emptycontent">
				<div class="icon-video"></div>
				<h2><?php p($l->t('OpenVidu Meet server not configured')); ?></h2>
				<p><?php p($l->t('Please ask your administrator to configure the OpenVidu Meet server URL.')); ?></p>
			</div>
		<?php elseif ($_['room'] === null): ?>
			<div class="emptycontent">
				<div class="icon-error"></div>
				<h2><?php p($l->t('Room not found')); ?></h2>
				<p><?php p($l->t('The requested room does not exist.')); ?></p>
			</div>
		<?php else: ?>
			<div class="openvidu-room-header">
				<h2><?php p($roomName); ?></h2>
			</div>
			<iframe
				id="openvidu-meet-frame"
				src="<?php p($iframeSrc); ?>"
				allow="camera; microphone; display-capture; fullscreen"
				allowfullscreen
			></iframe>
		<?php endif; ?>
	</div>
</div>
