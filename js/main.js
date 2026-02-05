/**
 * Main JavaScript for Fabian Theme
 *
 * @package Fabian_Theme
 */

(function () {
    'use strict';

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

    function init() {
        initLenis();
        initRingsAnimation();
        initProjectParallax();
        initMobileMenu();
        initTOC();
        initPhotographyGallery();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
