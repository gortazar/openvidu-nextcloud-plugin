<?php

/** @var array $_ */
/** @var \OCP\IL10N $l */
?>

<div class="section" id="openviduintegration-admin">
	<h2><?php p($l->t('OpenVidu Integration')); ?></h2>
	<p class="settings-hint">
		<?php p($l->t('Configure the OpenVidu Meet server that will be embedded inside Nextcloud.')); ?>
	</p>

	<form id="openviduintegration-admin-form">
		<div class="openvidu-admin-row">
			<label for="openvidu-meet-url"><?php p($l->t('OpenVidu Meet URL')); ?></label>
			<input
				id="openvidu-meet-url"
				type="url"
				name="openvidu_meet_url"
				value="<?php p($_['openvidu_meet_url']); ?>"
				placeholder="https://meet.example.com"
				class="input-medium"
			/>
			<em class="settings-hint">
				<?php p($l->t('The base URL of your OpenVidu Meet instance (e.g. https://meet.example.com). Leave empty to disable the integration.')); ?>
			</em>
		</div>

		<div class="openvidu-admin-row">
			<input
				type="submit"
				class="button primary"
				value="<?php p($l->t('Save')); ?>"
			/>
			<span id="openviduintegration-admin-msg" class="msg hidden"></span>
		</div>
	</form>
</div>

<script>
(function() {
	'use strict';
	document.getElementById('openviduintegration-admin-form')
		.addEventListener('submit', function (e) {
			e.preventDefault();
			var url = document.getElementById('openvidu-meet-url').value;
			var msgEl = document.getElementById('openviduintegration-admin-msg');
			msgEl.classList.remove('hidden', 'success', 'error');

			fetch(OC.generateUrl('/apps/openviduintegration/api/admin/settings'), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'requesttoken': OC.requestToken,
				},
				body: JSON.stringify({ openvidu_meet_url: url }),
			})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data.error) {
					msgEl.textContent = data.error;
					msgEl.classList.add('error');
				} else {
					msgEl.textContent = t('openviduintegration', 'Saved');
					msgEl.classList.add('success');
				}
			})
			.catch(function () {
				msgEl.textContent = t('openviduintegration', 'Error saving settings');
				msgEl.classList.add('error');
			});
		});
})();
</script>
