<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Currency Master</title>
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        <h1>Currency Master</h1>
    </div>

    <div class="card">
        <h2>Add currency</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="currency-form">
            <div class="form-row">
                <div>
                    <label for="currency_code">Currency Code</label>
                    <input id="currency_code" name="currency_code" type="text" maxlength="20" required placeholder="e.g. USD">
                </div>
                <div>
                    <label for="txt">Description</label>
                    <input id="txt" name="txt" type="text" maxlength="255" required placeholder="e.g. US Dollar">
                </div>
                <button type="submit">Save currency</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Currencies</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Currency Code</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5">Loading currencies...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <script>
        const currenciesTbody = document.querySelector('tbody');
        const currenciesApiUrl = "{{ url('/masters/api/currencies') }}";
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

        const loadCurrencies = async () => {
            currenciesTbody.innerHTML = '<tr><td colspan="5">Loading currencies...</td></tr>';
            try {
                const response = await fetch(currenciesApiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load currencies');
                const payload = await response.json();
                const currencies = payload.data || [];

                if (!currencies.length) {
                    currenciesTbody.innerHTML = '<tr><td colspan="5">No currencies found.</td></tr>';
                    return;
                }

                currenciesTbody.innerHTML = currencies.map((currency, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(currency.currency_code ?? '-')}</td>
                        <td>${escapeHtml(currency.txt ?? '-')}</td>
                        <td>${formatDate(currency.created_at)}</td>
                        <td><button class="danger" data-id="${currency.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                currenciesTbody.innerHTML = '<tr><td colspan="5">Failed to load currencies.</td></tr>';
            }
        };

        document.getElementById('currency-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const currencyCode = document.getElementById('currency_code').value.trim();
            const txt = document.getElementById('txt').value.trim();

            try {
                const response = await fetch(currenciesApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        currency_code: currencyCode,
                        txt: txt,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                document.getElementById('currency_code').value = '';
                document.getElementById('txt').value = '';
                setFormMessage(statusEl, payload.message || 'Currency created.', true);
                await loadCurrencies();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        currenciesTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this currency?')) return;

            try {
                const response = await fetch(`${currenciesApiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });

                if (!response.ok) throw new Error('Delete failed');
                await loadCurrencies();
            } catch {
                window.alert('Failed to delete currency.');
            }
        });

        loadCurrencies();
    </script>
</body>
</html>
