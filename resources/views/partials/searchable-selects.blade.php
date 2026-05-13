{{-- Tom Select: type-to-filter on all native selects. Opt out with data-no-ts="1" on the select. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" crossorigin="anonymous">
<style>
    .ts-wrapper.single .ts-control {
        min-height: 34px;
        border-radius: 2px;
        border-color: #8a8886;
    }
    .ts-dropdown {
        z-index: 10050;
    }
    .global-company-box .ts-wrapper {
        flex: 1;
        min-width: 0;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" crossorigin="anonymous"></script>
<script>
(() => {
    if (window.__searchableSelectsBooted) return;
    window.__searchableSelectsBooted = true;

    window.tsDestroy = function (el) {
        if (!el || el.tagName !== 'SELECT') return;
        if (el.tomselect) {
            try {
                el.tomselect.destroy();
            } catch (e) {}
        }
    };

    window.tsInit = function (el) {
        if (!el || el.tagName !== 'SELECT') return;
        if (el.dataset.noTs === '1') return;
        if (el.tomselect) return;
        if (el.closest('[data-no-ts]')) return;

        new TomSelect(el, {
            allowEmptyOption: true,
            create: false,
            maxOptions: null,
            hideSelected: false,
            dropdownParent: document.body,
            plugins: ['dropdown_input'],
        });
    };

    function initAllIn(root) {
        const scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('select').forEach((el) => {
            if (el.dataset.noTs === '1') return;
            if (el.closest('[data-no-ts]')) return;
            window.tsInit(el);
        });
    }

    const selectRefreshTimers = new Map();

    function scheduleSelectRefresh(el) {
        if (!el || el.tagName !== 'SELECT') return;
        const prev = selectRefreshTimers.get(el);
        if (prev) clearTimeout(prev);
        selectRefreshTimers.set(
            el,
            setTimeout(() => {
                selectRefreshTimers.delete(el);
                if (!el.isConnected) return;
                if (el.dataset.noTs === '1') return;
                if (el.closest('[data-no-ts]')) return;
                window.tsDestroy(el);
                window.tsInit(el);
            }, 40)
        );
    }

    let tsBootstrap = true;

    function onMutations(records) {
        if (tsBootstrap) return;
        for (const r of records) {
            if (r.type !== 'childList') continue;
            const t = r.target;
            if (t && t.tagName === 'SELECT') {
                scheduleSelectRefresh(t);
                continue;
            }
            r.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return;
                if (node.tagName === 'SELECT') window.tsInit(node);
                if (node.querySelectorAll) {
                    node.querySelectorAll('select').forEach((sel) => {
                        if (sel.dataset.noTs === '1') return;
                        if (sel.closest('[data-no-ts]')) return;
                        window.tsInit(sel);
                    });
                }
            });
        }
    }

    function boot() {
        initAllIn(document);
        queueMicrotask(() => {
            tsBootstrap = false;
        });
        const ob = new MutationObserver(onMutations);
        ob.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
