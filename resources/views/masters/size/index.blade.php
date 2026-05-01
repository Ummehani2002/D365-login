<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Size Master</title>
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
            grid-template-columns: minmax(320px, 640px) auto;
            gap: 12px;
            margin-bottom: 12px;
            align-items: end;
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
        <h1>Size Master</h1>
    </div>

    <div class="card">
        <h2>Add size</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="size-form">
            <div class="form-row">
                <div>
                    <label for="d365_size_name">Size Name</label>
                    <input id="d365_size_name" name="d365_size_name" type="text" maxlength="100" required placeholder="e.g. XL">
                </div>
                <button type="submit">Save size</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Sizes</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Size Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" id="sizes-loading">Loading sizes...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <script>
        const sizesTbody = document.querySelector('tbody');
        const sizesApiUrl = "{{ url('/masters/api/sizes') }}";
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

        const loadSizes = async () => {
            sizesTbody.innerHTML = '<tr><td colspan="4">Loading sizes...</td></tr>';
            try {
                const response = await fetch(sizesApiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load sizes');
                const payload = await response.json();
                const sizes = payload.data || [];

                if (!sizes.length) {
                    sizesTbody.innerHTML = '<tr><td colspan="4">No sizes found.</td></tr>';
                    return;
                }

                sizesTbody.innerHTML = sizes.map((size, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(size.d365_size_name ?? '-')}</td>
                        <td>${formatDate(size.created_at)}</td>
                        <td><button class="danger" data-id="${size.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                sizesTbody.innerHTML = '<tr><td colspan="4">Failed to load sizes.</td></tr>';
            }
        };

        document.getElementById('size-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const d365SizeName = document.getElementById('d365_size_name').value.trim();

            try {
                const response = await fetch(sizesApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        d365_size_name: d365SizeName,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                document.getElementById('d365_size_name').value = '';
                setFormMessage(statusEl, payload.message || 'Size saved.', true);
                await loadSizes();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        sizesTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this size?')) return;

            try {
                const response = await fetch(`${sizesApiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });

                if (!response.ok) throw new Error('Delete failed');
                await loadSizes();
            } catch {
                window.alert('Failed to delete size.');
            }
        });

        loadSizes();
    </script>
</body>
</html>
