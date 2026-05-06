<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Roles</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body
    data-url-roles="{{ route('settings.roles.api.roles.index') }}"
    data-url-roles-store="{{ route('settings.roles.api.roles.store') }}"
    data-url-permissions="{{ route('settings.roles.api.permissions.index') }}"
>
@include('partials.global-company-selector')
@include('settings.rbac.partials.sidebar')
<main class="main">
    <h1 class="page-title">Roles</h1>
    <p class="rbac-page-intro">Manage roles and assign permissions.</p>
    <div id="flashError" class="flash-error"></div>
    <div class="d365-cmd-bar"><button id="cmdNew" class="d365-cmd d365-cmd-primary">+ New</button></div>
    <div class="table-card"><table class="users-grid"><thead><tr><th>Name</th><th>Permission count</th></tr></thead><tbody id="rolesTableBody"></tbody></table></div>
</main>
<script>
(function(){const b=document.body,u=b.dataset.urlRoles,s=b.dataset.urlRolesStore,p=b.dataset.urlPermissions,c=document.querySelector('meta[name="csrf-token"]').content,t=document.getElementById('rolesTableBody');let perms=[];function api(x,o={}){const h=Object.assign({'Accept':'application/json','X-CSRF-TOKEN':c,'X-Requested-With':'XMLHttpRequest'},o.headers||{});if(o.body)h['Content-Type']='application/json';return fetch(x,Object.assign({},o,{headers:h})).then(r=>r.json().then(d=>({r,d})).catch(()=>({r,data:{}})));}function load(){api(p).then(x=>{perms=(x.d||{}).permissions||[];});api(u).then(x=>{const d=x.d||{};t.innerHTML='';(d.roles||[]).forEach(r=>{const tr=document.createElement('tr');tr.innerHTML='<td>'+r.name+'</td><td>'+r.permission_count+'</td>';t.appendChild(tr);});});}document.getElementById('cmdNew').addEventListener('click',()=>{const name=prompt('Role name');if(!name)return;const hint=perms.map(pp=>pp.id+':'+pp.slug).join(', ');const raw=prompt('Permission IDs comma separated\n'+hint);const ids=(raw||'').split(',').map(v=>parseInt(v.trim(),10)).filter(v=>!isNaN(v));api(s,{method:'POST',body:JSON.stringify({name,permission_ids:ids})}).then(()=>load());});load();})();
</script>
</body>
</html>

