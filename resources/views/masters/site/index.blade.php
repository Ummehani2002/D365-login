<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Site Master</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 16px;
            background: #f3f2f1;
            color: #323130;
        }
        .header {
            background: #fff;
            color: #201f1e;
            padding: 14px 16px;
            border: 1px solid #edebe9;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .card {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        button {
            background: #106ebe;
            color: #fff;
            border: 1px solid #106ebe;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            border-bottom: 1px solid #edebe9;
            padding: 10px 8px;
        }
        th {
            color: #605e5c;
            background: #faf9f8;
            font-weight: 600;
        }
        .error {
            color: #a4262c;
            font-size: 13px;
            margin-top: 4px;
        }
        .status {
            background: #e8f6ee;
            color: #1f7a48;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .back-link {
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
        }
        .danger {
            background: #a4262c;
            border-color: #a4262c;
            padding: 6px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    <div class="header">
        <h1>Site Master</h1>
    </div>

    <div class="card">
        <h2>Add site</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="site-form">
            <div class="form-row">
                <div>
                    <label for="site_id">Site ID</label>
                    <input id="site_id" name="site_id" type="text" maxlength="100" required placeholder="e.g. SITE001">
                </div>
                <div>
                    <label for="site_name">Site name</label>
                    <input id="site_name" name="site_name" type="text" maxlength="255" required placeholder="e.g. Dubai Main Site">
                </div>
            </div>
            <button type="submit">Save site</button>
        </form>
    </div>

    <div class="card">
        <h2>Sites</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Site ID</th>
                    <th>Site Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" id="sites-loading">Loading sites...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <script>
        const sitesTbody = document.querySelector('tbody');
        const sitesApiUrl = "{{ url('/masters/api/sites') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        };

        const formatDate = (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleString();
        };

        const setFormMessage = (el, text, show) => {
            el.textContent = text;
            el.style.display = show ? 'block' : 'none';
        };

        const escapeHtml = (s) => {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        };

        const loadSites = async () => {
            sitesTbody.innerHTML = '<tr><td colspan="5">Loading sites...</td></tr>';
            try {
                const response = await fetch(sitesApiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load sites');
                const payload = await response.json();
                const sites = payload.data || [];

                if (!sites.length) {
                    sitesTbody.innerHTML = '<tr><td colspan="5">No sites found.</td></tr>';
                    return;
                }

                sitesTbody.innerHTML = sites.map((site, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(site.site_id ?? '-')}</td>
                        <td>${escapeHtml(site.site_name ?? '-')}</td>
                        <td>${formatDate(site.created_at)}</td>
                        <td><button class="danger" data-id="${site.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                sitesTbody.innerHTML = '<tr><td colspan="5">Failed to load sites.</td></tr>';
            }
        };

        document.getElementById('site-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const siteId = document.getElementById('site_id').value.trim();
            const siteName = document.getElementById('site_name').value.trim();

            try {
                const response = await fetch(sitesApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        site_id: siteId,
                        site_name: siteName,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                document.getElementById('site_id').value = '';
                document.getElementById('site_name').value = '';
                setFormMessage(statusEl, 'Site created.', true);
                await loadSites();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        sitesTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this site?')) return;

            try {
                const response = await fetch(`${sitesApiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });

                if (!response.ok) throw new Error('Delete failed');
                await loadSites();
            } catch {
                window.alert('Failed to delete site.');
            }
        });

        loadSites();
    </script>
</body>
</html>
