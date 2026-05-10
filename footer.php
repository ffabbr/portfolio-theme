<?php
/**
 * The footer for our theme
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>

<footer class="footer">
    <div class="footer-content">
        <webring-banner></webring-banner>

        <div class="footer-bottom">
            <div class="footer-name">Fabian Rohlik</div>
            <a class="footer-email" href="mailto:hi@rohlik.net">hi@rohlik.net</a>
        </div>
    </div>
</footer>

<script>
    // Polyring webring script - deferred for performance
    // Uses requestIdleCallback to load only when browser is idle
    (function () {
        function initPolyring() {
            async function loadMembers() {
                const res = await fetch("https://polyring.ch/embed.js");
                const js = await res.text();
                const match = js.match(/MEMBERS\s*=\s*(\[.*\]);/s);
                if (!match) throw new Error("Could not find MEMBERS array in embed.js");
                return eval(match[1]);
            }

            class WebringBanner extends HTMLElement {
                constructor() {
                    super();
                    this.render();
                }

                async render() {
                    const MEMBERS = await loadMembers();

                    const idx = MEMBERS.findIndex(m =>
                        m.url.includes(window.location.hostname)
                    );
                    const prev = idx <= 0 ? MEMBERS[MEMBERS.length - 1] : MEMBERS[idx - 1];
                    const next = idx >= MEMBERS.length - 1 ? MEMBERS[0] : MEMBERS[idx + 1];

                    this.innerHTML = `
                    <style>
                        .webring {
                            border: 1px solid var(--webring-border, #ddd);
                            border-radius: 12px;
                            padding: 0.75em 1em;
                            background: var(--webring-bg, transparent);
                            font-size: 0.95em;
                            margin-bottom: 20px !important;
                        }
                        .webring-h2 {
                            margin: 0 0 0.6em 0;
                            font-size: 1.1em;
                            font-weight: 500;
                        }
                        .webring-description {
                            text-align: left;
                            line-height: 1.4;
                            margin: 0 0 0.75em 0;
                            color: var(--text-muted, #555);
                        }
                        .webring-nav {
                            display: flex;
                            justify-content: space-between;
                            letter-spacing: -0.03em;
                            line-height: 1.15;
                            color: #7b16ff;
                            font: inherit;
                            font-size: 1em;
                        }
                        .webring *:after {
                            content: unset !important;
                        }

                        @media (prefers-color-scheme: dark) {
                            .webring-nav {
                                color: #d4b3ff !important;
                            }
                            .webring { border-color: #464646; }
                        }

                        .webring a { color: inherit; }
                        .webring a:hover { text-decoration: none !important; }
                        .webring-nav a { text-decoration: none; }
                        .webring-nav a:hover { text-decoration: underline !important; }
                    </style>
                    <div class="webring">
                        <span class="webring-h2">Polyring</span>
                        <div class="webring-description">
                            This site is part of <a href="https://polyring.ch" target="_blank">Polyring</a>,  
                            a collection of ${MEMBERS.length} blogs published by ETH Zurich members.
                        </div>
                        <nav class="webring-nav" aria-label="Polyring navigation">
                            <a href="${prev.url}" target="_blank" rel="prev">← Previous</a>
                            <a href="${next.url}" target="_blank" rel="next">Next →</a>
                        </nav>
                    </div>
                `;
                }
            }

            if (!customElements.get("webring-banner")) {
                customElements.define("webring-banner", WebringBanner);
            }
        }

        // Defer execution using requestIdleCallback (with fallback for older browsers)
        if ('requestIdleCallback' in window) {
            requestIdleCallback(initPolyring, { timeout: 3000 });
        } else {
            // Fallback: run after a short delay to not block initial render
            setTimeout(initPolyring, 200);
        }
    })();
</script>

<?php
$theme_uri = get_stylesheet_directory_uri();
$theme_dir = get_stylesheet_directory();
$swup_files = array(
    'swup.umd.js',
    'swup-head-plugin.umd.js',
    'swup-body-class-plugin.umd.js',
);
foreach ($swup_files as $file) {
    $path = $theme_dir . '/js/vendor/' . $file;
    $url = $theme_uri . '/js/vendor/' . $file;
    $ver = file_exists($path) ? (string) filemtime($path) : '0';
    echo '<script src="' . esc_url($url) . '?v=' . esc_attr($ver) . '"></script>' . "\n";
}
?>
<script>
    (function () {
        if (typeof window.Swup === 'undefined') {
            console.error('[swup] failed to load');
            return;
        }

        const OUT_MS = 120;
        const IN_MS = 220;
        const swup = new window.Swup({
            containers: ['#swup'],
            animationSelector: false,
            cache: true,
            linkSelector: 'a[href]:not([href^="#"]):not([target="_blank"]):not([data-no-swup])',
            plugins: [
                new window.SwupHeadPlugin(),
                new window.SwupBodyClassPlugin(),
            ],
        });
        window.__swup = swup;

        const wait = (ms) => new Promise((r) => setTimeout(r, ms));
        swup.hooks.replace('animation:out:await', () => wait(OUT_MS));
        swup.hooks.replace('animation:in:await', () => wait(IN_MS));

        // Drive the in-animation manually so the cascade between Swup classes
        // can't suppress it. Right after the new content is in, snap it to
        // the "from" state, then on the next frame let CSS animate to rest.
        swup.hooks.on('content:replace', () => {
            const main = document.getElementById('swup');
            if (!main) return;
            main.style.transition = 'none';
            main.style.opacity = '0';
            main.style.transform = 'translateY(8px)';
            void main.offsetWidth; // force reflow
            requestAnimationFrame(() => {
                main.style.transition = '';
                main.style.opacity = '';
                main.style.transform = '';
            });
        });

        // ---- Scroll save / restore --------------------------------------
        const SCROLL_KEY = 'fabian-swup-scroll';
        let scrollMap = {};
        try { scrollMap = JSON.parse(sessionStorage.getItem(SCROLL_KEY) || '{}'); } catch (e) {}
        const saveMap = () => {
            try { sessionStorage.setItem(SCROLL_KEY, JSON.stringify(scrollMap)); } catch (e) {}
        };
        const setScroll = (y) => {
            const lenis = window.__lenis;
            if (lenis && typeof lenis.scrollTo === 'function') {
                lenis.scrollTo(y, { immediate: true, force: true });
            }
            window.scrollTo(0, y);
        };

        let restoreNext = false;

        swup.hooks.before('visit:start', (visit) => {
            scrollMap[visit.from.url] = window.scrollY;
            saveMap();
            const trigger = visit.trigger && visit.trigger.el;
            restoreNext = !!(trigger && trigger.closest && trigger.closest('[data-restore-scroll]'));
        });

        swup.hooks.on('content:replace', () => {
            const url = window.location.pathname + window.location.search;
            const saved = scrollMap[url];
            const isPopstate = swup.visit && swup.visit.history.popstate;
            const shouldRestore = typeof saved === 'number' && (isPopstate || restoreNext);
            restoreNext = false;
            if (shouldRestore) {
                requestAnimationFrame(() => setScroll(saved));
            } else {
                requestAnimationFrame(() => setScroll(0));
            }
        });

        swup.hooks.on('page:view', () => {
            try {
                const t = localStorage.getItem('fabian-theme');
                if (t === 'light' || t === 'dark') {
                    document.documentElement.setAttribute('data-theme', t);
                    document.documentElement.classList.add('theme-' + t);
                }
            } catch (e) {}
        });
    })();
</script>

<?php wp_footer(); ?>
</body>

</html>