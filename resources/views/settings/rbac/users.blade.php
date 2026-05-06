<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Users</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body data-url-memberships="{{ route('settings.users.api.memberships.index') }}" data-url-memberships-store="{{ route('settings.users.api.memberships.store') }}">
@include('partials.global-company-selector')
@include('settings.rbac.partials.sidebar')
<main class="main">
    <h1 class="page-title">Users</h1>
    <p class="rbac-page-intro">Manage company user memberships and assigned roles.</p>
    <div id="flashError" class="flash-error"></div>
    <div class="d365-cmd-bar"><button id="cmdNew" class="d365-cmd d365-cmd-primary">+ New</button></div>
    <div class="table-card"><table class="users-grid"><thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Roles</th></tr></thead><tbody id="usersTableBody"></tbody></table></div>
</main>
<script>
(function(){const b=document.body,u=b.dataset.urlMemberships,s=b.dataset.urlMembershipsStore,c=document.querySelector('meta[name="csrf-token"]').content,t=document.getElementById('usersTableBody');function api(p,o={}){const h=Object.assign({'Accept':'application/json','X-CSRF-TOKEN':c,'X-Requested-With':'XMLHttpRequest'},o.headers||{});if(o.body)h['Content-Type']='application/json';return fetch(p,Object.assign({},o,{headers:h})).then(r=>r.json().then(d=>({r,d})).catch(()=>({r,data:{}})));}function load(){api(u).then(x=>{const d=x.d||{};t.innerHTML='';(d.memberships||[]).forEach(m=>{const roles=(m.roles||[]).map(r=>r.name).join(', ');const tr=document.createElement('tr');tr.innerHTML='<td>'+m.name+'</td><td>'+m.email+'</td><td>'+m.company_code+'</td><td>'+roles+'</td>';t.appendChild(tr);});});}document.getElementById('cmdNew').addEventListener('click',()=>alert('Use API endpoints to create membership from your shared UI flow.'));load();})();
</script>
</body>
</html>

