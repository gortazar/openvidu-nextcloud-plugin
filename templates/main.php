<?php

/** @var array $_ */
/** @var \OCP\IL10N $l */
/** @var \OCP\IURLGenerator $urlGenerator */

script('openviduintegration', 'app');
style('openviduintegration', 'style');
?>

<div id="app" class="app-openviduintegration">
	<div id="app-navigation">
		<ul>
			<li>
				<a href="<?php p(link_to('openviduintegration', '')); ?>">
					<?php p($l->t('My Rooms')); ?>
				</a>
			</li>
		</ul>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<div class="section">
				<h2><?php p($l->t('OpenVidu Meet – My Rooms')); ?></h2>

				<?php if (empty($_['openViduMeetUrl'])): ?>
					<div class="emptycontent">
						<div class="icon-video"></div>
						<h2><?php p($l->t('OpenVidu Meet server not configured')); ?></h2>
						<p><?php p($l->t('Please ask your administrator to set the OpenVidu Meet server URL in the admin settings.')); ?></p>
					</div>
				<?php else: ?>

					<!-- Create-room form -->
					<div class="openvidu-create-room">
						<form id="create-room-form" class="openvidu-form">
							<input
								id="room-name-input"
								type="text"
								name="name"
								placeholder="<?php p($l->t('Room name …')); ?>"
								maxlength="255"
								required
							/>
							<button type="submit" class="button primary">
								<?php p($l->t('Create room')); ?>
							</button>
						</form>
					</div>

					<!-- Room list -->
					<div id="room-list">
						<?php if (empty($_['rooms'])): ?>
							<div class="emptycontent">
								<div class="icon-video"></div>
								<h2><?php p($l->t('No rooms yet')); ?></h2>
								<p><?php p($l->t('Create your first room above.')); ?></p>
							</div>
						<?php else: ?>
							<ul class="openvidu-rooms">
								<?php foreach ($_['rooms'] as $room): ?>
									<li class="openvidu-room-item" data-token="<?php p($room['token']); ?>">
										<span class="openvidu-room-name"><?php p($room['name']); ?></span>
										<span class="openvidu-room-actions">
											<a
												href="<?php p(link_to('openviduintegration', 'room/' . $room['token'])); ?>"
												class="button"
											>
												<?php p($l->t('Join')); ?>
											</a>
											<button
												class="button icon-delete openvidu-delete-room"
												data-token="<?php p($room['token']); ?>"
												title="<?php p($l->t('Delete room')); ?>"
											></button>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>

				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script>
	/* Pass server-side data to our JS module. */
	window.OCA = window.OCA || {};
	window.OCA.OpenViduIntegration = {
		openViduMeetUrl: <?php echo json_encode($_['openViduMeetUrl']); ?>,
		userId: <?php echo json_encode($_['userId']); ?>,
		initialRooms: <?php echo json_encode($_['rooms']); ?>,
		createRoomUrl: OC.generateUrl('/apps/openviduintegration/api/rooms'),
		deleteRoomUrl: OC.generateUrl('/apps/openviduintegration/api/rooms'),
		roomBaseUrl: OC.generateUrl('/apps/openviduintegration/room'),
	};
</script>
