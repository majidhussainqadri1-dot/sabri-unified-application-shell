(function () {
	'use strict';

	var state = {
		openDrawer: null,
		lastFocus: null
	};

	function ready(callback) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callback, { once: true });
			return;
		}
		callback();
	}

	function measureChrome() {
		var header = document.querySelector('.sabri-shell-header');
		var nav = document.querySelector('.sabri-shell-primary-nav');
		var headerHeight = header ? Math.ceil(header.getBoundingClientRect().height) : 0;
		var navHeight = nav ? Math.ceil(nav.getBoundingClientRect().height) : 0;
		var total = headerHeight + navHeight;
		document.body.style.setProperty('--sabri-shell-header-height', headerHeight + 'px');
		document.body.style.setProperty('--sabri-shell-chrome-height', total + 'px');
	}

	function focusable(container) {
		return Array.prototype.slice.call(container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), summary, [tabindex]:not([tabindex="-1"])'))
			.filter(function (element) {
				return element.offsetParent !== null || element === document.activeElement;
			});
	}

	function setTriggerState(id, expanded) {
		document.querySelectorAll('[data-sabri-drawer-trigger="' + id + '"]').forEach(function (trigger) {
			trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			if (window.SabriShell) {
				trigger.setAttribute('aria-label', expanded ? window.SabriShell.closeLabel : window.SabriShell.openLabel);
			}
		});
	}

	function openDrawer(id, trigger) {
		var drawer = document.getElementById(id);
		var overlay = document.querySelector('[data-sabri-drawer-overlay]');
		if (!drawer) {
			return;
		}

		closeDrawer();
		state.openDrawer = drawer;
		state.lastFocus = trigger || document.activeElement;
		drawer.removeAttribute('hidden');
		drawer.removeAttribute('inert');
		drawer.setAttribute('aria-hidden', 'false');
		drawer.classList.add('is-open');
		if (overlay) {
			overlay.hidden = false;
		}
		document.body.classList.add('sabri-shell-drawer-open');
		setTriggerState(id, true);

		var items = focusable(drawer);
		if (items.length) {
			items[0].focus();
		}
	}

	function closeDrawer() {
		if (!state.openDrawer) {
			return;
		}

		var drawer = state.openDrawer;
		var overlay = document.querySelector('[data-sabri-drawer-overlay]');
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		drawer.setAttribute('inert', '');
		if (overlay) {
			overlay.hidden = true;
		}
		document.body.classList.remove('sabri-shell-drawer-open');
		setTriggerState(drawer.id, false);
		state.openDrawer = null;

		if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
			state.lastFocus.focus();
		}
	}

	function onKeydown(event) {
		if (event.key === 'Escape') {
			closeDrawer();
			return;
		}

		if (event.key !== 'Tab' || !state.openDrawer) {
			return;
		}

		var items = focusable(state.openDrawer);
		if (!items.length) {
			event.preventDefault();
			return;
		}

		var first = items[0];
		var last = items[items.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function bindDrawers() {
		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('[data-sabri-drawer-trigger]');
			if (trigger) {
				event.preventDefault();
				openDrawer(trigger.getAttribute('data-sabri-drawer-trigger'), trigger);
				return;
			}

			if (event.target.closest('[data-sabri-drawer-close]') || event.target.matches('[data-sabri-drawer-overlay]')) {
				event.preventDefault();
				closeDrawer();
				return;
			}

			if (state.openDrawer && event.target.closest('.sabri-shell-drawer a[href]')) {
				closeDrawer();
			}
		});

		document.addEventListener('keydown', onKeydown);

		var media = window.matchMedia('(min-width: 1024px)');
		var onChange = function () {
			if (media.matches) {
				closeDrawer();
			}
		};
		if (typeof media.addEventListener === 'function') {
			media.addEventListener('change', onChange);
		} else if (typeof media.addListener === 'function') {
			media.addListener(onChange);
		}
	}

	function rememberSidebarScroll() {
		var sidebar = document.querySelector('.sabri-shell-left-sidebar:not(.sabri-shell-left-sidebar-drawer)');
		if (!sidebar || !window.sessionStorage) {
			return;
		}

		var key = 'sabriShellLeftScroll:' + window.location.pathname;
		var saved = parseInt(window.sessionStorage.getItem(key) || '0', 10);
		if (saved > 0) {
			sidebar.scrollTop = saved;
		}

		sidebar.addEventListener('scroll', function () {
			window.sessionStorage.setItem(key, String(sidebar.scrollTop));
		}, { passive: true });
	}

	function normalizeMainTarget() {
		var target = document.querySelector('main, [role="main"], #content, .site-content, .wp-site-blocks');
		var anchor = document.getElementById('sabri-shell-main-content');
		if (target && target !== anchor) {
			if (anchor) {
				anchor.removeAttribute('id');
			}
			target.id = 'sabri-shell-main-content';
			if (!target.hasAttribute('tabindex')) {
				target.setAttribute('tabindex', '-1');
			}
		}
	}

	ready(function () {
		measureChrome();
		bindDrawers();
		rememberSidebarScroll();
		normalizeMainTarget();

		window.addEventListener('resize', measureChrome, { passive: true });
		window.addEventListener('load', measureChrome, { once: true });
		if (document.fonts && typeof document.fonts.ready.then === 'function') {
			document.fonts.ready.then(measureChrome);
		}
	});
}());
