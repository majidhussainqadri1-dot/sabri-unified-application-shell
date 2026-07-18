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
			var openLabel = trigger.getAttribute('data-sabri-open-label') || (window.SabriShell && window.SabriShell.openLabel) || 'Open menu';
			var closeLabel = trigger.getAttribute('data-sabri-close-label') || (window.SabriShell && window.SabriShell.closeLabel) || 'Close menu';
			trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			trigger.setAttribute('aria-label', expanded ? closeLabel : openLabel);
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

		var navMedia = window.matchMedia('(min-width: 1024px)');
		var contextMedia = window.matchMedia('(min-width: 1200px)');
		var onChange = function () {
			if (!state.openDrawer) {
				return;
			}
			if (state.openDrawer.id === 'sabri-shell-drawer-nav' && navMedia.matches) {
				closeDrawer();
			}
			if (state.openDrawer && state.openDrawer.id === 'sabri-shell-drawer-context' && contextMedia.matches) {
				closeDrawer();
			}
		};
		[navMedia, contextMedia].forEach(function (media) {
			if (typeof media.addEventListener === 'function') {
				media.addEventListener('change', onChange);
			} else if (typeof media.addListener === 'function') {
				media.addListener(onChange);
			}
		});
	}

	function configuredCandidates() {
		var candidates = [];
		if (window.SabriShell && window.SabriShell.contentSelector) {
			candidates.push(window.SabriShell.contentSelector);
		}
		if (window.SabriShell && Array.isArray(window.SabriShell.contentCandidates)) {
			window.SabriShell.contentCandidates.forEach(function (selector) {
				if (selector && candidates.indexOf(selector) === -1) {
					candidates.push(selector);
				}
			});
		}
		['.wp-site-blocks', '#page', '.site', 'main', '#content', '.site-content'].forEach(function (selector) {
			if (candidates.indexOf(selector) === -1) {
				candidates.push(selector);
			}
		});
		return candidates;
	}

	function validContentTarget(target) {
		if (!target || target === document.body || target === document.documentElement) {
			return false;
		}
		if (target.closest('[data-sabri-shell-component]') || target.matches('[data-sabri-shell-component]')) {
			return false;
		}
		return true;
	}

	function resolveContentTarget() {
		var candidates = configuredCandidates();
		for (var i = 0; i < candidates.length; i += 1) {
			try {
				var target = document.querySelector(candidates[i]);
				if (validContentTarget(target)) {
					document.body.setAttribute('data-sabri-shell-content-target', candidates[i]);
					return target;
				}
			} catch (error) {
				// Invalid selectors are rejected in PHP; this keeps runtime resilient if a theme mutates settings.
			}
		}
		return null;
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

	function assembleStructuralLayout() {
		var host = document.getElementById('sabri-shell-layout-host');
		var left = document.querySelector('.sabri-shell-left-sidebar:not(.sabri-shell-left-sidebar-drawer)');
		var right = document.querySelector('.sabri-shell-right-sidebar:not(.sabri-shell-right-sidebar-drawer)');
		var target = resolveContentTarget();
		var anchor = document.getElementById('sabri-shell-main-content');

		if (!host || !target) {
			return;
		}

		if (anchor && anchor !== target) {
			anchor.removeAttribute('id');
		}
		target.id = 'sabri-shell-main-content';
		target.classList.add('sabri-shell-content-column');
		if (!target.hasAttribute('tabindex')) {
			target.setAttribute('tabindex', '-1');
		}

		if (left) {
			document.body.classList.add('sabri-shell-has-left-sidebar');
			host.appendChild(left);
		} else {
			document.body.classList.add('sabri-shell-no-left-sidebar');
		}
		host.appendChild(target);
		if (right) {
			document.body.classList.add('sabri-shell-has-right-sidebar');
			host.appendChild(right);
		} else {
			document.body.classList.add('sabri-shell-no-right-sidebar');
		}

		host.hidden = false;
		host.classList.add('is-ready');
		document.body.classList.add('sabri-shell-layout-ready');
	}

	ready(function () {
		measureChrome();
		assembleStructuralLayout();
		bindDrawers();
		rememberSidebarScroll();

		window.addEventListener('resize', measureChrome, { passive: true });
		window.addEventListener('load', measureChrome, { once: true });
		if (document.fonts && typeof document.fonts.ready.then === 'function') {
			document.fonts.ready.then(measureChrome);
		}
	});
}());
