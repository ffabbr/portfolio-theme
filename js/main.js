/**
 * Main JavaScript for Fabian Theme
 *
 * @package Fabian_Theme
 */

(function () {
    'use strict';

    // =========================================================================
    // Archive scroll restore (run synchronously, before Lenis init / paint)
    // =========================================================================

    const archiveScrollKey = 'fabian-archive-scroll:' + window.location.pathname;
    const isArchivePage = !!document.querySelector('.writing__posts, .category-archive__posts');

    if (isArchivePage) {
        const saved = sessionStorage.getItem(archiveScrollKey);
        if (saved !== null) {
            sessionStorage.removeItem(archiveScrollKey);
            const y = parseInt(saved, 10);
            if (!isNaN(y)) {
                // Strip any hash so the browser's hash-scroll doesn't fight our restore
                if (window.location.hash) {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                }
                window.scrollTo(0, y);
            }
        }
    }

    // =========================================================================
    // Lenis Smooth Scroll
    // =========================================================================

    let lenis = null;

    function initLenis() {
        // Check for Lenis - it may be exposed as window.Lenis or as a default export
        var LenisClass = window.Lenis || (typeof Lenis !== 'undefined' ? Lenis : null);

        if (!LenisClass) {
            return;
        }

        try {
            lenis = new LenisClass({
                // Subtle smooth scrolling (still enabled, less "floaty")
                duration: 0.65,
                easing: function (t) { return 1 - Math.pow(1 - t, 3); },
                smoothWheel: true
            });
            window.__lenis = lenis;

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // Disable scroll restoration to let Lenis handle it
            if ('scrollRestoration' in window.history) {
                window.history.scrollRestoration = 'manual';
            }
        } catch (e) {
            // Lenis initialization failed silently
        }
    }

    // =========================================================================
    // Rings Animation (Home Page)
    // =========================================================================

    function initRingsAnimation() {
        const ringsWrapper = document.getElementById('rings-wrapper');
        if (!ringsWrapper) return;

        const ellipses = ringsWrapper.querySelectorAll('ellipse');
        if (!ellipses.length) return;

        const startRy = [500, 480, 460];
        const endRy = [120, 110, 100];

        function handleScroll() {
            const rect = ringsWrapper.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            // Progress based on element position in viewport
            const progress = Math.max(0, Math.min(1, (windowHeight - rect.top) / (windowHeight * 1.2)));

            ellipses.forEach(function (ellipse, i) {
                const ry = startRy[i] - (startRy[i] - endRy[i]) * progress;
                ellipse.setAttribute('ry', ry);
            });
        }

        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Initial call
    }

    // =========================================================================
    // Project Parallax Effect
    // =========================================================================

    function initProjectParallax() {
        const hero = document.getElementById('project-hero');
        const background = document.getElementById('project-hero-bg');

        if (!hero || !background) return;

        function handleScroll() {
            const rect = hero.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            // Only apply parallax when hero is in view
            if (rect.bottom < 0 || rect.top > windowHeight) return;

            const scrollProgress = -rect.top;
            const parallaxOffset = scrollProgress * 0.2;

            background.style.transform = 'translate3d(0, ' + parallaxOffset + 'px, 0)';
        }

        window.addEventListener('scroll', handleScroll);
        handleScroll();
    }

    // =========================================================================
    // Mobile Menu Toggle
    // =========================================================================

    function initMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const nav = document.getElementById('primary-navigation');

        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            const isOpen = nav.classList.contains('nav--open');
            nav.classList.toggle('nav--open');
            toggle.setAttribute('aria-expanded', !isOpen);
        });

        // Close menu when clicking a link
        nav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('nav--open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // =========================================================================
    // TOC Scroll-to Functionality
    // =========================================================================

    const SCROLL_OFFSET = 100;

    function scrollToId(id) {
        const target = document.getElementById(id);
        if (!target) return;

        // Use Lenis if available for smooth scrolling
        if (lenis) {
            lenis.scrollTo(target, { offset: -SCROLL_OFFSET });
        } else {
            const y = target.getBoundingClientRect().top + window.scrollY - SCROLL_OFFSET;
            window.scrollTo({ top: y, left: 0, behavior: 'smooth' });
        }
    }

    function initTOC() {
        // Desktop TOC links
        document.querySelectorAll('.blog-post__toc a.toc-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('href').slice(1);
                window.history.pushState(null, '', '#' + id);
                scrollToId(id);
            });
        });

        // Mobile TOC links
        document.querySelectorAll('.blog-post__toc-mobile a.toc-link-mobile').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('href').slice(1);
                const details = this.closest('details');
                if (details && details.open) {
                    details.open = false;
                }
                window.history.pushState(null, '', '#' + id);
                scrollToId(id);
            });
        });

        // Handle initial hash
        function scrollToCurrentHash() {
            const raw = window.location.hash;
            if (!raw) return;
            const id = decodeURIComponent(raw.slice(1));
            if (!id) return;
            scrollToId(id);
        }

        window.addEventListener('hashchange', function () {
            requestAnimationFrame(scrollToCurrentHash);
        });

        // Scroll on initial load if there's a hash
        if (window.location.hash) {
            setTimeout(scrollToCurrentHash, 100);
        }
    }


    // =========================================================================
    // Photography Gallery (GSAP)
    // =========================================================================

    let activeFolder = null;
    let folderState = 'closed';
    let scrollTriggerInstance = null;
    let hasReachedEnd = false;

    function mapRange(value, inMin, inMax, outMin, outMax) {
        return ((value - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;
    }

    function initPhotographyGallery() {
        const foldersView = document.getElementById('folders-view');
        const container = document.getElementById('gsap-scroll-container');
        const folders = document.querySelectorAll('.folder[data-collection-id]');

        if (!foldersView || !folders.length) return;

        // Trigger entry animation
        setTimeout(function () {
            foldersView.classList.add('loaded');
        }, 100);

        // Make sure GSAP and ScrollTrigger are available
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        folders.forEach(function (folder) {
            folder.addEventListener('click', function () {
                if (activeFolder) return;

                const collectionId = folder.dataset.collectionId;
                const collectionName = folder.dataset.collectionName;
                const collectionColor = folder.dataset.collectionColor;
                let photos = [];

                try {
                    photos = JSON.parse(folder.dataset.photos);
                } catch (e) {
                    return;
                }

                openFolder({
                    id: collectionId,
                    name: collectionName,
                    color: collectionColor,
                    photos: photos
                }, folder);
            });
        });

        function openFolder(collection, folderElement) {
            activeFolder = collection;
            folderState = 'opening';
            hasReachedEnd = false;

            foldersView.classList.add('has-active');

            // Create the scroll container content
            const scrollContainerHTML = createScrollContainerHTML(collection);
            container.innerHTML = scrollContainerHTML;
            container.style.height = (collection.photos.length * 200) + 'vh';
            container.style.setProperty('--folder-color', collection.color);

            // Set up close button
            const closeBtn = container.querySelector('.gsap-header__close');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeFolder);
            }

            setTimeout(function () {
                folderState = 'open';
                window.scrollTo(0, 0);
                initGSAPSlider(collection);
            }, 550);
        }

        function createScrollContainerHTML(collection) {
            let html = '<div class="gsap-scroll-container">';

            // Background images
            html += '<div class="active-slide-bg">';
            collection.photos.forEach(function (photo, index) {
                const bgUrl = photo.medium || photo.large || photo.full;
                html += '<div class="bg-image" style="background-image: url(' + bgUrl + '); opacity: ' + (index === 0 ? 1 : 0) + ';"></div>';
            });
            html += '</div>';

            // Slider
            html += '<div class="gsap-slider">';
            collection.photos.forEach(function (photo, index) {
                const isLeft = index % 2 === 0;
                const imgUrl = photo.large || photo.full || photo.medium;
                html += '<div class="gsap-slide" style="left: ' + (isLeft ? '35%' : '65%') + ';">';
                html += '<div class="gsap-slide__copy">';
                html += '<p class="gsap-slide__title">' + (photo.title || '') + '</p>';
                html += '<p class="gsap-slide__index">( ' + String(index + 1).padStart(2, '0') + ' / ' + String(collection.photos.length).padStart(2, '0') + ' )</p>';
                html += '</div>';
                html += '<div class="gsap-slide__img">';
                html += '<img src="' + imgUrl + '" alt="' + (photo.alt || photo.title || '') + '" draggable="false">';
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';

            // Header
            html += '<div class="gsap-header">';
            html += '<h2 class="gsap-header__title">' + collection.name + '</h2>';
            html += '<div class="scroll-indicator"><span>Scroll to explore</span></div>';
            html += '<button class="gsap-header__close"><span>&times;</span></button>';
            html += '</div>';

            html += '</div>';

            return html;
        }

        function initGSAPSlider(collection) {
            const slides = container.querySelectorAll('.gsap-slide');
            const bgImages = container.querySelectorAll('.bg-image');
            const zSpacing = 2500;
            const totalPhotos = collection.photos.length;

            // Set initial Z positions
            slides.forEach(function (slide, index) {
                const initialZ = -index * zSpacing;
                slide.dataset.initialZ = initialZ;
                slide.style.transform = 'translateX(-50%) translateY(-50%) translateZ(' + initialZ + 'px)';

                let initialOpacity;
                if (initialZ > -2500) {
                    initialOpacity = mapRange(initialZ, -2500, 0, 0.5, 1);
                } else {
                    initialOpacity = mapRange(initialZ, -5000, -2500, 0, 0.5);
                }
                slide.style.opacity = Math.max(0, Math.min(1, initialOpacity));
            });

            // Create ScrollTrigger
            scrollTriggerInstance = ScrollTrigger.create({
                trigger: container,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.5,
                onUpdate: function (self) {
                    const progress = self.progress;
                    const zIncrement = progress * (totalPhotos * zSpacing);

                    slides.forEach(function (slide, index) {
                        const initialZ = parseFloat(slide.dataset.initialZ);
                        const currentZ = initialZ + zIncrement;

                        let opacity;
                        if (currentZ > -2500) {
                            opacity = mapRange(currentZ, -2500, 0, 0.5, 1);
                        } else {
                            opacity = mapRange(currentZ, -5000, -2500, 0, 0.5);
                        }
                        opacity = Math.max(0, Math.min(1, opacity));

                        slide.style.opacity = opacity;
                        slide.style.transform = 'translateX(-50%) translateY(-50%) translateZ(' + currentZ + 'px)';

                        // Update background
                        if (bgImages[index]) {
                            if (currentZ > -500 && currentZ < 500) {
                                gsap.to(bgImages[index], { opacity: 1, duration: 0.8, ease: 'power3.out' });
                            } else {
                                gsap.to(bgImages[index], { opacity: 0, duration: 0.8, ease: 'power3.out' });
                            }
                        }
                    });

                    // Auto-close when reaching the end
                    if (progress >= 0.85 && !hasReachedEnd) {
                        hasReachedEnd = true;
                        setTimeout(closeFolder, 400);
                    }
                }
            });

            ScrollTrigger.refresh();
        }

        function closeFolder() {
            if (scrollTriggerInstance) {
                scrollTriggerInstance.kill();
                scrollTriggerInstance = null;
            }

            folderState = 'closing';
            foldersView.classList.add('is-closing');

            setTimeout(function () {
                folderState = 'closed';
                activeFolder = null;
                foldersView.classList.remove('has-active', 'is-closing');
                container.innerHTML = '';
                container.style.height = '';
                window.scrollTo(0, 0);
            }, 1000);
        }
    }

    // =========================================================================
    // Initialize
    // =========================================================================

    // =========================================================================
    // Archive scroll memory (Writing / category archives)
    // =========================================================================

    function initArchiveScrollMemory() {
        if (isArchivePage) {
            window.addEventListener('pagehide', function () {
                sessionStorage.setItem(archiveScrollKey, String(window.scrollY));
            });
        }

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || link.target === '_blank') return;
            if (isArchivePage) {
                sessionStorage.setItem(archiveScrollKey, String(window.scrollY));
            }
        }, true);
    }

    // =========================================================================
    // Command Palette (Cmd+K / Ctrl+K)
    // =========================================================================

    function initCommandPalette() {
        const data = window.fabianCmdK || { items: [], home: '/' };
        const isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform);

        // Single theme command: switches to the opposite of the currently resolved theme.
        function getThemeCommands() {
            const target = currentResolvedTheme() === 'dark' ? 'light' : 'dark';
            return [
                { type: 'Theme', title: 'Use ' + target + ' theme', action: 'theme:' + target, icon: target === 'dark' ? 'moon' : 'sun' }
            ];
        }

        function getPinnedPages() {
            const home = (data.home || '/').replace(/\/?$/, '/');
            return [
                { type: 'Pages', title: 'Home', url: home, icon: 'home' },
                { type: 'Pages', title: 'Writing', url: home + 'writing/', icon: 'writing' }
            ];
        }

        const ICONS = {
            home: '<path d="M3.5 10.5 12 3.5l8.5 7"/><path d="M5.5 9.5v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-9"/>',
            writing: '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
            sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
            moon: '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            page: '<path d="M14 2H7a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>',
            post: '<rect x="4" y="4" width="16" height="16" rx="4"/><path d="M8 9h8M8 13h8M8 17h5"/>',
            project: '<rect x="3" y="7" width="18" height="13" rx="3"/><path d="M8 7V5.5A2.5 2.5 0 0 1 10.5 3h3A2.5 2.5 0 0 1 16 5.5V7"/>',
            talk: '<rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0"/><path d="M12 19v3"/>'
        };

        const TYPE_FALLBACK_ICON = {
            'Page': 'page',
            'Pages': 'page',
            'Post': 'post',
            'Project': 'project',
            'Talk': 'talk',
            'Theme': 'sun'
        };

        function iconSvg(name) {
            const path = ICONS[name];
            if (!path) return '';
            return '<svg class="cmdk-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
        }

        function applyTheme(mode) {
            try {
                const root = document.documentElement;
                root.classList.remove('theme-light', 'theme-dark');
                if (mode === 'system') {
                    localStorage.removeItem('fabian-theme');
                    root.removeAttribute('data-theme');
                } else {
                    localStorage.setItem('fabian-theme', mode);
                    root.setAttribute('data-theme', mode);
                    root.classList.add('theme-' + mode);
                }
            } catch (e) {}
        }

        function currentResolvedTheme() {
            const set = document.documentElement.getAttribute('data-theme');
            if (set) return set;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        // Markup
        const overlay = document.createElement('div');
        overlay.className = 'cmdk-overlay';
        overlay.setAttribute('data-open', 'false');

        const dialog = document.createElement('div');
        dialog.className = 'cmdk-dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('aria-label', 'Command palette');
        dialog.setAttribute('data-open', 'false');

        dialog.setAttribute('data-lenis-prevent', '');
        dialog.innerHTML =
            '<div class="cmdk-input-wrap">' +
                '<svg class="cmdk-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>' +
                '<input type="text" class="cmdk-input" placeholder="Search pages, posts, actions..." autocomplete="off" spellcheck="false" aria-label="Search">' +
            '</div>' +
            '<ul class="cmdk-list" role="listbox" aria-label="Search results" data-lenis-prevent></ul>';

        document.body.appendChild(overlay);
        document.body.appendChild(dialog);

        const input = dialog.querySelector('.cmdk-input');
        const list = dialog.querySelector('.cmdk-list');

        let visibleItems = [];
        let selectedIndex = 0;

        function fuzzyMatch(query, text) {
            if (!query) return true;
            const q = query.toLowerCase();
            const t = text.toLowerCase();
            if (t.indexOf(q) !== -1) return true;
            // simple subsequence match
            let i = 0;
            for (let j = 0; j < t.length && i < q.length; j++) {
                if (t.charAt(j) === q.charAt(i)) i++;
            }
            return i === q.length;
        }

        function render() {
            const query = input.value.trim();
            const pinned = getPinnedPages();
            const pinnedUrls = {};
            pinned.forEach(function (p) { pinnedUrls[p.url] = true; });
            const filteredFromWP = (data.items || []).filter(function (it) { return !pinnedUrls[it.url]; });
            const allItems = pinned.concat(getThemeCommands()).concat(filteredFromWP);
            visibleItems = allItems.filter(function (it) { return fuzzyMatch(query, it.title) || fuzzyMatch(query, it.type); });
            if (!query) {
                let postCount = 0;
                visibleItems = visibleItems.filter(function (it) {
                    if (it.type === 'Post') {
                        postCount++;
                        return postCount <= 3;
                    }
                    return true;
                });
            }
            selectedIndex = 0;

            if (!visibleItems.length) {
                list.innerHTML = '<li class="cmdk-empty">No results.</li>';
                return;
            }

            // Group by type
            const groups = {};
            const order = [];
            visibleItems.forEach(function (it) {
                if (!groups[it.type]) { groups[it.type] = []; order.push(it.type); }
                groups[it.type].push(it);
            });

            let html = '';
            let idx = 0;
            order.forEach(function (type) {
                html += '<li class="cmdk-group-label" role="presentation">' + escapeHtml(type) + '</li>';
                groups[type].forEach(function (it) {
                    const hint = it.hint ? '<span class="cmdk-item__type">' + escapeHtml(it.hint) + '</span>' : '';
                    const iconName = it.icon || TYPE_FALLBACK_ICON[it.type] || '';
                    html += '<li class="cmdk-item" role="option" data-index="' + idx + '" data-selected="' + (idx === 0 ? 'true' : 'false') + '">' +
                        iconSvg(iconName) +
                        '<span class="cmdk-item__title">' + escapeHtml(it.title) + '</span>' + hint + '</li>';
                    idx++;
                });
            });

            list.innerHTML = html;
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function setSelected(i) {
            const items = list.querySelectorAll('.cmdk-item');
            if (!items.length) return;
            if (i < 0) i = items.length - 1;
            if (i >= items.length) i = 0;
            selectedIndex = i;
            items.forEach(function (el, j) {
                el.setAttribute('data-selected', j === i ? 'true' : 'false');
                if (j === i) el.scrollIntoView({ block: 'nearest' });
            });
        }

        function activate(item) {
            if (!item) return;
            if (item.action) {
                if (item.action === 'theme:toggle') {
                    applyTheme(currentResolvedTheme() === 'dark' ? 'light' : 'dark');
                } else if (item.action === 'theme:light') {
                    applyTheme('light');
                } else if (item.action === 'theme:dark') {
                    applyTheme('dark');
                } else if (item.action === 'theme:system') {
                    applyTheme('system');
                }
                close();
                return;
            }
            if (item.url) {
                close();
                if (window.__swup && typeof window.__swup.navigate === 'function') {
                    window.__swup.navigate(item.url);
                } else {
                    window.location.href = item.url;
                }
            }
        }

        function open() {
            overlay.setAttribute('data-open', 'true');
            dialog.setAttribute('data-open', 'true');
            input.value = '';
            render();
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            if (lenis && typeof lenis.stop === 'function') lenis.stop();
            setTimeout(function () { input.focus(); }, 0);
        }

        function close() {
            overlay.setAttribute('data-open', 'false');
            dialog.setAttribute('data-open', 'false');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            if (lenis && typeof lenis.start === 'function') lenis.start();
        }

        function isOpen() { return dialog.getAttribute('data-open') === 'true'; }

        // Events
        input.addEventListener('input', render);

        dialog.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); setSelected(selectedIndex + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setSelected(selectedIndex - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); activate(visibleItems[selectedIndex]); }
            else if (e.key === 'Escape') { e.preventDefault(); close(); }
        });

        list.addEventListener('mousemove', function (e) {
            const li = e.target.closest('.cmdk-item');
            if (!li) return;
            const idx = parseInt(li.getAttribute('data-index'), 10);
            if (!isNaN(idx) && idx !== selectedIndex) setSelected(idx);
        });

        list.addEventListener('click', function (e) {
            const li = e.target.closest('.cmdk-item');
            if (!li) return;
            const idx = parseInt(li.getAttribute('data-index'), 10);
            activate(visibleItems[idx]);
        });

        overlay.addEventListener('click', close);

        document.addEventListener('keydown', function (e) {
            const mod = isMac ? e.metaKey : e.ctrlKey;
            if (mod && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                if (isOpen()) close(); else open();
            } else if (e.key === 'Escape' && isOpen()) {
                close();
            }
        });
    }

    function init() {
        initLenis();
        initRingsAnimation();
        initProjectParallax();
        initMobileMenu();
        initTOC();
        initPhotographyGallery();
        initArchiveScrollMemory();
        initCommandPalette();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
