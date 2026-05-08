/**
 * OpenVidu Integration – Frontend App
 *
 * Manages the room-list page: create room, delete room, real-time list update.
 * All fetch calls use the Nextcloud request token for CSRF protection.
 */
(function (OCA) {
	'use strict';

	var config = OCA.OpenViduIntegration || {};

	/* ------------------------------------------------------------------ */
	/* Utility helpers                                                      */
	/* ------------------------------------------------------------------ */

	/** Build the URL for the embedded room page. */
	function roomUrl(token) {
		return config.roomBaseUrl + '/' + encodeURIComponent(token);
	}

	/** Returns common fetch headers including the Nextcloud CSRF token. */
	function headers() {
		return {
			'Content-Type': 'application/json',
			'requesttoken': OC.requestToken,
		};
	}

	/* ------------------------------------------------------------------ */
	/* DOM helpers                                                          */
	/* ------------------------------------------------------------------ */

	function createRoomListItem(room) {
		var li = document.createElement('li');
		li.className = 'openvidu-room-item';
		li.dataset.token = room.token;

		var nameSpan = document.createElement('span');
		nameSpan.className = 'openvidu-room-name';
		nameSpan.textContent = room.name;

		var actions = document.createElement('span');
		actions.className = 'openvidu-room-actions';

		var joinLink = document.createElement('a');
		joinLink.href = roomUrl(room.token);
		joinLink.className = 'button';
		joinLink.textContent = t('openviduintegration', 'Join');

		var deleteBtn = document.createElement('button');
		deleteBtn.className = 'button icon-delete openvidu-delete-room';
		deleteBtn.dataset.token = room.token;
		deleteBtn.title = t('openviduintegration', 'Delete room');
		deleteBtn.addEventListener('click', handleDeleteRoom);

		actions.appendChild(joinLink);
		actions.appendChild(deleteBtn);
		li.appendChild(nameSpan);
		li.appendChild(actions);
		return li;
	}

	function removeRoomFromList(token) {
		var item = document.querySelector('.openvidu-room-item[data-token="' + token + '"]');
		if (item) {
			item.parentNode.removeChild(item);
		}
	}

	function ensureRoomList() {
		var list = document.querySelector('.openvidu-rooms');
		if (!list) {
			var roomListDiv = document.getElementById('room-list');
			// Remove the emptycontent placeholder if present
			var empty = roomListDiv && roomListDiv.querySelector('.emptycontent');
			if (empty) {
				roomListDiv.removeChild(empty);
			}
			list = document.createElement('ul');
			list.className = 'openvidu-rooms';
			if (roomListDiv) {
				roomListDiv.appendChild(list);
			}
		}
		return list;
	}

	/* ------------------------------------------------------------------ */
	/* Event handlers                                                       */
	/* ------------------------------------------------------------------ */

	function handleCreateRoom(e) {
		e.preventDefault();
		var nameInput = document.getElementById('room-name-input');
		var name = nameInput ? nameInput.value.trim() : '';
		if (!name) {
			return;
		}

		fetch(config.createRoomUrl, {
			method: 'POST',
			headers: headers(),
			body: JSON.stringify({ name: name }),
		})
		.then(function (r) {
			if (!r.ok) {
				return r.json().then(function (d) { throw new Error(d.error || 'Error'); });
			}
			return r.json();
		})
		.then(function (room) {
			var list = ensureRoomList();
			list.insertBefore(createRoomListItem(room), list.firstChild);
			if (nameInput) {
				nameInput.value = '';
			}
		})
		.catch(function (err) {
			OC.Notification.showTemporary(err.message || t('openviduintegration', 'Could not create room'));
		});
	}

	function handleDeleteRoom(e) {
		var token = e.currentTarget.dataset.token;
		if (!token) {
			return;
		}

		OC.dialogs.confirm(
			t('openviduintegration', 'Are you sure you want to delete this room?'),
			t('openviduintegration', 'Delete room'),
			function (confirmed) {
				if (!confirmed) {
					return;
				}
				fetch(config.deleteRoomUrl + '/' + encodeURIComponent(token), {
					method: 'DELETE',
					headers: headers(),
				})
				.then(function (r) {
					if (!r.ok) {
						return r.json().then(function (d) { throw new Error(d.error || 'Error'); });
					}
					removeRoomFromList(token);
				})
				.catch(function (err) {
					OC.Notification.showTemporary(err.message || t('openviduintegration', 'Could not delete room'));
				});
			},
			true
		);
	}

	/* ------------------------------------------------------------------ */
	/* Bootstrap                                                            */
	/* ------------------------------------------------------------------ */

	document.addEventListener('DOMContentLoaded', function () {
		// Bind the create-room form
		var form = document.getElementById('create-room-form');
		if (form) {
			form.addEventListener('submit', handleCreateRoom);
		}

		// Bind delete buttons for rooms already in the DOM (server-rendered)
		document.querySelectorAll('.openvidu-delete-room').forEach(function (btn) {
			btn.addEventListener('click', handleDeleteRoom);
		});
	});

})(window.OCA || {});
